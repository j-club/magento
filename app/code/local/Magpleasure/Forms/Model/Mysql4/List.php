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

class Magpleasure_Forms_Model_Mysql4_List extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Mysql Date format
     */
    const DATE_FORMAT = 'YYYY-MM-dd HH:mm:ss';

    public function _construct()
    {
        $this->_init('forms/list', 'list_id');
    }

    public function loadValues()
    {
        return $this;
    }

    public function pushValue($value, $structureId, $listId)
    {
        $bind = array(
            'value' => $value,
            'structure_id' => $structureId,
            'list_id'   => $listId,
        );
        $this->_getWriteAdapter()->beginTransaction();
        $this->_getWriteAdapter()->insert($this->getMainTable()."_value", $bind);
        $this->_getWriteAdapter()->commit();
        return $this;
    }

    protected function _beforeSave(Mage_Core_Model_Abstract  $object)
    {
        $date = new Zend_Date();
        if (!$object->getId()){
            $object->setCreatedAt($date->toString(self::DATE_FORMAT));
            $object->setStoreId(Mage::app()->getStore()->getId());
        }
        parent::_beforeSave($object);
    }

}