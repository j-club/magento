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

class Magpleasure_Forms_Helper_Notifier extends Mage_Core_Helper_Abstract
{
    /**
     * Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    public function confNotifyAdmin($storeId)
    {
        return Mage::getStoreConfig('forms/notify_admin/enabled', $storeId);
    }

    public function getBackLinkUrl(Magpleasure_Forms_Model_List $submit)
    {
        /** @var $url Mage_Adminhtml_Model_Url */
        $url = Mage::getSingleton('adminhtml/url');
        return $url->getUrl('forms_admin/adminhtml_forms/edit', array(
                                    'id' => $submit->getForm()->getId(),
                                    'tab'=>'forms_tabs_list_post',
                                ));
    }

    public function notifyAdmin(Magpleasure_Forms_Model_List $submit, $formUrl = false)
    {
        $storeId = $submit->getStoreId();
        if (!$this->confNotifyAdmin($storeId)){
            return $this;
        }

        # Set IS_EMAIL flag
        Mage::register('is_email', true, true);

        $template = Mage::getStoreConfig('forms/notify_admin/template', $storeId);
        $sender = Mage::getStoreConfig('forms/notify_admin/sender', $storeId);
        $customer = $submit->getCustomer();
        $receivers = explode(",", Mage::getStoreConfig('forms/notify_admin/receiver', $storeId));

        $vars = array(
            'customer_name' => $customer ? $customer->getName() : $this->_helper()->__("Customer"),
            'form' => $submit->getForm(),
            'form_url' => $formUrl ? $formUrl : $submit->getForm()->getUrl(),
            'submit' => $submit,
            'store' => Mage::app()->getStore($storeId),
            'back_link' => $this->getBackLinkUrl($submit),
            'need_approve' => $submit->getForm()->getData('approve_require') ? true : false,
        );

        if ($submit->getForm($storeId)->getData('add_data_to_email')){
            $vars['show_data'] = 1;
            $vars['form_data_html'] = $submit->getFrontendDataHtml($storeId);
        }

        foreach ($receivers as $receiver){
            /** @var Mage_Core_Model_Email_Template $mailTemplate  */
            $mailTemplate = Mage::getModel('core/email_template');
            try {
                $mailTemplate
                    ->setDesignConfig(array('area' => 'frontend', 'store'=>$storeId))
                    ->sendTransactional(
                    $template,
                    $sender,
                    trim($receiver),
                    $this->_helper()->__("Administrator"),
                    $vars,
                    $storeId
                )
                ;
            } catch (Exception $e) {
                $this->_helper()
                    ->getCommonHelper()
                    ->getException()
                    ->logException($e);
            }
        }

        return $this;
    }

}