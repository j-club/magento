<?php

class Magestore_Luckydraw_Block_Widget_Taxvat extends Mage_Customer_Block_Widget_Taxvat
{
    public function isEnabled(){
        return (bool)Mage::helper('luckydraw')->getRegisterConfig('taxvat_show');
    }
    
    public function isRequired(){
        return (bool)(Mage::helper('luckydraw')->getRegisterConfig('taxvat_show') == 'req');
    }
}