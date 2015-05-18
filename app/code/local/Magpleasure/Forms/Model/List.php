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
 * Form List
 */
class Magpleasure_Forms_Model_List extends Magpleasure_Common_Model_Abstract
{
    const TYPE_APPROVED = 'approved';
    const TYPE_REJECTED = 'rejected';
    const TYPE_PENDING = 'pending';

    protected $_values;

    public function _construct()
    {
        parent::_construct();
        $this->_init('forms/list');
    }

    /**
     * Forms
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    public function pushValue($value, $structureId)
    {
        if (!$this->getId()){
            $this->save();
        }

        /** @var Magpleasure_Forms_Model_Mysql4_List $resource  */
        $resource = $this->getResource();
        $resource->pushValue($value, $structureId, $this->getId());
        return $this;
    }

    /**
     * Form
     *
     * @return Magpleasure_Forms_Model_Form|boolean
     */
    public function getForm($storeId = null)
    {
        if ($formId = $this->getFormId()){
            /** @var $form Magpleasure_Forms_Model_Form */
            $form = Mage::getModel('forms/form')->load($formId);
            if ($form->getId()){
                if ($storeId){
                    $form->setStoreId($storeId);
                }

                return $form;
            }
        }
        return false;
    }

    /**
     * Customer
     *
     * @return bool|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($customerId = $this->getCustomerId()){
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($customer->getId()){
                return $customer;
            }
        }
        return false;
    }

    public function getValues()
    {
        if (!$this->_values){
            /** @var $collection Magpleasure_Forms_Model_Mysql4_Value_Collection */
            $collection = Mage::getModel('forms/value')->getCollection();
            $collection->addFieldToFilter('list_id', $this->getId());
            $this->_values = $collection;
        }
        return $this->_values;
    }

    public function getValue($fieldId)
    {
        foreach ($this->getValues() as $value){
            if ($value->getStructureId() == $fieldId){
                return $value->getValue();
            }
        }
        return false;
    }

    public function getFields()
    {
        return $this->getForm()->getFields();
    }

    public function getPostUrl()
    {
        if (Mage::app()->getLayout()->getArea() == 'frontend'){
            /** @var $urlModel Mage_Core_Model_Url */
            $urlModel = Mage::getSingleton('core/url');
            return $urlModel->getUrl('forms/index/view', array(
                $this->_helper()->getFormParamKey() => $this->getForm()->getId(),
                'pid' => $this->getId(),
            ));
        }
        return false;
    }

    public function getFrontendDataHtml($storeId = null)
    {
        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }

        /** @var $contentBlock Magpleasure_Forms_Block_View_Content */
        $contentBlock = Mage::app()->getLayout()->createBlock('forms/view_content');
        if ($contentBlock){
            $contentBlock->setPost($this);
            return $contentBlock->toHtml();
        }

        return false;
    }



}