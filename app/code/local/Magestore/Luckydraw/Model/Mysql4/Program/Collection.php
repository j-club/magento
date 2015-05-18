<?php

class Magestore_Luckydraw_Model_Mysql4_Program_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('luckydraw/program');
	}
	
	public function addStoreFilter($store){
		if (is_object($store)) $storeId = $store->getId();
		else $storeId = $store;
		$this->getSelect()
			->where("FIND_IN_SET(0,`store_ids`) OR FIND_IN_SET(?,`store_ids`)",$storeId);
		return $this;
	}
}