<?php

class Magestore_Luckydraw_Block_Adminhtml_Code_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getData('customer_id')){
			return sprintf('<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/customer/edit/', array('id' => $row->getData('customer_id'))),
				Mage::helper('luckydraw')->__('View Customer Detail'),
				$row->getData('email')
			);
		}
		return $row->getData('email');
	}
}