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
class Magpleasure_Forms_Block_Form extends Magpleasure_Forms_Block_Abstract
{
    const TEMPLATE_PATH = "forms/form.phtml";

    protected $_jsObject;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    /**
     * Rendered form
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getForm()->getFormHtml($this->getSubmitUrl());
    }

    public function getHtmlId()
    {
        return $this->getForm()->getHtmlId();
    }

    public function getJsObject()
    {
        if (!$this->_jsObject){
            $this->_jsObject = "form".md5(time());
        }
        return $this->_jsObject;
    }

    /**
     * Retrieves submit url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        $params = array(
            $this->getFormParamKey() => $this->getFormId(),
        );
        $referrerUrl = $this->_helper()->getCommon()->getRequest()->getReferrerUrl();
        if ($referrerUrl && $this->_isFormView()){
            $params[Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED] = $this->_helper()->getCommon()->getCore()->urlEncode($referrerUrl);
        }
        return $this->getUrl('forms/index/submit', $params);
    }

    /**
     * Count of fields
     * @return int
     */
    public function getSize()
    {
        return $this->getForm()->getFields()->getSize();
    }

    public function getLoginUrl()
    {
        return $this->getUrl('forms/index/loginandback');
    }

    /**
     * Retrieves return url
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getUrl('forms/index/index', array($this->getFormParamKey()=>$this->getForm()->getData('id')));
    }

    public function getSecurityKey()
    {
        return $this->_helper()->getSecurity()->getSecureKey($this->getHtmlId(), $this->getJsObject());
    }

}