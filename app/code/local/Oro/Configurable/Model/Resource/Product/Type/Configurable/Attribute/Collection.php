<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Configurable
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/**
 * Optimized Catalog Configurable Product Attribute Collection
 */
class Oro_Configurable_Model_Resource_Product_Type_Configurable_Attribute_Collection
    extends Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
{
    /**
     * Add product attributes to collection items
     *
     * @return Oro_Configurable_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _addProductAttributes()
    {
        foreach ($this->_items as $item) {
            $productAttribute = $this->getProduct()->getResource()->getAttribute($item->getAttributeId());
            $item->setProductAttribute($productAttribute);
        }
        return $this;
    }

    /**
     * Get configurable attribute options
     *
     * @param Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection $products
     * @param string $attributeCode
     * @return array
     */
    protected function _getUsedOptions($products, $attributeCode)
    {
        $options = array();
        foreach ($products as $product) {
            $options[] = $product->getData($attributeCode);
        }
        return $options;
    }

    public function getProduct()
    {
        return $this->_product;
    }
}

