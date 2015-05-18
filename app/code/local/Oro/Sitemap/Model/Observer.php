<?php
/**
 * Sitemap Model Observer - to backport a fix from Magento EE 1.14
 *
 * @category   Oro
 * @package    Oro_Sitemap
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_Sitemap_Model_Observer
{
    /**
     * Add Seo suffix to category's URL if doesn't exists.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSeoSuffixToCategoryUrl(Varien_Event_Observer $observer)
    {
        $seoSuffix = (string) Mage::app()->getStore()->getConfig(
            Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX
        );
        $this->_addSuffixToUrl($observer->getCollection()->getItems(), $seoSuffix);

        return $this;
    }

    /**
     * Add Seo suffix to product's URL if doesn't exists.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addSeoSuffixToProductUrl(Varien_Event_Observer $observer)
    {
        $seoSuffix = (string) Mage::app()->getStore()->getConfig(
            Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX
        );
        $this->_addSuffixToUrl($observer->getCollection()->getItems(), $seoSuffix);

        return $this;
    }

    /**
     * Iterate via items and add suffix to item's URL.
     *
     * @param $items
     * @param $seoSuffix
     */
    protected function _addSuffixToUrl($items, $seoSuffix)
    {
        foreach ($items as $item) {
            if ($item->getUrl() && strpos($item->getUrl(), $seoSuffix) === false) {
                $item->setUrl($item->getUrl() . '.' . $seoSuffix);
            }
        }
    }
}
