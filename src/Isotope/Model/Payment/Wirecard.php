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

class Wirecard extends Postsale implements IsotopePayment
{
    /**
     * The API host.
     *
     * @var string
     */
    protected const API_HOST = 'https://wpp.wirecard.com';

    /**
     * The API test host.
     *
     * @var string
     */
    protected const API_TEST_HOST = 'https://wpp-test.wirecard.com';

    /**
     * The API payment end point.
     *
     * @var string
     */
    protected const API_REGISTER = '/api/payment/register';

    /**
     * The request.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function __construct(\Contao\Database\Result $databaseResult)
    {
        parent::__construct($databaseResult);
        $this->request = System::getContainer()->get('request_stack')->getCurrentRequest();
    }

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
        $basePath = $this->request->getSchemeAndHttpHost().$this->request->getBasePath();

        // Redirect URL in case of any failure
        $failUrl = $basePath.'/'.$module->generateUrlForStep('failed', $order);

        // Define account holder
        $accountHolder = [];
        $billingAddress = $order->getBillingAddress();

        if (!empty($billingAddress->firstname)) {
            $accountHolder['first-name'] = $billingAddress->firstname;
        }

        if (!empty($billingAddress->lastname)) {
            $accountHolder['last-name'] = $billingAddress->lastname;
        } else {
            System::log('No last name defined in billing address for order ID '.$order->getId().', which is required by Wirecard', __METHOD__, TL_ERROR);
            throw new RedirectResponseException($failUrl);
        }

        // Define the rest of the parameters
        $parameters = [
            'merchant-account-id' => ['value' => $this->wirecardMerchantId],
            'request-id' => $order->getUniqueId(),
            'transaction-type' => 'authorization',
            'requested-amount' => [
                'value' => $order->getTotal(),
                'currency' => $order->getCurrency(),
            ],
            'account-holder' => $accountHolder,
            'success-redirect-url' => $basePath.'/'.$module->generateUrlForStep('complete', $order),
            'fail-redirect-url' => $failUrl,
            'cancel-redirect-url' => $failUrl,
            'notifications' => [
                'format' => 'application/json',
                'notification' => [[
                    'url' => $basePath.'/system/modules/isotope/postsale.php?mod=pay&id='.$this->id,
                ]],
            ],
        ];

        // Check for payment method
        if ($this->wirecardPaymentMethod) {
            $parameters['payment-methods'] = [
                'payment-method' => [
                    'name' => $this->wirecardPaymentMethod,
                ],
            ];
        }

        $domain = $this->wirecardTestApi ? self::API_TEST_HOST : self::API_HOST;
        $client = new \GuzzleHttp\Client(['base_uri' => $domain]);
        $response = $client->post(self::API_REGISTER, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($this->wirecardUser.':'.$this->wirecardPassword),
            ],
            'body' => json_encode(['payment' => $parameters]),
        ]);

        try {
            $result = @json_decode((string) $response->getBody(), true);
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
        $paymentResponse = $this->getResponseData();

        if (null === $paymentResponse) {
            return null;
        }

        if ('success' !== $paymentResponse['payment']['transaction-state']) {
            System::log('Unsuccessful transaction via Wirecard for order ' . $paymentResponse['payment']['request-id'], __METHOD__, TL_ERROR);
            return null;
        }
        
        return Order::findOneByUniqid($paymentResponse['payment']['request-id']);
    }

    public function processPostsale(IsotopeProductCollection $order)
    {
        $paymentResponse = $this->getResponseData();

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
    protected function getResponseData(): ?array
    {
        $post = $this->request->request;

        $responseBase64 = $post->get('response-base64');
        $signatureBase64 = $post->get('response-signature-base64');
        $signatureAlgorithm = $post->get('response-signature-algorithm');

        if (!$this->isValidSignature($responseBase64, $signatureBase64, $signatureAlgorithm)) {
            System::log('Invalid Wirecard response signature.', __METHOD__, TL_ERROR);
            return null;
        }

        return json_decode(base64_decode($post->get('response-base64'), true), true);
    }

    /**
     * Checks if the given parameters from the response contain a valid signature.
     */
    protected function isValidSignature(string $responseBase64, string $signatureBase64, string $signatureAlgorithm = 'HmacSHA256'): bool
    {
        if ('HmacSHA256' !== $signatureAlgorithm) {
            throw new \RuntimeException('Unsupported signature algorithm "'.$signatureAlgorithm.'"');
        }

        $signature = hash_hmac('sha256', $responseBase64, $this->secret, true);

        return hash_equals($signature, base64_decode($signatureBase64, true));
    }
}
