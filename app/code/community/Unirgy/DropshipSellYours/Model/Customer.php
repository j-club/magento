<?php

class Unirgy_DropshipSellYours_Model_Customer extends Mage_Customer_Model_Customer
{
    public function validate()
    {
        $errors  = parent::validate();
        
    }
}