<?php

/**
 * Wirecard Payment Module for Isotope eCommerce for Contao Open Source CMS
 *
 * 
 * @copyright  Wangaz. GbR 2014
 * @author     Wangaz. GbR <hallo@wangaz.com>
 * @package    isotope-wirecard
 */

namespace Isotope\Model;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment\Postsale;
use Isotope\Model\ProductCollection\Order;


class Wirecard extends Postsale implements IsotopePayment {

    /**
     * Handle the server to server postsale request
     * @param   IsotopeProductCollection
     */
    public function processPostsale(IsotopeProductCollection $objOrder) {
        // check if both hashes match
        if (Input::post('requestFingerprint') == $this->calcHashPost()) {
            System::log('The given hash does not match for Order ID "' . Input::post('order_id') . '" (Wirecard)', __METHOD__, TL_ERROR);

            return;
        }
        
        $strState = Input::post('paymentState');
        $strMessage = Input::post('message');
        
        // log
        System::log('Update of payment status of Order ID "' . Input::post('order_id') . '" (Wirecard): "' . $strState . '"' . (!empty($strMessage) ? ': ' . $strMessage : ''), __METHOD__, $strState == 'SUCCESS' ? TL_GENERAL : TL_ERROR);
        
        // ignore all cases except success
        if ($strState != 'SUCCESS')
            return;

		// perform checkout
        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for Order ID "' . Input::post('order_id') . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        // Store request data in order for future references
        $payment = StringUtil::deserialize($objOrder->payment_data, true);
        foreach ($_POST as $k => $v) {
            $payment['POSTSALE'][$k] = Input::post($k);
        }
        $objOrder->payment_data = serialize($payment);

		// update status
        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);
        $objOrder->save();
    }

    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('order_id'));
    }

    /**
     * Return the payment form
     * @param   IsotopeProductCollection    The order being places
     * @param   Module                      The checkout module instance
     * @return  string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule) {  
    	// get current host and       
    	$strDescription = Environment::get('host');
    	$objContact		= PageModel::findWithDetails($this->wirecard_contact);

        $arrParams = array(
            'customerId'			=> $this->wirecard_customer_id,
            'language'				=> $GLOBALS['TL_LANGUAGE'],
            'paymentType'			=> 'SELECT',
            'amount'				=> number_format($objOrder->getTotal(), 2, '.', ''),
            'currency'				=> $objOrder->currency,
            'orderDescription'		=> $strDescription,
            'successUrl'			=> Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder),
            'cancelUrl'				=> Environment::get('base') . $objModule->generateUrlForStep('failed'),
            'failureUrl'			=> Environment::get('base') . $objModule->generateUrlForStep('failed'),
            'serviceUrl'			=> Environment::get('base') . Controller::generateFrontendUrl($objContact->row()),
            'confirmUrl'			=> Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id,
            'customerStatement'		=> $strDescription,
            'order_id'				=> $objOrder->id,
            'order_uniqid'			=> $objOrder->uniqid,
            'secret'				=> $this->wirecard_secret,
            'REQUEST_TOKEN'			=> REQUEST_TOKEN,
        );
                
        $arrParams['requestFingerprintOrder']	= implode(',', array_keys($arrParams)) . ',requestFingerprintOrder';
        $arrParams['requestFingerprint']		= $this->calcHashArray($arrParams);

        $objTemplate = new \Isotope\Template('iso_payment_wirecard');
        $objTemplate->setData($this->arrData);
        $objTemplate->action = 'https://api.qenta.com/page/init.php';
        $objTemplate->params = array_filter(array_diff_key($arrParams, array('secret' => '')));

        return $objTemplate->parse();
    }

    public function backendInterface($orderId)
    {
        $order = Order::findByPk($orderId);

        if (null === $order) {
            return parent::backendInterface($orderId);
        }

        $payment = StringUtil::deserialize($order->payment_data);

        if (empty($payment) || empty($payment['POSTSALE'])) {
            return parent::backendInterface($orderId);
        }

        $template = new BackendTemplate('be_iso_payment_data_wirecard');
        $template->name = $this->name;
        $template->type = $this->type;
        $template->payment = $payment['POSTSALE'];

        return $template->parse();
    }
    
    /**
     * Calculate the MD5-hash for POST parameters.
     */
    private function calcHashPost() {
    	$strParams = '';
    	
    	$arrOrder = explode(',', \Input::post('requestFingerprintOrder'));
    
		// iterate over params in given order
		foreach ($arrOrder as $strKey)
			$strParams .= \Input::post($strKey);
		
		// calc hash
		return hash_hmac('sha512', $strParams, $this->wirecard_secret);
    }
    
    /**
     * Calculate the MD5-hash for given Array.
     */
    private function calcHashArray($arrParams) {
    	$strParams = '';
    	
    	$arrOrder = explode(',', $arrParams['requestFingerprintOrder']);
    
		// iterate over params in given order
		foreach ($arrOrder as $strKey)
			$strParams .= $arrParams[$strKey];
		
		// calc hash
		return hash_hmac('sha512', $strParams, $this->wirecard_secret);
    }
}

