<?php

class Magestore_Luckydraw_Block_Adminhtml_Code_Renderer_Program extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getData('program_name')){
			return sprintf('<a href="%s" title="%s">%s</a>',
				$this->getUrl('*/adminhtml_program/edit',array('id' => $row->getData('program_id'))),
				Mage::helper('luckydraw')->__('View Program Detail'),
				$row->getData('program_name')
			);
		}
		return '';
	}
}