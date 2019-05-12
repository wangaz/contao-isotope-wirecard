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

use Contao\Module;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment\Postsale;

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
     * {@inheritdoc}
     */
    public function processPostsale(IsotopeProductCollection $order): void
    {
        throw new \Exception(__METHOD__.' not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsaleOrder(): void
    {
        throw new \Exception(__METHOD__.' not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function checkoutForm(IsotopeProductCollection $order, Module $module): void
    {
        throw new \Exception(__METHOD__.' not implemented');
    }
}
