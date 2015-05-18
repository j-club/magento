<?php

class Oro_ProductQuickView_Model_Resource_Catalog_Product_Media_Item_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_eventPrefix              = 'oro_productquickview_catalog_product_media_collection';
    protected $_eventObject              = 'product_media_collection';
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Varien_Object', 'oro_productquickview/catalog_product_media_item');
    }
    /**
     * Initialize select
     *
     * @return Oro_ProductQuickView_Model_Resource_Catalog_Product_Media_Item_Collection
     */
    protected function _initSelect()
    {
        $currentStore = Mage::app()->getStore();
        $adapter = $this->getConnection();
        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');
        $this->getSelect()->from(
            array('main' => $this->getMainTable()),
            array('value_id', 'entity_id', 'value AS file')
        )->joinLeft(
            array('value' => $this->getTable(Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media::GALLERY_VALUE_TABLE)),
            $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', $currentStore->getId()),
            array('label','position','disabled')
        )
        ->joinLeft( // Joining default values
            array('default_value' => $this->getTable(Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media::GALLERY_VALUE_TABLE)),
            'main.value_id = default_value.value_id AND default_value.store_id = 0',
            array(
                'label_default' => 'label',
                'position_default' => 'position',
                'disabled_default' => 'disabled'
            )
        )->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);
        return $this;
    }
    /**
     * Add product filter to collection
     *
     * @param array $products
     * @return Oro_ProductQuickView_Model_Resource_Catalog_Product_Media_Item_Collection
     */
    public function addProductsFilter($products)
    {
        $productIds = array();
        foreach ($products as $product) {
            if ($product instanceof Mage_Catalog_Model_Product) {
                $productIds[] = $product->getId();
            } else {
                $productIds[] = $product;
            }
        }
        if (empty($productIds)) {
            $productIds[] = false;
            $this->_setIsLoaded(true);
        }
        $this->addFieldToFilter('main.entity_id', array('in' => $productIds));
        return $this;
    }

    protected function _afterLoad()
    {
        $localAttributes = array('label', 'position', 'disabled');
        foreach ($this->getItems() as $image) {
            foreach ($localAttributes as $localAttribute) {
                if (is_null($image[$localAttribute])) {
                    $image[$localAttribute] = $this->_getDefaultValue($localAttribute, $image);
                }
            }
        }
        return parent::_afterLoad();
    }

    protected function _getDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }
        return '';
    }
}
