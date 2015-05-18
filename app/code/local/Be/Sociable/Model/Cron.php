<?php
class Be_Sociable_Model_Cron extends Mage_Core_Model_Abstract{	
	protected function _construct()	{
		parent::_construct();
		$this->_init('sociable/cron');
	}
}