<?php

class Magestore_Membership_Model_Mysql4_Groupproduct extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the membership_id refers to the key field in your database table.
        $this->_init('membership/groupproduct', 'group_product_id');
    }
}