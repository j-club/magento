<?php

class Magestore_Luckydraw_Model_Mysql4_Code_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isGroupSql = false;
    
	public function _construct(){
		parent::_construct();
		$this->_init('luckydraw/code');
	}
    
    public function setIsGroupCountSql($value){
        $this->_isGroupSql = $value;
        return $this;
    }
    
    public function getSelectCountSql(){
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT main_table.customer_id)');
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
}
