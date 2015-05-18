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

class Magpleasure_Forms_Model_Mysql4_Form extends Mage_Core_Model_Mysql4_Abstract
{
    const TABLE_NAME_FORM_STORE = "forms/store";

    /**
     * Mysql Date format
     */
    const DATE_FORMAT = 'YYYY-MM-dd HH:mm:ss';

    public function _construct()
    {    
        $this->_init('forms/form', 'form_id');
    }

    public function loadStores($formId)
    {
        return $this->_loadStores($formId);
    }

    /**
     * Load stores for a form
     * @param $formId
     * @return array
     */
    protected function _loadStores($formId)
    {
        $tableName = $this->getTable(self::TABLE_NAME_FORM_STORE);
        $select = new Zend_Db_Select($this->_getReadAdapter());
        $select
                ->from(array('main_table'=>$tableName), array("store_id"))
                ->where('main_table.form_id = ?', $formId);

        $result = array();
        foreach ($this->_getReadAdapter()->fetchAll($select) as $answer){
            $result[] = $answer['store_id'];
        }
        return $result;
    }

    /**
     * Update form stores
     * @param $formId
     * @param null|array $stores
     * @return Magpleasure_Forms_Model_Mysql4_Form
     */
    protected function _updateStores($formId, $stores = null)
    {
        if (!$formId){
            return $this;
        }
        if (!$stores){
            return $this;
        }
        $tableName = $this->getTable(self::TABLE_NAME_FORM_STORE);
        $deleteQuery = new Zend_Db_Expr("delete from `{$tableName}` where `form_id` = '{$formId}'");
        $this->_getWriteAdapter()->exec($deleteQuery);
        foreach ($stores as $storeId){
            $updateQuery = new Zend_Db_Expr("insert into `$tableName` (`form_id`,`store_id`) values ('{$formId}','{$storeId}')");
            $this->_getWriteAdapter()->exec($updateQuery);
        }
        return $this;
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $date = new Zend_Date();
        if (!$object->getCreatedAt()){
            $object->setCreatedAt($date->toString(self::DATE_FORMAT));
        }
        $object->setUpdatedAt($date->toString(self::DATE_FORMAT));
        parent::_beforeSave($object);
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        $this->_updateStores($object->getFormId(), $object->getStores());
        return $this;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        if ($object->getFormId()){
            $object->setStores($this->_loadStores($object->getFormId()));
        }
        return $this;
    }

}