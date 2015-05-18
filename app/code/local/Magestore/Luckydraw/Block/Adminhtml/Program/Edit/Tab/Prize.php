<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Edit_Tab_Prize extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('luckydrawcodeGrid');
		$this->setDefaultSort('prizecode_id');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
	}
	
	protected function _prepareCollection(){
		$programId = $this->getRequest()->getParam('id');
		$collection = Mage::getResourceModel('luckydraw/code_collection')
			->addFieldToFilter('program_id',$programId)
			->addFieldToFilter('is_prize',1);
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns(){
		$this->addColumn('prizecode_id', array(
			'header'	=> Mage::helper('luckydraw')->__('ID'),
			'align'	 =>'right',
			'width'	 => '50px',
			'index'	 => 'code_id',
		));
		
		$this->addColumn('prizedraw_code', array(
			'header'	=> Mage::helper('luckydraw')->__('Lucky Draw Code'),
			'align'	 => 'right',
			'index'	 => 'draw_code',
		));
		
		$this->addColumn('prizecode_email', array(
			'header'	=> Mage::helper('luckydraw')->__('Customer'),
			'align'	 => 'right',
			'index'	 => 'email',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_customer'
		));
		
		$this->addColumn('prizecode_created_time', array(
			'header'	=> Mage::helper('luckydraw')->__('Create Time'),
			'index'	 => 'created_time',
			'type'	 => 'datetime',
		));
		
		$this->addColumn('prizecode_expired_time', array(
			'header'	=> Mage::helper('luckydraw')->__('Expire Time'),
			'index'	 => 'expired_time',
			'type'	 => 'datetime',
		));
		
		$this->addColumn('prizecode_status',array(
			'header'	=> Mage::helper('luckydraw')->__('Status'),
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('luckydraw/code')->getStatusArray()
		));
		/*
		$this->addColumn('prizecode_order_increment_id', array(
			'header'	=> Mage::helper('luckydraw')->__('Spend on order'),
			'align'	 => 'right',
			'index'	 => 'order_increment_id',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_order'
		));
		*/
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row){
		return '';
	}
	
	public function getGridUrl(){
		if (!$this->hasData('grid_url')){
			$gridUrl = $this->getUrl('*/*/prizeGrid',array(
				'_current'	=> true,
				'id'		=> $this->getRequest()->getParam('id')
			));
			$this->setData('grid_url',$gridUrl);
		}
		return $this->getData('grid_url');
	}
}