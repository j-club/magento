<?php
/**
 * @category   Oro
 * @package    Oro_Configurable
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Configurable Observer
 */
class Oro_Configurable_Model_Observer
{
    /**
     * Add min/max prices to product
     *
     * @param Varien_Event_Observer $observer
     * @return Oro_Configurable_Model_Observer
     */
    public function addMinMaxPrices(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->isConfigurable()) {
            $minMaxPrices = $product->getTypeInstance(true)->getMinMaxPrices($product->getId());
            if (!empty($minMaxPrices)) {
                $product->setMinPrice($minMaxPrices['min_price'])
                    ->setMaxPrice($minMaxPrices['max_price']);
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadAllCofigurableUsedProducts(Varien_Event_Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();

        $productIds = array();
        foreach ($productCollection as $product) {
            if ($product->isConfigurable()) {
                $productIds[] = $product->getId();
            }
        }

        if (!empty($productIds)) {
            $collection = Mage::getResourceModel('catalog/product_type_configurable_product_collection')
                ->setFlag('require_stock_items', true)
                ->setFlag('product_children', true)
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addFilterByRequiredOptions();
            $collection->getSelect()->where('link_table.parent_id IN(?)', $productIds);
            $collection->setFlag('skip_udmulti_load', true);

            foreach ($productCollection as $product) {
                if ($product->isConfigurable()) {
                    $usedProducts = array();
                    $productId = $product->getId();
                    foreach ($collection as $item) {
                        if ($item->getData('parent_id') == $productId) {
                            $usedProducts[$productId][] = $item;
                        }
                    }
                    $product->getTypeInstance(true)->setCachedUsedProducts($usedProducts);
                }
            }
        }

        return $this;
    }
}
