<?php

class Magestore_Luckydraw_Model_Mysql4_Program extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('luckydraw/program', 'program_id');
	}
}