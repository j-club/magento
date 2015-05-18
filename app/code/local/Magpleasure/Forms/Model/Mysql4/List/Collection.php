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

class Magpleasure_Forms_Model_Mysql4_List_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_fieldLinks = array();

    protected $_mainTable;

    public function _construct()
    {
        parent::_construct();
        $this->_init('forms/list');
    }

    /**
     * Retrieve main table
     *
     * @return string
     */
    public function getMainTable()
    {
        if ($this->_mainTable === null) {
            $this->setMainTable($this->getResource()->getMainTable());
        }

        return $this->_mainTable;
    }

    public function setMainTable($value)
    {
        $this->_mainTable = $value;
        return $this;
    }

    public function addValueToCollection($fieldId)
    {
        $id = 'val'.$fieldId;
        $key = 'field'.$fieldId;
        $this->_fieldLinks[$key] = $id.".value";
        $valueTable = $this->getMainTable()."_value";
        $this->getSelect()->joinLeft(array($id=>$valueTable), "{$id}.list_id = main_table.list_id AND {$id}.structure_id = '{$fieldId}'", array($key=>'value'));
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @see self::_getConditionSql for $condition
     * @param string $field
     * @param null|string|array $condition
     * @return Magpleasure_Forms_Model_Mysql4_List_Collection
     */
    public function addFieldToFilter($field, $condition=null)
    {
        if (strpos($field, "field") !== false){
            parent::addFieldToFilter($this->_fieldLinks[$field], $condition);

        } else {
            parent::addFieldToFilter($field, $condition);
        }
        return $this;
    }



}