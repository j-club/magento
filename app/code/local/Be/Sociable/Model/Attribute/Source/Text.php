<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */

class Be_Sociable_Model_Attribute_Source_Text extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
				array('value'=>'', 'label'=>Mage::helper('sociable')->__('Default')),
				array('value'=>'title', 'label'=>Mage::helper('sociable')->__('Product Title Only')),
				array('value'=>'title_short', 'label'=>Mage::helper('sociable')->__('Product Title & Product Short Description')),
				array('value'=>'short', 'label'=>Mage::helper('sociable')->__('Product Short Description Only')),
				array('value'=>'title_full', 'label'=>Mage::helper('sociable')->__('Product Title And Product Full Description')),
				array('value'=>'full', 'label'=>Mage::helper('sociable')->__('Product Full Description Only')),
			);
        }
        return $this->_options;
    }
}
