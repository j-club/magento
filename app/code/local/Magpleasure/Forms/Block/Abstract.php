<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE-CE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE-CE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Magpleasure does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Magpleasure does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Magpleasure
 * @package    Magpleasure_Forms
 * @version    1.0.5
 * @copyright  Copyright (c) 2011-2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE-CE.txt
 */

/**
 * Form
 */
class Magpleasure_Forms_Block_Abstract extends Magpleasure_Common_Block_Template implements Mage_Widget_Block_Interface
{

    /** @var Magpleasure_Forms_Model_Form */
    protected $_form = null;

    /**
     * Form Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('forms');
    }

    /**
     * Customer session
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return $this->_helper()->getCustomerSession();
    }

    /**
     * Default form param
     * @return string
     */
    public function getFormParamKey()
    {
        return $this->_helper()->getFormParamKey();
    }

    public function getFormId()
    {
        if ($this->getData('form_id')){
            return $this->getData('form_id');
        } else {
            return $this->getRequest()->getParam($this->getFormParamKey());
        }
    }

    /**
     * Retrieves current form
     *
     * @return Magpleasure_Forms_Model_Form|null
     */
    public function getForm()
    {
        if (!$this->_form) {
            $form = null;
            if ($formId = $this->getFormId()){
                if (is_numeric($formId)){
                    $form = Mage::getModel('forms/form')->load($formId);
                } elseif (is_string($formId)) {
                    $form = Mage::getModel('forms/form')->load($formId, 'id');
                }
            }
            if ($form && $form->getId()){
                $this->_form = $form;
            }
        }
        return $this->_form;
    }

    /**
     * Can display block
     *
     * @return bool
     */
    public function canDisplay()
    {
        return (
                $this->getForm() &&
                $this->getForm()->getId() &&
                $this->getForm()->getStatus() &&
                (Mage::app()->isSingleStoreMode() || in_array(Mage::app()->getStore()->getId(), $this->getForm()->getStores()))
        );
    }

    /**
     * Page Config
     *
     * @return Mage_Page_Model_Config
     */
    protected function _getPageConfig()
    {
        return Mage::getSingleton('page/config');
    }

    public function setFormPageLayout()
    {
        if ($layoutCode = $this->getForm()->getLayout()){

            /** @var $root Mage_Page_Block_Html */
            $root = $this->getLayout()->getBlock('root');
            if ($root){
                $layout = $this->_getPageConfig()->getPageLayout($layoutCode);
                if ($layout){
                    $root->setTemplate($layout->getTemplate());
                }
            }

        }
    }

    /**
     * Check url to be used as internal
     *
     * @param   string $url
     * @return  bool
     */
    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            /**
             * Url must start from base secure or base unsecure url
             */
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function _getRefererUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ($url = $this->getRequest()->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }

        $refererUrl = $this->_helper()->getCommon()->getCore()->escapeUrl($refererUrl);

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }

    protected function _isFormView()
    {
        return (
            ($this->getRequest()->getModuleName() == "forms") &&
            ($this->getRequest()->getControllerName() == "index") &&
            ($this->getRequest()->getActionName() == "index")
        );
    }

}