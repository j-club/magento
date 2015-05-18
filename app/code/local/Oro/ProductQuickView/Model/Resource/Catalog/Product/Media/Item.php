<?php
class Oro_ProductQuickView_Model_Resource_Catalog_Product_Media_Item extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init(Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media::GALLERY_TABLE, 'value_id');
    }

}
