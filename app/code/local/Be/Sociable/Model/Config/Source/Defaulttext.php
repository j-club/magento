<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */

class Be_Sociable_Model_Config_Source_Defaulttext
{
	public function toOptionArray()
	{
		return array(
				array('value'=>'title', 'label'=>Mage::helper('sociable')->__('Product Title Only')),
				array('value'=>'title_short', 'label'=>Mage::helper('sociable')->__('Product Title & Product Short Description')),
				array('value'=>'short', 'label'=>Mage::helper('sociable')->__('Product Short Description Only')),
				array('value'=>'title_full', 'label'=>Mage::helper('sociable')->__('Product Title And Product Full Description')),
				array('value'=>'full', 'label'=>Mage::helper('sociable')->__('Product Full Description Only')),
		);
	}
}
