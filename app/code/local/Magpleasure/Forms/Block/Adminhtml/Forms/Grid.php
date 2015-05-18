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

class Magpleasure_Forms_Block_Adminhtml_Forms_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Form Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    protected function _prepareCollection()
    {
        /**
         * Collection
         * @var Magpleasure_Forms_Model_Mysql4_Form_Collection $collection
         */
        $collection = Mage::getModel('forms/form')->getCollection();
        $collection->addFieldToFilter('status', array('neq' => Magpleasure_Forms_Model_Form::STATUS_DELETED));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _parentPrepareCollection()
    {
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('form_id', array(
            'header' => $this->_helper()->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'form_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->_helper()->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('id', array(
            'header' => $this->_helper()->__('CMS ID'),
            'align' => 'left',
            'index' => 'id',
        ));


        $this->addColumn('status', array(
            'header' => $this->_helper()->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                Magpleasure_Forms_Model_Form::STATUS_ENABLED => $this->_helper()->__('Enabled'),
                Magpleasure_Forms_Model_Form::STATUS_DISABLED => $this->_helper()->__('Disabled'),
            ),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('stores', array(
                'header' => $this->_helper()->__('Store View'),
                'index' => 'stores',
                'type' => 'store',
                'store_all' => false,
                'store_view' => false,
                'width' => 210,
                'sortable' => false,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => $this->_helper()->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '140px',
        ));

        $this->addColumn('updated_at', array(
            'header' => $this->_helper()->__('Updated At'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '140px',
        ));

        $this->addColumn('action',
            array(
                'header' => $this->_helper()->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getFormId',
                'actions' => array(
                    array(
                        'caption' => $this->_helper()->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->_helper()->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                        'confirm' => $this->_helper()->__('Are you sure?')
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getFormId()));
    }

    protected function _filterStoresCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

}
