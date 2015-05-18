<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('program_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('luckydraw')->__('Program Information'));
	}

	protected function _beforeToHtml(){
		$this->addTab('general_section', array(
			'label'	 => Mage::helper('luckydraw')->__('Program Detail'),
			'title'	 => Mage::helper('luckydraw')->__('Program Detail'),
			'content'	 => $this->getLayout()->createBlock('luckydraw/adminhtml_program_edit_tab_form')->toHtml(),
		));
		$this->addTab('description_section', array(
			'label'	 => Mage::helper('luckydraw')->__('Description'),
			'title'	 => Mage::helper('luckydraw')->__('Description'),
			'content'	 => $this->getLayout()->createBlock('luckydraw/adminhtml_program_edit_tab_description')->toHtml(),
		));
		
		$program = Mage::registry('program_data');
		if ($program && $program->getId()){
			$this->addTab('code_section',array(
				'label'	=> Mage::helper('luckydraw')->__('Lucky Draw Code(s)'),
				'title'	=> Mage::helper('luckydraw')->__('Lucky Draw Code(s)'),
				'url'	=> $this->getUrl('*/*/code',array('_current'=>true)),
				'class'	=> 'ajax'
			));
			if ($program->getData('status') == Magestore_Luckydraw_Model_Program::STATUS_COMPLETE
			 || $program->getData('status') == Magestore_Luckydraw_Model_Program::STATUS_CLOSED)
			$this->addTab('prize_section',array(
				'label'	=> Mage::helper('luckydraw')->__('Prize Code(s)'),
				'title'	=> Mage::helper('luckydraw')->__('Prize Code(s)'),
				'url'	=> $this->getUrl('*/*/prize',array('_current'=>true)),
				'class'	=> 'ajax'
			));
		}
		
		return parent::_beforeToHtml();
	}
}