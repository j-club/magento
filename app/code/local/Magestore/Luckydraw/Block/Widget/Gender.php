<?php

class Magestore_Luckydraw_Block_Widget_Gender extends Mage_Customer_Block_Widget_Gender
{
    public function isEnabled(){
        return (bool)Mage::helper('luckydraw')->getRegisterConfig('gender_show');
    }
    
    public function isRequired(){
        return (bool)(Mage::helper('luckydraw')->getRegisterConfig('gender_show') == 'req');
    }
}