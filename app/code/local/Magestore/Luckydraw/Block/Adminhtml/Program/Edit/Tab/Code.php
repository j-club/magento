<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Edit_Tab_Code extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('luckydrawcodeGrid');
		$this->setDefaultSort('code_id');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
	}
	
	protected function _prepareCollection(){
		$programId = $this->getRequest()->getParam('id');
		$collection = Mage::getResourceModel('luckydraw/code_collection')
			->addFieldToFilter('program_id',$programId);
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns(){
		$this->addColumn('code_id', array(
			'header'	=> Mage::helper('luckydraw')->__('ID'),
			'align'	 =>'right',
			'width'	 => '50px',
			'index'	 => 'code_id',
		));
		
		$this->addColumn('luckydraw_code', array(
			'header'	=> Mage::helper('luckydraw')->__('Lucky Draw Code'),
			'align'	 => 'right',
			'index'	 => 'draw_code',
		));
		
		$this->addColumn('luckycode_email', array(
			'header'	=> Mage::helper('luckydraw')->__('Customer'),
			'align'	 => 'right',
			'index'	 => 'email',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_customer'
		));
		
		$this->addColumn('luckycode_refer_email', array(
			'header'	=> Mage::helper('luckydraw')->__('Referrer'),
			'align'	 => 'right',
			'index'	 => 'refer_email',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_referer'
		));
		
		$this->addColumn('luckycode_created_time', array(
			'header'	=> Mage::helper('luckydraw')->__('Create Time'),
			'index'	 => 'created_time',
			'type'	 => 'datetime',
		));
		
		$this->addColumn('luckycode_expired_time', array(
			'header'	=> Mage::helper('luckydraw')->__('Expire Time'),
			'index'	 => 'expired_time',
			'type'	 => 'datetime',
		));
		
		$this->addColumn('luckycode_status',array(
			'header'	=> Mage::helper('luckydraw')->__('Status'),
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('luckydraw/code')->getStatusArray()
		));
		
		$this->addColumn('luckycode_credit_rate',array(
			'header'	=> Mage::helper('luckydraw')->__('Credit'),
			'index'		=> 'credit_rate',
			'type'		=> 'price',
			'currency_code'	=> Mage::app()->getBaseCurrencyCode(),
		));
		
		$this->addColumn('luckycode_order_increment_id', array(
			'header'	=> Mage::helper('luckydraw')->__('Spend on order'),
			'align'	 => 'right',
			'index'	 => 'order_increment_id',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_order'
		));
		
		return parent::_prepareColumns();
	}
	
	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}
	
	public function getRowUrl($row){
		return '';
	}
	
	public function getGridUrl(){
		if (!$this->hasData('grid_url')){
			$gridUrl = $this->getUrl('*/*/codeGrid',array(
				'_current'	=> true,
				'id'		=> $this->getRequest()->getParam('id')
			));
			$this->setData('grid_url',$gridUrl);
		}
		return $this->getData('grid_url');
	}
}