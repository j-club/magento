<?php

class Unirgy_DropshipSellYours_Block_Adminhtml_SystemConfigField_SyFieldsetsColumnConfig extends Unirgy_Dropship_Block_Adminhtml_SystemConfigFormField_FieldContainer
{
    public function getEditFieldsConfig()
    {
        return Mage::helper('udsell')->getSellYoursFieldsConfig();
    }
}