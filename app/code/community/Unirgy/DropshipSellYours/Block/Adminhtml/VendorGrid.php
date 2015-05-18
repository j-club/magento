<?php

class Unirgy_DropshipSellYours_Block_Adminhtml_VendorGrid extends Unirgy_Dropship_Block_Adminhtml_Vendor_Grid
{
    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udsell');
        $this->addColumnAfter('is_featured', array(
            'header'    => $hlp->__('Is Featured'),
            'index'     => 'is_featured',
            'type'      => 'options',
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ), 'status');
        return parent::_prepareColumns();
    }
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->addItem('is_featured', array(
             'label'=> Mage::helper('udropship')->__('Change Is Featured'),
             'url'  => $this->getUrl('udselladmin/vendor/massIsFeatured', array('_current'=>true)),
             'additional' => array(
                    'is_featured' => array(
                         'name' => 'is_featured',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('udsell')->__('Is Featured'),
                         'values' => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionArray(true),
                     )
             )
        ));

        return $this;
    }
}