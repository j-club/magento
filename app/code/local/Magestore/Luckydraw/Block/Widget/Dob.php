<?php

class Magestore_Luckydraw_Block_Widget_Dob extends Mage_Customer_Block_Widget_Dob
{
    public function isEnabled(){
        return (bool)Mage::helper('luckydraw')->getRegisterConfig('dob_show');
    }
    
    public function isRequired(){
        return (bool)(Mage::helper('luckydraw')->getRegisterConfig('dob_show') == 'req');
    }
}