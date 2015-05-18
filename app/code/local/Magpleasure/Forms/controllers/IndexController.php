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
 * Frontend controller
 */
class Magpleasure_Forms_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Form Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
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
     * Retrieves current form
     *
     * @return Magpleasure_Forms_Model_Form|null
     */
    protected function _getForm()
    {
        $form = null;
        if ($formId = $this->getRequest()->getParam($this->_helper()->getFormParamKey())){
            if (is_numeric($formId)){
                $form = Mage::getModel('forms/form')->load($formId);
            } elseif (is_string($formId)) {
                $form = Mage::getModel('forms/form')->load($formId, 'id');
            }
        }
        if ($form && $form->getId()){
            return $form;
        }
        return false;
    }

    public function indexAction()
    {
        $form = $this->_getForm();
        if ($form && $form->getStatus()){
            $this->loadLayout();
            $this->renderLayout();
        } else {
            Mage::getSingleton('core/session')->addError($this->_helper()->__('This form was disabled'));
            $this->_redirect('');
        }
    }

    /**
     * Get Redirect Model
     *
     * @param string $redirectType
     * @return Magpleasure_Forms_Model_Url_Abstract|boolean
     */
    protected function _getRedirectModel($redirectType)
    {
        $redirectType = strtolower($redirectType);
        $redirectType = str_replace("_", "", $redirectType);
        try {
            return Mage::getModel("forms/url_{$redirectType}");
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _redirectForm(Magpleasure_Forms_Model_Form $form)
    {
        $redirectType = $form->getRedirectType();
        if ($redirectType && ($redirectModel = $this->_getRedirectModel($redirectType))){
            $url = $redirectModel->getUrl($form->getRedirectEntityId());
            if ($url){
                $this->_redirectUrl($url);
            } else {
                $this->_redirectReferer();
            }
        } else {
            $this->_redirectReferer();
        }
        return $this;
    }

    public function submitAction()
    {
        $post = $this->getRequest()->getPost();

        $securityKey = @$post['skey'];
        $formId = @$post['fid'];
        $objectName = @$post['oname'];

        if (!$securityKey || !$formId || !$objectName || !$this->_helper()->getSecurity()->checkSecureKey($formId, $objectName, $securityKey)){
            $this->_helper()->getCoreSession()->addError($this->_helper()->__('Security check error. Please contact to administrator.'));
            $this->_redirectReferer();
            return;
        }

        if ($form = $this->_getForm()){
            /** @var Magpleasure_Forms_Model_Form $form */
            try {
                $form->submitData($post, $this->_getRefererUrl());
                $this->_helper()->getCoreSession()->addSuccess($this->_helper()->__('Your data submitted successfully.'));
                $this->_redirectForm($form);

            } catch (Exception $e) {
                ///TODO Save form data for future load when error
                $this->_helper()->getCoreSession()->addError($this->_helper()->__('Some error. Please contact to administrator.'));
                $this->_helper()->getCommon()->getException()->logException($e);
                $this->_redirectReferer();
            }
        }
    }

    public function viewAction()
    {
        $form = $this->_getForm();
        $pid = $this->getRequest()->getParam('pid');
        if ($pid && $form && $form->getStatus() && $form->getData('list_rows_responsive')){
            $this->loadLayout();
            $this->renderLayout();
        } else {
            Mage::getSingleton('core/session')->addError($this->_helper()->__('This post was disabled'));
            $this->_redirect('');
        }
    }

    public function loginandbackAction()
    {
        $referrerUrl = $this->_getRefererUrl();
        $this->getCustomerSession()->setAfterAuthUrl($referrerUrl);
        $this->_redirect('customer/account/login');
    }
    
}