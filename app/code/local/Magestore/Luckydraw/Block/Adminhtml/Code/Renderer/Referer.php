<?php

class Magestore_Luckydraw_Block_Adminhtml_Code_Renderer_Referer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getData('refer_user')){
			return sprintf('<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/customer/edit/', array('id' => $row->getData('refer_user'))),
				Mage::helper('luckydraw')->__('View Refer Customer Detail'),
				$row->getData('refer_email')
			);
		}
		return $row->getData('refer_email');
	}
}