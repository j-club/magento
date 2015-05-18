<?php

class Magestore_Luckydraw_Model_Mysql4_Code extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('luckydraw/code', 'code_id');
	}
	
	public function getExistedCodes($programId){
		$select = $this->_getReadAdapter()->select()
			->from(array('main_table' => $this->getMainTable()),'main_table.draw_code')
			->where('main_table.program_id = ?',$programId);
		return $this->_getReadAdapter()->fetchCol($select);
	}
}