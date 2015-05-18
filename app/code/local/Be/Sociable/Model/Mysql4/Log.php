<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */

class Be_Sociable_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract {
	
	protected function _construct()
    {
        $this->_init('sociable/log', 'entity_id');
    }

	
}