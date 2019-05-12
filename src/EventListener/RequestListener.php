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

namespace Wangaz\ContaoIsotopeWirecardBundle\EventListener;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Check for a response-base64 POST parameter from Wirecard and disable token check if applicable.
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $post = $request->request;

        if ($post->has('response-base64')) {
            // Decode response-base64 content
            $paymentResponse = json_decode(base64_decode($post->get('response-base64'), true), true);

            // Get the request-id
            $requestId = $paymentResponse['payment']['request-id'];

            // Check for request-id in orders
            $dbresult = $this->db->executeQuery('SELECT * FROM tl_iso_product_collection WHERE uniqid = ?', [$requestId]);

            if ($dbresult->rowCount() > 0) {
                // Seems to be a legit wirecard response, so disable token check
                $request->attributes->set('_token_check', false);
            }
        }
    }
}
