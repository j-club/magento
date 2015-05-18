<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */


class Be_Sociable_Model_Config_Source_Shortener
{
	public function toOptionArray()
	{
		return array(
				array('value'=>'is.gd', 'label'=>Mage::helper('adminhtml')->__('Is.gd')),
				array('value'=>'bit.ly', 'label'=>Mage::helper('adminhtml')->__('Bit.ly')),
				array('value'=>'j.mp', 'label'=>Mage::helper('adminhtml')->__('J.mp')),
				array('value'=>'tr.im', 'label'=>Mage::helper('adminhtml')->__('Tr.im')),
				array('value'=>'su.pr', 'label'=>Mage::helper('adminhtml')->__('Su.pr')),
				array('value'=>'ow.ly', 'label'=>Mage::helper('adminhtml')->__('Ow.ly')),
				array('value'=>'3.ly', 'label'=>Mage::helper('adminhtml')->__('3.ly')),
				array('value'=>'tinyurl', 'label'=>Mage::helper('adminhtml')->__('TinyURL.com')),
				array('value'=>'yourls', 'label'=>Mage::helper('adminhtml')->__('YOURLS')),
		);
		
		
		
	}
}
