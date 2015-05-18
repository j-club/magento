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

class Magpleasure_Forms_Block_Adminhtml_Rubbish_Grid extends Magpleasure_Forms_Block_Adminhtml_Forms_Grid
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
        $collection->addFieldToFilter('status', Magpleasure_Forms_Model_Form::STATUS_DELETED);
        $this->setCollection($collection);
        return parent::_parentPrepareCollection();
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this
            ->removeColumn('status')
            ->removeColumn('action')
            ;

        $this->addColumn('action',
            array(
                'header'    =>  $this->_helper()->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getFormId',
                'actions'   => array(
                    array(
                        'caption'   => $this->_helper()->__('Restore'),
                        'url'       => array('base'=> '*/*/restore'),
                        'field'     => 'id'
                    ),
                    array(
                        'caption'   => $this->_helper()->__('Delete Forever'),
                        'url'       => array('base'=> '*/*/purge'),
                        'field'     => 'id',
                        'confirm'  => $this->_helper()->__('Are you sure?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    protected function _filterStoresCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
    
}
