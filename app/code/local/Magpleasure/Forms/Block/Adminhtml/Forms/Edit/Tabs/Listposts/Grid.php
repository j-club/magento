<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE-CE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE-CE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Magpleasure does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Magpleasure does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Magpleasure
 * @package    Magpleasure_Forms
 * @version    1.0.5
 * @copyright  Copyright (c) 2011-2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE-CE.txt
 */

/**
 * List of Posts
 */
class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Listposts_Grid
                extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_form;

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('listpostsGrid');
        $this->setDefaultSort('list_id');
        $this->setDefaultDir('desc');
    }

    /**
     * Form Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }


    public function getFormId()
    {
        if (Mage::registry('forms_data') && Mage::registry('forms_data')->getFormId() ) {
            return Mage::registry('forms_data')->getFormId();
        }
        return false;
    }

    /**
     * Set form
     *
     * @param Magpleasure_Forms_Model_Form $form
     * @return Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Listposts_Grid
     */
    public function setForm(Magpleasure_Forms_Model_Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Form
     *
     * @return Magpleasure_Forms_Model_Form
     */
    public function getForm()
    {
        if (!$this->_form){
            $this->_form = Mage::getModel('forms/form')->load($this->getFormId());
        }
        return $this->_form;
    }

    protected function _prepareCollection()
    {
        /** @var Magpleasure_Forms_Model_Mysql4_List_Collection $collection  */
        $collection = Mage::getModel('forms/list')->getCollection();
        $collection->addFieldToFilter('form_id', $this->getForm()->getId());
        foreach ($this->getForm()->getFields() as $field){
            $collection->addValueToCollection($field->getId());
        }
        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		$this->addColumn('list_id', array(
		  'header'    => $this->_helper()->__('ID'),
		  'align'     =>'right',
		  'width'     => '50px',
		  'index'     => 'list_id',
		));

        $this->addColumn('list_created_at', array(
            'header' => $this->_helper()->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));



        /** @var Magpleasure_Forms_Model_System_Config_Source_List_Status $statuses  */
        $statuses = Mage::getSingleton('forms/system_config_source_list_status');

        $this->addColumn('list_status', array(
            'header'    => $this->_helper()->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => $statuses->toOptionArray(),
            'is_system' => true,
        ));



        foreach ($this->getForm()->getFields() as $field){
            $this->addColumn('field'.$field->getId(), array(
              'header'    => $field->getQuestion(),
              'align'     =>'left',
              'index'     => 'field'.$field->getId(),
            ));
        }


        if (!Mage::app()->isSingleStoreMode()){
            $this->addColumn('store_id', array(
                'header'     => $this->_helper()->__('Store View'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_all'  => false,
                'store_view' => false,
                'width'      => '150px',
                'sortable'   => false,
                'is_system'  => true,
            ));
        }

        $this->addColumn('action',
            array(
                'header'    =>  $this->_helper()->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getListId',
                'actions'   => array(
                    array(
                        'caption'   => $this->_helper()->__('Approve'),
                        'url'       => array('base'=> '*/*/approvepost'),
                        'field'     => 'pid'
                    ),
                    array(
                        'caption'   => $this->_helper()->__('Reject'),
                        'url'       => array('base'=> '*/*/rejectpost'),
                        'field'     => 'pid'
                    ),
                    array(
                        'caption'   => $this->_helper()->__('Delete'),
                        'url'       => array('base'=> '*/*/deletepost'),
                        'field'     => 'pid',
                        'confirm' => $this->_helper()->__('Are you sure?')

                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', $this->_helper()->__('CSV'));
        $this->addExportType('*/*/exportExcel', $this->_helper()->__('Excel'));

    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('list_id');
        $this->getMassactionBlock()->setFormFieldName('listIds');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => $this->_helper()->__('Delete'),
             'url'      => $this->getUrl('*/*/massPostDelete', array('id' => $this->getRequest()->getParam('id'))),
             'confirm'  => $this->_helper()->__('Are you sure?')
        ));

        /** @var Magpleasure_Forms_Model_System_Config_Source_List_Status $statuses  */
        $statuses = Mage::getSingleton('forms/system_config_source_list_status');


        $this->getMassactionBlock()->addItem('list_status', array(
             'label'=> $this->_helper()->__('Change status'),
             'url'  => $this->getUrl('*/*/massPostStatus', array('_current'=>true, 'id' => $this->getRequest()->getParam('id'))),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'list_status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => $this->_helper()->__('Status'),
                         'values' => $statuses->getOptionsArray()
                     )
             )
        ));
        return $this;
    }

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();

        $this->_afterLoadCollection();

        $data = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getExportHeader().'"';
            }
        }
        $csv.= implode(',', $data)."\n";

        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"'.str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($item)).'"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        if ($this->getCountTotals())
        {
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



    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @param string $filename the Workbook sheet name
     * @return string
     */
    public function getExcel($filename = '')
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $headers = array();
        $data = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $headers[] = $column->getHeader();
            }
        }
        $data[] = $headers;

        foreach ($this->getCollection() as $item) {
            $row = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($item);
                }
            }
            $data[] = $row;
        }

        if ($this->getCountTotals())
        {
            $row = array();
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->getTotals());
                }
            }
            $data[] = $row;
        }



        $xmlObj = new Varien_Convert_Parser_Xml_Excel();
        $xmlObj->setVar('single_sheet', $filename);
        $xmlObj->setData($data);
        $xmlObj->unparse();

        return $xmlObj->getData();
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/listgrid', array('form_id'=> $this->getFormId()));
    }



}