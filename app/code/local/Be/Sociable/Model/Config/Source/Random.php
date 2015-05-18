<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */

class Be_Sociable_Model_Config_Source_Random
{
	public function toOptionArray()
	{
		return array(
				array('value'=>0, 'label'=>Mage::helper('adminhtml')->__('No')),
				array('value'=>1, 'label'=>Mage::helper('adminhtml')->__('1 per day')),
				array('value'=>5, 'label'=>Mage::helper('adminhtml')->__('5 per day')),
				array('value'=>10, 'label'=>Mage::helper('adminhtml')->__('10 per day')),
				array('value'=>20, 'label'=>Mage::helper('adminhtml')->__('20 per day')),
		);
	}
}
