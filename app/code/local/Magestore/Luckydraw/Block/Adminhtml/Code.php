<?php

class Magestore_Luckydraw_Block_Adminhtml_Code extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_code';
		$this->_blockGroup = 'luckydraw';
		$this->_headerText = Mage::helper('luckydraw')->__('Lucky Draw Codes');
		parent::__construct();
		$this->_removeButton('add');
	}
}