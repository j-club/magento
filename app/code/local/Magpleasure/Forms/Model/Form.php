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

class Magpleasure_Forms_Model_Form extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_DELETED = 2;

    const REDIRECT_SELF = 'self';
    const REDIRECT_FORM = 'form';
    const REDIRECT_CMS_PAGE = 'cms_page';
    const REDIRECT_PRODUCT = 'product';
    const REDIRECT_CATEGORY = 'category';
    const REDIRECT_BLOG_PAGE = 'blog_post';

    protected $_storeId = false;

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
     * Default form param
     * @return string
     */
    public function getFormParamKey()
    {
        return $this->_helper()->getFormParamKey();
    }

    /**
     * Retrieves Zend_Form Type
     * @param string $type
     * @return string
     */
    protected function _parseType($type)
    {
        return $type;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('forms/form');
    }    

    public function setStoreId($value)
    {
        $this->_storeId = $value;
        return $this;
    }

    /**
     * Retrieves collection for this field
     * @param string $direction
     * @return Magpleasure_Forms_Model_Mysql4_Structure_Collection
     */
    public function getFields($direction = Varien_Db_Select::SQL_ASC)
    {
        /** @var Magpleasure_Forms_Model_Mysql4_Structure_Collection $fields  */
        $fields = Mage::getModel('forms/structure')->getCollection();
        $fields->addOrdering($direction)
               ->addFormFilter($this->getId());

        return $fields;
    }

    /**
     * Retrieves "Is locked" trigger
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->_getData('is_locked');
    }

    /**
     * Prepare url
     *
     * @param $url
     * @return Mage_Core_Block_Abstract|Magpleasure_Forms_Block_Render_Form
     */
    public function prepareFormRenderer($url)
    {
        /** @var Magpleasure_Forms_Block_Render_Form $form  */
        $form = Mage::app()->getLayout()->createBlock('forms/render_form');
        $form->addAttributes(
            array(
                'action'=>$url,
                'id'=>$this->getHtmlId(),
                'method'=>'post',
            )
        );


        foreach ($this->getFields() as $field){
            /** @var Magpleasure_Forms_Model_Structure $field */
            $form->addElement($field);
        }
        return $form;
    }

    /**
     * Retrieves rendered form fields
     * @param string $url
     * @return string
     */
    public function getFormHtml($url)
    {
        return $this->prepareFormRenderer($url)->toHtml();
    }

    public function getHtmlId()
    {
        return $this->getData('id')."_form";
    }

    /**
     * Store submitted data to DB
     *
     * @param array $post
     * @param string|null $fromUrl
     * @return Magpleasure_Forms_Model_Form
     */
    public function submitData($post, $fromUrl = null)
    {
        /** @var Magpleasure_Forms_Model_List $list  */
        $list = Mage::getModel('forms/list');
        $list->setFormId($this->getFormId());

        if ($this->_helper()->getCustomerSession()->isLoggedIn()){
            $list->setCustomerId( $this->_helper()->getCustomerSession()->getCustomerId() );
        }

        if ($this->getApproveRequire()){
            $list->setStatus(Magpleasure_Forms_Model_List::TYPE_PENDING);
        } else {
            $list->setStatus(Magpleasure_Forms_Model_List::TYPE_APPROVED);
        }

        foreach ($this->getFields() as $field){
            /** @var  Magpleasure_Forms_Model_Structure $field */
            $key = "field".$field->getId();

            if (isset($post[$key])){
                $value = $post[$key];
                $value = $field->prepareValue($value);
                $list->pushValue($value, $field->getId());
            }
        }
        $this->_helper()->getNotifier()->notifyAdmin($list, $fromUrl);
        return $this;
    }

    public function getMaxId()
    {
        $readAdapter = $this->getResource()->getReadConnection();
        $select = new Zend_Db_Select($readAdapter);
        $select
            ->from($this->getResource()->getMainTable()."_structure", array('structure_id'))
            ->order("structure_id DESC")
            ->limit(1)
            ;

        try {
            $result = $readAdapter->fetchOne($select);
            if ($result){
                return $result;
            }
        } catch (Exception $e){
            Mage::logException($e);
        }

        return 1;
    }

    public function getUrl()
    {
        /** @var $urlModel Mage_Core_Model_Url */
        $urlModel = Mage::getSingleton('core/url');
        return $urlModel->getUrl('forms/index/index', array($this->getFormParamKey()=>$this->getData('id')));
    }

    public function toOptionArray()
    {
        /** @var $collection Magpleasure_Forms_Model_Mysql4_Form_Collection */
        $collection = $this->getCollection();
        $collection
            ->addFieldToFilter('status', Magpleasure_Forms_Model_Form::STATUS_ENABLED)
            ->setOrder('form_id', Varien_Db_Select::SQL_DESC)
            ;

        $forms = array();
        foreach ($collection as $form){
            $forms[$form->getId()] = $form->getName();
        }
        return $this->_helper()->getCommon()->getArrays()->paramsToValueLabel($forms);
    }


    public function delete()
    {
        if ($this->getId()){
            $this->setStatus(Magpleasure_Forms_Model_Form::STATUS_DELETED)->save();
        }

        return $this;
    }

    public function purge()
    {
        parent::delete();
    }

    public function restore()
    {
        if ($this->getId()){
            $this->setStatus(Magpleasure_Forms_Model_Form::STATUS_ENABLED)->save();
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        # Prepare redirect after entity
        $redirectType = $this->getData('redirect_type');
        if ($redirectType != self::REDIRECT_SELF){
            $this->setData("{$redirectType}_entity_id", $this->getData('redirect_entity_id'));
        }

        return $this;
    }

    protected function _beforeSave()
    {
        # Prepare redirect after entity
        $redirectType = $this->getData('redirect_type');
        if ($redirectType != self::REDIRECT_SELF){
            $this->setData('redirect_entity_id', $this->getData("{$redirectType}_entity_id"));
        } else {
            $this->setData('redirect_entity_id', 0);
        }

        parent::_beforeSave();
        return $this;
    }

    /**
     * List Colleciton
     *
     * @param int|null $storeId
     * @param string|null $status
     * @return Magpleasure_Forms_Model_Mysql4_List_Collection
     */
    public function getListCollection($storeId = null, $status = Magpleasure_Forms_Model_List::TYPE_APPROVED)
    {
        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }

        /** @var $collection Magpleasure_Forms_Model_Mysql4_List_Collection */
        $collection = Mage::getModel('forms/list')->getCollection();
        $collection->addFieldToFilter('form_id', $this->getFormId());
        $collection->addFieldToFilter('status', $status);
        if (!Mage::app()->isSingleStoreMode()){
            $collection->addFieldToFilter('store_id', $storeId);
        }
        foreach ($this->getFields() as $field){
            $collection->addValueToCollection($field->getId());
        }
        return $collection;
    }
}