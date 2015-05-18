<?php
class Be_Sociable_Model_Log extends Mage_Core_Model_Abstract{	
	protected function _construct()	{
		parent::_construct();
		$this->_init('sociable/log');
	}
}