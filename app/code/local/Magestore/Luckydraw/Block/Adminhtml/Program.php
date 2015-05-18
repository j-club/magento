<?php

class Magestore_Luckydraw_Block_Adminhtml_Program extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_program';
		$this->_blockGroup = 'luckydraw';
		$this->_headerText = Mage::helper('luckydraw')->__('Program Manager');
		$this->_addButtonLabel = Mage::helper('luckydraw')->__('Add Program');
		parent::__construct();
	}
}