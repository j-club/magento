<?php
/**
 * @category   Oro
 * @package    Oro_Configurable
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Configurable product type resource model
 */
class Oro_Configurable_Model_Resource_Product_Type_Configurable extends Mage_Catalog_Model_Resource_Product_Type_Configurable
{
    /**
     * Retrieve min/max prices
     *
     * @param int $productId
     * @return array
     */
    public function getMinMaxPrices($productId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('catalog/product_index_price'), array('min_price', 'max_price'))
            ->where('entity_id = ?', $productId);

        return $adapter->fetchRow($select);
    }
}
