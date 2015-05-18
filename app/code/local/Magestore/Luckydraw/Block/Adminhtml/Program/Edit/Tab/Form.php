<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		$data = array();
		if (Mage::getSingleton('adminhtml/session')->getProgramData())
			$data = Mage::getSingleton('adminhtml/session')->getProgramData();
		elseif (Mage::registry('program_data'))
			$data = Mage::registry('program_data')->getData();
		
		$fieldset = $form->addFieldset('general_fieldset', array('legend'=>Mage::helper('luckydraw')->__('Program Detail')));

		$fieldset->addField('name', 'text', array(
			'label'		=> Mage::helper('luckydraw')->__('Name'),
			'title'		=> Mage::helper('luckydraw')->__('Name'),
			'required'	=> true,
			'name'		=> 'name',
		));
		
		$urlKeyInfo = array(
			'label'		=> Mage::helper('luckydraw')->__('URL Key'),
			'title'		=> Mage::helper('luckydraw')->__('URL Key'),
			'required'	=> true,
			'name'		=> 'url_key',
		);
		if (isset($data['url_key'])){
			$programUrl = Mage::getUrl(null,array('_direct' => $data['url_key']));
			$urlKeyInfo['note'] = '<a href="'.$programUrl.'" title="'.Mage::helper('luckydraw')->__('Program URL').'" target="_blank">'.$programUrl.'</a>';
		}
		$fieldset->addField('url_key', 'text', $urlKeyInfo);
		
		if (Mage::app()->isSingleStoreMode()){
			$fieldset->addField('store_id','hidden',array(
				'name'	=> 'stores[]',
				'value'	=> Mage::app()->getStore(true)->getId(),
			));
			$data['store_id'] = Mage::app()->getStore(true)->getId();
		} else {
			$fieldset->addField('store_id','multiselect',array(
				'name'	=> 'stores[]',
				'label'		=> Mage::helper('luckydraw')->__('Store View'),
				'title'		=> Mage::helper('luckydraw')->__('Store View'),
				'required'	=> true,
				'values'	=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
			));
		}
		
		$datetimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
		$note = $this->__('The current server time is').': '.$this->formatTime(now(),Mage_Core_Model_Locale::FORMAT_TYPE_SHORT,true);
		$fieldset->addField('start_time_obj','date',array(
			'name'	=> 'start_time',
			'label'	=> Mage::helper('luckydraw')->__('Start Time'),
			'title'	=> Mage::helper('luckydraw')->__('Start Time'),
			'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
			'input_format'	=> Varien_Date::DATETIME_INTERNAL_FORMAT,
			'format'	=> $datetimeFormat,
			'time'	=> true,
			'required'	=> true,
		));
		
		$fieldset->addField('end_time_obj','date',array(
			'name'	=> 'end_time',
			'label'	=> Mage::helper('luckydraw')->__('End Time'),
			'title'	=> Mage::helper('luckydraw')->__('End Time'),
			'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
			'input_format'	=> Varien_Date::DATETIME_INTERNAL_FORMAT,
			'format'	=> $datetimeFormat,
			'time'	=> true,
			'required'	=> true,
			'note'	=> $note,
		));
		
		$fieldset->addField('code_length', 'text', array(
			'label'		=> Mage::helper('luckydraw')->__('Number of digits in lucky draw code'),
			'title'		=> Mage::helper('luckydraw')->__('Number of digits in lucky draw code'),
			'required'	=> true,
			'name'		=> 'code_length',
			'class'		=> 'validate-greater-than-zero',
			'note'		=> Mage::helper('luckydraw')->__('From 3 to 8 is recommended for the best display')
		));
		if (!isset($data['code_length'])) $data['code_length'] = 5;
		
		$fieldset->addField('min_user', 'text', array(
			'label'		=> Mage::helper('luckydraw')->__('Minimum number of participants to draw'),
			'title'		=> Mage::helper('luckydraw')->__('Minimum number of participants to draw'),
			'name'		=> 'min_user',
		));
		
		$fieldset->addField('auto_prize', 'select', array(
			'label'		=> Mage::helper('luckydraw')->__('Auto draw after program ends'),
			'title'		=> Mage::helper('luckydraw')->__('Auto draw after program ends'),
			'name'		=> 'auto_prize',
			'values'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
		));
		
		$fieldset->addField('prize_code', 'text', array(
			'label'		=> Mage::helper('luckydraw')->__('Prize Code'),
			'title'		=> Mage::helper('luckydraw')->__('Prize Code'),
			'name'		=> 'prize_code',
            'style'     => 'margin-right: 22px;',
            'after_element_html'    => '
                <img id="prize-helper" style="position:relative;float:left;top:-18px;left:285px;cursor:pointer;" src="'
                . $this->getSkinUrl('images/fam_help.gif') . '" />
                <div style="display:none;width:320px;border:1px solid #ccc;padding:10px;background-color:#eee;" id="prize-helper-tip">'.
                $this->__('Please note that Prize Code is used to define the winner of lucky draw program. As the Prize Code can be set in the backend at your convenience, it may bring to your customers thoughts of an unfair result or a trick. To avoid this unexpected situation, we recommend that you should set the Prize Code based on the result of other reputed organizations in your region. For example, you can use the lottery result of the day that your lucky draw program ends. Also, this result must be announced when the program expires.')
                .'</div>
                <script type="text/javascript">new Tooltip("prize-helper","prize-helper-tip");</script>',
			'note'		=> Mage::helper('luckydraw')->__('This code is used to auto-define the winner. Winner is the person owning the prize code or the code closest to it.')
		));
		
		$fieldset->addField('prize_days', 'text', array(
			'label'		=> Mage::helper('luckydraw')->__('Valid time of lucky draw code'),
			'title'		=> Mage::helper('luckydraw')->__('Valid time of lucky draw code'),
			'name'		=> 'prize_days',
			'note'		=> Mage::helper('luckydraw')->__('(Days). Enter zero or leave blank for unlimited time')
		));
		
		$fieldset->addField('credit_rate', 'text', array(
			'label'		=> Mage::helper('luckydraw')->__('A lucky code is redeemed for'),
			'title'		=> Mage::helper('luckydraw')->__('A lucky code is redeemed for'),
			'name'		=> 'credit_rate',
			'note'		=> '['.Mage::app()->getBaseCurrencyCode().']. '.Mage::helper('luckydraw')->__('The credit that a failed code received.')
		));
		
		if (isset($data['created_time'])){
			$fieldset->addField('created_time','note',array(
				'label'		=> Mage::helper('luckydraw')->__('Created Time'),
				'title'		=> Mage::helper('luckydraw')->__('Created Time'),
				'text'		=> '<strong>'.$this->formatDate($data['created_time'],Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,true).'</strong>',
			));
		}
		if (isset($data['status'])){
			$fieldset->addField('status','note',array(
				'label'		=> Mage::helper('luckydraw')->__('Status'),
				'title'		=> Mage::helper('luckydraw')->__('Status'),
				'text'		=> '<strong>'.Mage::getSingleton('luckydraw/program')->getLabelByStatus($data['status'],true).'</strong>',
			));
		}
		
		$form->setValues($data);
		return parent::_prepareForm();
	}
}