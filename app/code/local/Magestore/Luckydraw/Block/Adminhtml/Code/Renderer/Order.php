<?php

class Magestore_Luckydraw_Block_Adminhtml_Code_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getData('order_id')){
			return sprintf('<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/sales_order/view/',array('order_id' => $row->getData('order_id'))),
				Mage::helper('luckydraw')->__('View Order Detail'),
				$row->getData('order_increment_id')
			);
		}
		return '';
	}
}