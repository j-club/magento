<?php
/**
 * Featured product list
 *
 * @category   Oro
 * @package    Oro_FeaturedProducts
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_FeaturedProducts_Block_List extends Mage_Catalog_Block_Product_Abstract
{
    const FEATURED_LIMIT = 25;

    var $_featuredProductsIds;

    /**
     * Get All Featured Products Ids
     *
     * @return array
     */
    protected function _getAllFeaturedProductsIds()
    {
        if (!$this->_featuredProductsIds) {
            /** @var  Mage_Catalog_Model_Resource_Product_Collection $collection */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addFieldToFilter('is_featured', 1);
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($collection);
            $ids = $collection->getAllIds();
            $this->_featuredProductsIds = $ids;
        }
        return $this->_featuredProductsIds;
    }

    /**
     * Return Featured Products Collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        $ids = $this->_getAllFeaturedProductsIds();
        shuffle($ids);
        $ids = array_slice($ids, 0, self::FEATURED_LIMIT);
        /** @var  Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        $collection->addAttributeToSelect('*');
        Mage::dispatchEvent(
            'catalog_block_product_list_collection', array('collection' => $collection)
        );

        return $collection;
    }
}
