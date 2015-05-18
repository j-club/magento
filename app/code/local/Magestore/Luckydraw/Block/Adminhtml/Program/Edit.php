<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct(){
		parent::__construct();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'luckydraw';
		$this->_controller = 'adminhtml_program';
		
		
		$program = Mage::registry('program_data');
		if ($program->getStatus() < Magestore_Luckydraw_Model_Program::STATUS_COMPLETE){
			$this->_addButton('pause', array(
				'label'		=> Mage::helper('luckydraw')->__('Pause'),
				'class'		=> 'delete',
				'onclick'	=> 'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                    .'\', \'' . $this->getUrl('*/*/pause',array('id' => $program->getId())) . '\')',
			),0);
		}
		if ($program->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_PAUSED){
			$this->_addButton('resume', array(
				'label'		=> Mage::helper('luckydraw')->__('Resume'),
				'onclick'	=> 'setLocation(\'' . $this->getUrl('*/*/resume',array('id' => $program->getId())) . '\')',
				'class'		=> 'save',
			),0);
		}
		
		if ($program->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_DIALING){
			$this->_addButton('dial', array(
				'label'		=> Mage::helper('luckydraw')->__('Complete'),
				'onclick'	=> 'editForm.submit(\'' . $this->getUrl('*/*/dial',array('id' => $program->getId())) . '\')',
				'class'		=> 'save',
			),0);
		}
		
		if ($program->getStatus() != Magestore_Luckydraw_Model_Program::STATUS_COMPLETE){
			$this->_addButton('saveandcontinue', array(
				'label'		=> Mage::helper('adminhtml')->__('Save And Continue Edit'),
				'onclick'	=> 'saveAndContinueEdit()',
				'class'		=> 'save',
			), 7);
		} else {
			$this->_removeButton('save');
		}

		$this->_formScripts[] = "
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}

	public function getHeaderText(){
		if(Mage::registry('program_data') && Mage::registry('program_data')->getId())
			return Mage::helper('luckydraw')->__("Edit Program '%s'", $this->htmlEscape(Mage::registry('program_data')->getName()));
		return Mage::helper('luckydraw')->__('Add Program');
	}
}