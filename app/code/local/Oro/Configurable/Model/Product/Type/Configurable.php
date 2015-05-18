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
 * Optimization of configurable product type implementation
 */
class Oro_Configurable_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    protected $_cachedUsedProducts = array();

    public function isSalable($product = null)
    {
        if ($this->getProduct($product)->hasData('is_salable')) {
            return $this->getProduct($product)->getData('is_salable');
        }
        
        return parent::isSalable($product);
    }

    /**
     * Retrieve array of "subproducts"
     *
     * @param  array
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProducts($requiredAttributeIds = null, $product = null)
    {
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);

        if (!$this->getProduct($product)->hasData($this->_usedProducts)) {
            if (is_null($requiredAttributeIds)
                and is_null($this->getProduct($product)->getData($this->_configurableAttributes))) {
                // If used products load before attributes, we will load attributes.
                $this->getConfigurableAttributes($product);
                // After attributes loading products loaded too.
                Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
                return $this->getProduct($product)->getData($this->_usedProducts);
            }

            $usedProducts = array();

            if (isset($this->_cachedUsedProducts[$product->getId()])) {
                $store = $this->getStoreFilter($product);
                if ($store === null) {
                    $store = Mage::app()->getStore()->getId();
                }
                $storeId = Mage::app()->getStore($store)->getId();

                $items = $this->_cachedUsedProducts[$product->getId()];
                foreach ($items as $item) {
                    if (is_array($requiredAttributeIds)) {
                        foreach ($requiredAttributeIds as $attributeId) {
                            $attribute = $product->getResource()->getAttribute($attributeId);
                            if (!is_null($attribute)) {
                                $attributeValue = $item->getData($attribute->getAttributeCode());
                                if (empty($attributeValue)) {
                                    continue;
                                }
                            }
                        }
                    }

                    if ($item->getStoreId() == $storeId) {
                        $usedProducts[] = $item;
                    }
                }
            } else {
                $collection = $this->getUsedProductCollection($product)
                    ->addAttributeToSelect('*')
                    ->addFilterByRequiredOptions();

                if (is_array($requiredAttributeIds)) {
                    foreach ($requiredAttributeIds as $attributeId) {
                        $attribute = $product->getResource()->getAttribute($attributeId);
                        if (!is_null($attribute))
                            $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
                    }
                }

                foreach ($collection as $item) {
                    $usedProducts[] = $item;
                }
            }

            $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);
            

        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $this->getProduct($product)->getData($this->_usedProducts);
    }

    /**
     * Retrieve Selected Attributes info
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getSelectedAttributesInfo($product = null)
    {
        $attributes = array();
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        if ($attributesOption = $this->getProduct($product)->getCustomOption('attributes')) {
            $data = unserialize($attributesOption->getValue());
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $this->getProduct($product)->getData($this->_usedAttributes);

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    $attribute = $usedAttributes[$attributeId];
                    $label = $attribute->getLabel();
                    $value = $attribute->getProductAttribute();
                    if ($value->getSourceModel()) {
                        if (!Mage::app()->getStore()->isAdmin()) {
                            $value = $this->_getOptionsValue($value, $attributeValue);
                        } else {
                            $value = $value->getSource()->getOptionText($attributeValue);
                        }
                    }
                    else {
                        $value = '';
                    }

                    $attributes[] = array('label'=>$label, 'value'=>$value);
                }
            }
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $attributes;
    }

    /**
     * Get options value
     *
     * @param type $attribute
     * @param type $attributeValue
     */
    protected function _getOptionsValue($attribute, $value)
    {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $options = Mage::helper('oro_configurable')->getAttributeOptions($attribute, $value);

        if ($isMultiple) {
            $values = array();
            foreach ($options as $item) {
                if (in_array($item['value'], $value)) {
                    $values[] = $item['label'];
                }
            }
            return $values;
        }

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;

    }

    /**
     * Copied from Peets_Catalog_Model_Product_Type_Configurable
     * 
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $attributes = $buyRequest->getSuperAttribute();
        $isConfigurable = ($product->getTypeId() == parent::TYPE_CODE);

        if ($attributes || !$this->_isStrictProcessMode($processMode) || $isConfigurable) {
            if (!$this->_isStrictProcessMode($processMode)) {
                if (is_array($attributes)) {
                    foreach ($attributes as $key => $val) {
                        if (empty($val)) {
                            unset($attributes[$key]);
                        }
                    }
                } else {
                    $attributes = array();
                }
            }

            $result = Mage_Catalog_Model_Product_Type_Abstract::_prepareProduct($buyRequest, $product, $processMode);
            
            if (is_array($result)) {
                $product = $this->getProduct($product);
                /**
                 * $attributes = array($attributeId=>$attributeValue)
                 */
                $subProduct = true;
                if ($this->_isStrictProcessMode($processMode)) {
                    foreach ($this->getConfigurableAttributes($product) as $attributeItem) {
                        /* @var $attributeItem Varien_Object */
                        $attrId = $attributeItem->getData('attribute_id');
                        if (!isset($attributes[$attrId]) || empty($attributes[$attrId])) {
                            $subProduct = null;
                            break;
                        }
                    }
                }
                if ($subProduct) {
                    $subProduct = $this->getProductByAttributes($attributes, $product);
                }

                if ($subProduct) {
                    $product->addCustomOption('attributes', serialize($attributes));
                    $product->addCustomOption('product_qty_' . $subProduct->getId(), 1, $subProduct);
                    $product->addCustomOption('simple_product', $subProduct->getId(), $subProduct);

                    $_result = $subProduct->getTypeInstance(true)->_prepareProduct(
                        $buyRequest, $subProduct, $processMode
                    );
                    if (is_string($_result) && !is_array($_result)) {
                        return $_result;
                    }

                    if (!isset($_result[0])) {
                        return Mage::helper('checkout')->__('Cannot add the item to shopping cart');
                    }

                    /**
                     * Adding parent product custom options to child product
                     * to be sure that it will be unique as its parent
                     */
                    if ($optionIds = $product->getCustomOption('option_ids')) {
                        $optionIds = explode(',', $optionIds->getValue());
                        foreach ($optionIds as $optionId) {
                            if ($option = $product->getCustomOption('option_' . $optionId)) {
                                $_result[0]->addCustomOption('option_' . $optionId, $option->getValue());
                            }
                        }
                    }

                    $_result[0]->setParentProductId($product->getId())
                        // add custom option to simple product for protection of process
                        //when we add simple product separately
                        ->addCustomOption('parent_product_id', $product->getId());
                    if ($this->_isStrictProcessMode($processMode)) {
                        $_result[0]->setCartQty(1);
                    }
                    $result[] = $_result[0];
                    return $result;
                } else if (!$this->_isStrictProcessMode($processMode) || $isConfigurable) {
                    return $result;
                }
            }
        }

        return $this->getSpecifyOptionMessage();
    }

    /**
     * Retrieve min/max prices
     *
     * @param int $productId
     * @return array
     */
    public function getMinMaxPrices($productId)
    {
        return Mage::getResourceSingleton('catalog/product_type_configurable')->getMinMaxPrices($productId);
    }

    public function setCachedUsedProducts($products)
    {
        //avoid array merge for rewriting array key(keys are product IDS)
        $this->_cachedUsedProducts = $this->_cachedUsedProducts + $products;

        return $this;
    }
}
