<?php

class Magestore_Luckydraw_Block_Adminhtml_Code_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('luckydrawcodeGrid');
		$this->setDefaultSort('code_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection(){
		$collection = Mage::getResourceModel('luckydraw/code_collection');
		$collection->getSelect()->joinLeft(
			array('p' => $collection->getTable('luckydraw/program')),
			'main_table.program_id = p.program_id',
			array('program_name' => 'name')
		);
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
		
		$this->addColumn('program_name', array(
			'header'	=> Mage::helper('luckydraw')->__('Program'),
			'align'	 => 'right',
			'index'	 => 'program_name',
			'filter_index'	=> 'p.name',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_program'
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
		
		$this->addColumn('luckycode_is_prize',array(
			'header'	=> Mage::helper('luckydraw')->__('Is Prize'),
			'index'		=> 'is_prize',
			'type'		=> 'options',
			'options'	=> array(
				'0'		=> Mage::helper('adminhtml')->__('No'),
				'1'		=> Mage::helper('adminhtml')->__('Yes'),
			)
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
			'filter_index'	=> 'main_table.created_time',
			'type'	 => 'datetime',
		));
		
		$this->addColumn('luckycode_expired_time', array(
			'header'	=> Mage::helper('luckydraw')->__('Expire Time'),
			'index'	 => 'expired_time',
			'filter_index'	=> 'main_table.expired_time',
			'type'	 => 'datetime',
		));
		
		$this->addColumn('luckycode_status',array(
			'header'	=> Mage::helper('luckydraw')->__('Status'),
			'index'		=> 'status',
			'filter_index'	=> 'main_table.status',
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('luckydraw/code')->getStatusArray()
		));
		
		$this->addColumn('luckycode_credit_rate',array(
			'header'	=> Mage::helper('luckydraw')->__('Credit'),
			'index'		=> 'credit_rate',
			'filter_index'	=> 'main_table.credit_rate',
			'type'		=> 'price',
			'currency_code'	=> Mage::app()->getBaseCurrencyCode(),
		));
		
		$this->addColumn('luckycode_order_increment_id', array(
			'header'	=> Mage::helper('luckydraw')->__('Spend on order'),
			'align'	 => 'right',
			'index'	 => 'order_increment_id',
			'filter_index'	=> 'main_table.order_increment_id',
			'renderer'	=> 'luckydraw/adminhtml_code_renderer_order'
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('luckydraw')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('luckydraw')->__('XML'));
		
		return parent::_prepareColumns();
	}
	
	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}
	
	public function getRowUrl($row){
		return '';
	}
	
	public function getCsv(){
		$csv = '';
		$this->_isExport = true;
		$this->_prepareGrid();
		$this->getCollection()->getSelect()->limit();
		$this->getCollection()->setPageSize(0);
		$this->getCollection()->load();
		$this->_afterLoadCollection();
		$this->addColumn('code_id',array('index' => 'code_id'));
		$this->addColumn('program_name',array('index' => 'program_name'));
		$this->addColumn('luckydraw_code',array('index' => 'draw_code'));
		$this->addColumn('luckycode_email',array('index' => 'email'));
		$this->addColumn('luckycode_is_prize',array('index' => 'is_prize','type'=>'options',
			'options'	=> array(
				'0'		=> Mage::helper('adminhtml')->__('No'),
				'1'		=> Mage::helper('adminhtml')->__('Yes'),
			)));
		$this->addColumn('luckycode_refer_email',array('index' => 'refer_email'));
		$this->addColumn('luckycode_created_time',array('index' => 'created_time'));
		$this->addColumn('luckycode_expired_time',array('index' => 'expired_time'));
		$this->addColumn('luckycode_status',array('index' => 'status',	'type'		=> 'options',
												'options'	=> Mage::getSingleton('luckydraw/code')->getStatusArray()));
		$this->addColumn('luckycode_credit_rate',array('index' => 'credit_rate'));
		$this->addColumn('luckycode_order_increment_id',array('index' => 'order_increment_id'));

		$data = array();
		foreach ($this->_columns as $column)
			if (!$column->getIsSystem())
				$data[] = '"'.$column->getIndex().'"';
		
		$csv .= implode(',', $data)."\n";

		foreach ($this->getCollection() as $item) {
			$data = array();
			foreach ($this->_columns as $column){
				if (!$column->getIsSystem()){
					$data[] = '"'.str_replace(array('"', '\\', chr(13), chr(10)), array('""', '\\\\', '', '\n'), $column->getRowFieldExport($item)).'"';
				}
			}
			$csv .= implode(',', $data)."\n";
		}

		if ($this->getCountTotals()){
			$data = array();
			foreach ($this->_columns as $column) {
				if (!$column->getIsSystem()) {
					$data[] = '"'.str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($this->getTotals())).'"';
				}
			}
			$csv.= implode(',', $data)."\n";
		}

		return $csv;
	}
	
	public function getXml()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
		$this->addColumn('code_id',array('index' => 'code_id'));
		$this->addColumn('program_name',array('index' => 'program_name'));
		$this->addColumn('luckydraw_code',array('index' => 'draw_code'));
		$this->addColumn('luckycode_email',array('index' => 'email'));
		$this->addColumn('luckycode_is_prize',array('index' => 'is_prize','type'=>'options',
			'options'	=> array(
				'0'		=> Mage::helper('adminhtml')->__('No'),
				'1'		=> Mage::helper('adminhtml')->__('Yes'),
			)));
		$this->addColumn('luckycode_refer_email',array('index' => 'refer_email'));
		$this->addColumn('luckycode_created_time',array('index' => 'created_time'));
		$this->addColumn('luckycode_expired_time',array('index' => 'expired_time'));
		$this->addColumn('luckycode_status',array('index' => 'status',	'type'		=> 'options',
												'options'	=> Mage::getSingleton('luckydraw/code')->getStatusArray()));
		$this->addColumn('luckycode_credit_rate',array('index' => 'credit_rate'));
		$this->addColumn('luckycode_order_increment_id',array('index' => 'order_increment_id'));
        $indexes = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $indexes[] = $column->getIndex();
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml.= '<items>';
        foreach ($this->getCollection() as $item) {
            $xml.= $item->toXml($indexes);
        }
        if ($this->getCountTotals())
        {
            $xml.= $this->getTotals()->toXml($indexes);
        }
        $xml.= '</items>';
        return $xml;
    }
	
	protected function _prepareMassaction(){
		$this->setMassactionIdField('code_id');
		$this->getMassactionBlock()->setFormFieldName('luckydrawcode');
		
		$statuses = Mage::getSingleton('luckydraw/code')->getStatusArray();
		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('luckydraw')->__('Change status'),
			'url'	=> $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'visibility' => array(
					'name'	=> 'status',
					'type'	=> 'select',
					'class'	=> 'required-entry',
					'label'	=> Mage::helper('luckydraw')->__('Status'),
					'values'=> $statuses
				))
		));
		return $this;
	}
}