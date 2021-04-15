<?php

declare(strict_types=1);

/*
 * This file is part of the Wangaz\ContaoIsotopeWirecardBundle.
 *
 * (c) Wangaz
 * (c) inspiredminds
 *
 * @license CC-BY-SA-4.0
 */

namespace Wangaz\ContaoIsotopeWirecardBundle\Isotope\Model\Payment;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Module;
use Contao\System;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment\Postsale;
use Isotope\Model\ProductCollection\Order;
use Symfony\Component\HttpFoundation\Request;

class Wirecard extends Postsale implements IsotopePayment
{
    /**
     * The API payment end point.
     *
     * @var string
     */
    protected const API_REGISTER = '/api/payment/register';

    /**
     * The available payment methods and transaction type mappings.
     *
     * @var array
     */
    public static $paymentMethods = [
        'creditcard' => 'purchase',
        'alipay-xborder' => 'debit',
        'bancontact' => 'debit',
        'eps' => 'debit',
        'ideal' => 'debit',
        'paybox' => 'purchase',
        'paypal' => 'debit',
        'paysafecard' => 'debit',
        'p24' => 'debit',
        'sepadirectdebit' => 'debit',
        'sofortbanking' => 'debit',
    ];

    /**
     * This does not actually show a form. Instead it initialises the Wirecard
     * payment session and throws a RedirectResponseException to redirect the
     * user to the Hosted Wirecard Payment Page.
     *
     * @throws RedirectResponseException
     */
    public function checkoutForm(IsotopeProductCollection $order, Module $module)
    {
        // Base path for URLs
        $basePath = $this->getRequest()->getSchemeAndHttpHost().$this->getRequest()->getBasePath();

        // Redirect URL in case of any failure
        $failUrl = $basePath.'/'.$module->generateUrlForStep('failed', $order);

        // Define account holder
        $accountHolder = [];
        /** @var \Isotope\Model\Address $billingAddress */
        $billingAddress = $order->getBillingAddress();

        if (!empty($billingAddress->firstname)) {
            $accountHolder['first-name'] = $billingAddress->firstname;
        }

        if (!empty($billingAddress->lastname) || !empty($billingAddress->company)) {
            $accountHolder['last-name'] = $billingAddress->lastname ?: $billingAddress->company;
        } else {
            System::log('No last name or company defined in billing address for order ID '.$order->getId().', which is required by Wirecard.', __METHOD__, TL_ERROR);
            throw new RedirectResponseException($failUrl);
        }

        if (!empty($billingAddress->email)) {
            $accountHolder['email'] = $billingAddress->email;
        }

        if (!empty($billingAddress->vat_no)) {
            $accountHolder['tax-number'] = $billingAddress->vat_no;
        }

        if (empty($this->wirecardPaymentMethod) || !isset(self::$paymentMethods[$this->wirecardPaymentMethod])) {
            throw new \RuntimeException('Invalid payment method defined in payment module.');
        }

        // Define the rest of the parameters
        $parameters = [
            'merchant-account-id' => ['value' => $this->wirecardMerchantId],
            'request-id' => $order->getUniqueId(),
            'transaction-type' => self::$paymentMethods[$this->wirecardPaymentMethod],
            'requested-amount' => [
                'value' => number_format($order->getTotal(), 2),
                'currency' => $order->getCurrency(),
            ],
            'payment-methods' => [
                'payment-method' => [
                    ['name' => $this->wirecardPaymentMethod],
                ],
            ],
            'account-holder' => $accountHolder,
            'success-redirect-url' => $basePath.'/'.$module->generateUrlForStep('complete', $order),
            'fail-redirect-url' => $failUrl,
            'cancel-redirect-url' => $failUrl,
            'notifications' => [
                'format' => 'application/json-signed',
                'notification' => [
                    ['url' => $basePath.'/system/modules/isotope/postsale.php?mod=pay&id='.$this->id],
                ],
            ],
            'descriptor' => $this->getRequest()->getHost(),
        ];

        // Process base URL
        $baseUrl = $this->wirecardBaseUrl;

        if (0 !== strpos($baseUrl, 'http')) {
            $baseUrl = 'https://'.$baseUrl;
        }

        $baseHost = parse_url($baseUrl, PHP_URL_HOST);

        if (empty($baseHost)) {
            throw new \RuntimeException('Invalid base URL.');
        }

        $baseUri = 'https://'.$baseHost;

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = System::getContainer()->get('monolog.logger.contao');

        try {
            // Initialize the client
            $client = new \GuzzleHttp\Client(['base_uri' => $baseUri]);

            // Create the payment request body and headers
            $body = json_encode(['payment' => $parameters]);
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($this->wirecardUser.':'.$this->wirecardPassword),
            ];

            // Log the request
            $logger->debug('Wirecard request URL: '.rtrim($baseUri, '/').self::API_REGISTER);
            $logger->debug('Wirecard request headers: '.json_encode($headers));
            $logger->debug('Wirecard request body: '.$body);

            // Initiate the payment session
            $response = $client->post(self::API_REGISTER, [
                'headers' => $headers,
                'body' => json_encode(['payment' => $parameters]),
            ]);

            // Get the payment session response body
            $result = @json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = (string) $response->getBody();
            $decodedBody = @json_decode($body);
            $errors = [];

            // Log the response body
            $logger->debug('Wirecard response '.$response->getStatusCode().': '.$body);

            // Compile the errors
            if (!empty($decodedBody) && isset($decodedBody->errors) && !empty($decodedBody->errors)) {
                foreach ($decodedBody->errors as $error) {
                    if (isset($error->description)) {
                        $errors[] = $error->description;
                    }
                }
            }

            if (!empty($decodedBody) && isset($decodedBody->payment) && !empty($decodedBody->payment->statuses)) {
                $statuses = $decodedBody->payment->statuses;
                if (!empty($statuses->status)) {
                    foreach ($statuses->status as $status) {
                        if (isset($status->description)) {
                            $errors[] = $status->description;
                        }
                    }
                }
            }

            if (empty($errors)) {
                $errors[] = $e->getMessage();
            }

            System::log('Error while initialising Wirecard payment session for order ID '.$order->getId().': '.implode(' | ', $errors), __METHOD__, TL_ERROR);
            throw new RedirectResponseException($failUrl);
        } catch (\Exception $e) {
            System::log('Error while initialising Wirecard payment session for order ID '.$order->getId().': '.$e->getMessage(), __METHOD__, TL_ERROR);
            throw new RedirectResponseException($failUrl);
        }

