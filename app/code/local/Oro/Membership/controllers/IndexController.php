<?php
/**
 * Controller
 *
 * @category   Oro
 * @package    Oro_Membership
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
require_once  'Magestore/Membership/controllers/IndexController.php';

class Oro_Membership_IndexController extends Magestore_Membership_IndexController
{
    /**
     * Redirect to "opener" product when purchasing membership
     */
    public function addToCartUrlAction()
    {
        $productId = $this->getRequest()->getParam('productId');
        $returnUrl = $this->getRequest()->getParam('return_url');
        $params = array();
        if($returnUrl){
            $params[self::PARAM_NAME_URL_ENCODED] = Mage::helper('core')->urlEncode($returnUrl);
        }
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $block = Mage::getBlockSingleton('catalog/product_list');
            $this->_redirectUrl($block->getAddToCartUrl(Mage::getModel('catalog/product')->load($productId), $params));
        } else {
            $this->preLogin();
        }
    }

    public function preLogin() {
        $productId = Mage::app()->getRequest()->getParam('productId');
        $returnUrl = $this->getRequest()->getParam('return_url');
        $params = array();
        if($returnUrl){
            $params[self::PARAM_NAME_URL_ENCODED] = Mage::helper('core')->urlEncode($returnUrl);
        }
        $block = Mage::getBlockSingleton('catalog/product_list');
        // $backUrl = Mage::getUrl('checkout/cart/add', array('product' => $productId));
        $backUrl = $block->getAddToCartUrl(Mage::getModel('catalog/product')->load($productId), $params);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl($backUrl);
        $this->_redirect('customer/account/login');
    }
}