<?php

class Magestore_Luckydraw_Block_Adminhtml_Program_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('programGrid');
		$this->setDefaultSort('program_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection(){
		$collection = Mage::getResourceModel('luckydraw/program_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns(){
		$this->addColumn('program_id', array(
			'header'	=> Mage::helper('luckydraw')->__('ID'),
			'align'	 =>'right',
			'width'	 => '50px',
			'index'	 => 'program_id',
		));

		$this->addColumn('name', array(
			'header'	=> Mage::helper('luckydraw')->__('Program Name'),
			'align'	 => 'left',
			'index'	 => 'name',
		));

		$this->addColumn('url_key', array(
			'header'	=> Mage::helper('luckydraw')->__('URL key'),
			'index'	 => 'url_key',
		));

		$this->addColumn('min_user', array(
			'header'	=> Mage::helper('luckydraw')->__('Min User'),
			'index'	 => 'min_user',
		));

		if (!Mage::app()->isSingleStoreMode()){
			$this->addColumn('store_id', array(
				'header'	=> Mage::helper('luckydraw')->__('Store View'),
				'index'	 => 'store_id',
				'type'	=> 'store',
				'store_all'	=> true,
				'store_view'	=> true,
				'sortable'	=> false,
				'filter_condition_callback' => array($this,'_filterStoreCondition')
			));
		}

		$this->addColumn('start_time', array(
			'header'	=> Mage::helper('luckydraw')->__('Start Time'),
			'index'	 => 'start_time',
			'type'	 => 'datetime',
		));

		$this->addColumn('end_time', array(
			'header'	=> Mage::helper('luckydraw')->__('End Time'),
			'index'	 => 'end_time',
			'type'	 => 'datetime',
		));

		$this->addColumn('status', array(
			'header'	=> Mage::helper('luckydraw')->__('Status'),
			'align'		=> 'left',
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('luckydraw/program')->getStatusArray(),
		));

		$this->addColumn('action',array(
			'header'	=>	Mage::helper('luckydraw')->__('Action'),
			'type'		=> 'action',
			'getter'	=> 'getId',
			'actions'	=> array(
				array(
					'caption'	=> Mage::helper('luckydraw')->__('Edit'),
					'url'		=> array('base'=> '*/*/edit'),
					'field'		=> 'id'
				)),
			'filter'	=> false,
			'sortable'	=> false,
			'index'		=> 'stores',
			'is_system'	=> true,
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('luckydraw')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('luckydraw')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction(){
		$this->setMassactionIdField('luckydraw_id');
		$this->getMassactionBlock()->setFormFieldName('luckydraw');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'		=> Mage::helper('luckydraw')->__('Delete'),
			'url'		=> $this->getUrl('*/*/massDelete'),
			'confirm'	=> Mage::helper('luckydraw')->__('Are you sure?')
		));
		
		return $this;
	}

	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	
	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		return parent::_afterLoadCollection();
	}
	
	protected function _filterStoreCondition($collection, $column){
		if ($value = $column->getFilter()->getValue())
			$this->getCollection()->addStoreFilter($value);
		return $this;
	}
}