<?php
/**
 * Top Sellers block
 *
 * @category   Oro
 * @package    Oro_TopSellers
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_TopSellers_Block_Topsellers extends Mage_Catalog_Block_Product_Abstract
{
    const AGGREGATE_MONTH_TABLE = 'sales_bestsellers_aggregated_monthly';

    /**
     * Get top sellers products collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection
            ->getSelect()
            ->joinInner(array('top'=>self::AGGREGATE_MONTH_TABLE), 'top.product_id = e.entity_id')
            ->group(array('e.entity_id'))
            ->order(array('period ASC', 'qty_ordered DESC'))
            ->limit(10);
        $collection->addStoreFilter();

        Mage::dispatchEvent(
            'catalog_block_product_list_collection', array('collection' => $collection)
        );

        if ($category = Mage::registry('current_category')) {
            $collection->addCategoryFilter($category);
        }

        return $collection;
    }
}