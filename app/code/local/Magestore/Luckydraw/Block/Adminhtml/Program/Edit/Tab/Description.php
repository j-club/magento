<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Edit_Tab_Description extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		$data = array();
		if (Mage::getSingleton('adminhtml/session')->getProgramData())
			$data = Mage::getSingleton('adminhtml/session')->getProgramData();
		elseif (Mage::registry('program_data'))
			$data = Mage::registry('program_data')->getData();
		
		$fieldset = $form->addFieldset('description_fieldset', array('legend'=>Mage::helper('luckydraw')->__('Description')));

		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
		$wysiwygConfig->addData(array(
			'add_variables'		=> false,
			'plugins'			=> array(),
			'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
			'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
			'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
			'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
		));
		
		$fieldset->addField('award_image','image',array(
			'name'		=> 'award_image',
			'label'		=> Mage::helper('luckydraw')->__('Award Image'),
			'title'		=> Mage::helper('luckydraw')->__('Award Image'),
			'note'		=> Mage::helper('luckydraw')->__('Recommend size: 297x305 pixel')
		));
		
		$fieldset->addField('short_description','editor',array(
			'name'		=> 'short_description',
			'label'		=> Mage::helper('luckydraw')->__('Short Description'),
			'title'		=> Mage::helper('luckydraw')->__('Short Description'),
			'required'	=> true,
			'wysiwyg'	=> true,
			'style'		=> 'width: 600px;',
			'config'	=> $wysiwygConfig,
		));
		/*
		$fieldset->addField('description', 'editor', array(
			'name'		=> 'description',
			'label'		=> Mage::helper('luckydraw')->__('Description'),
			'title'		=> Mage::helper('luckydraw')->__('Description'),
			'wysiwyg'	=> true,
			'style'		=> 'width: 600px;',
			'config'	=> $wysiwygConfig,
		));
		*/
		$fieldset->addField('term_condition', 'editor', array(
			'name'		=> 'term_condition',
			'label'		=> Mage::helper('luckydraw')->__('Term and Condition'),
			'title'		=> Mage::helper('luckydraw')->__('Term and Condition'),
			'wysiwyg'	=> true,
			'required'	=> true,
			'style'		=> 'width: 600px;',
			'config'	=> $wysiwygConfig,
		));
		
		if (isset($data['award_image']) && $data['award_image']){
			$data['award_image'] = 'luckydraw/program/'.$data['award_image'];
		}
		$form->setValues($data);
		return parent::_prepareForm();
	}
}