        if (empty($result) || !isset($result['payment-redirect-url'])) {
            System::log('Invalid response from Wirecard server for order ID '.$order->getId(), __METHOD__, TL_ERROR);
            throw new RedirectResponseException($failUrl);
        }

        throw new RedirectResponseException($result['payment-redirect-url']);
    }

    public function getPostsaleOrder()
    {
        $paymentResponse = $this->getPaymentResponse();

        if (null === $paymentResponse) {
            return null;
        }

        if ('success' !== $paymentResponse['payment']['transaction-state']) {
            System::log('Unsuccessful transaction via Wirecard for order '.$paymentResponse['payment']['request-id'], __METHOD__, TL_ERROR);

            return null;
        }

        return Order::findOneByUniqid($paymentResponse['payment']['request-id']);
    }

    public function processPostsale(IsotopeProductCollection $order)
    {
        $paymentResponse = $this->getPaymentResponse();

        // Check transaction state again
        if ('success' !== $paymentResponse['payment']['transaction-state']) {
            return;
        }

        // Perform checkout
        if (!$order->checkout()) {
            System::log('Postsale checkout for order ID '.$order->getId().' failed', __METHOD__, TL_ERROR);

            return;
        }

        // update status
        $order->date_paid = time();
        $order->updateOrderStatus($this->new_order_status);
        $order->save();
    }

    /**
     * Returns the Wirecard response data, when valid and present.
     */
    protected function getPaymentResponse(): ?array
    {
        // Do not use parse_str here, since parse_str automatically applies urldecode(), which destroys the base64url encoded content
        preg_match_all('/([-\w]+)=([^&]+)/', $this->getRequest()->getContent(), $pairs);
        $parameters = array_combine($pairs[1], $pairs[2]);

        $responseBase64 = $parameters['response-base64'];
        $signatureBase64 = $parameters['response-signature-base64'];
        $signatureAlgorithm = $parameters['response-signature-algorithm'];

        if (!$this->isValidSignature($responseBase64, $signatureBase64, $signatureAlgorithm)) {
            System::log('Invalid Wirecard response signature.', __METHOD__, TL_ERROR);

            return null;
        }

        // Wirecard uses "Base 64 Encoding with URL and Filename Safe Alphabet"
        $responseBase64 = strtr($responseBase64, '-_', '+/');
        $decodedResponse = base64_decode($responseBase64, true);

        if (false === $decodedResponse) {
            System::log('Could not decode base64 encoded response: '.$responseBase64, __METHOD__, TL_ERROR);

            return null;
        }

        $jsonDecodedResponse = json_decode($decodedResponse, true);

        if (false === $jsonDecodedResponse) {
            System::log('Could not JSON decode response: '.$decodedResponse, __METHOD__, TL_ERROR);

            return null;
        }

        return $jsonDecodedResponse;
    }

    /**
     * Checks if the given parameters from the response contain a valid signature.
     */
    protected function isValidSignature(string $responseBase64, string $signatureBase64, string $signatureAlgorithm = 'HmacSHA256'): bool
    {
        if ('HmacSHA256' !== $signatureAlgorithm) {
            throw new \RuntimeException('Unsupported signature algorithm "'.$signatureAlgorithm.'"');
        }

        $signature = hash_hmac('sha256', $responseBase64, $this->wirecardSecret, true);

        return hash_equals($signature, base64_decode($signatureBase64, true));
    }

    /**
     * Return the Symfony Request object of the current request.
     */
    protected function getRequest(): Request
    {
        return System::getContainer()->get('request_stack')->getCurrentRequest();
    }
}
