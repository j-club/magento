<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_ProductQuickView
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_ProductQuickView_Model_Media
{
    /**
     * Add media gallery data to product collection
     *
     * @return Oro_ProductQuickView_Model_Media
     */
    public function addMediaGalleryDataToProducts($productCollection)
    {
        $items = $this->getItemCollection()
            ->addProductsFilter($productCollection)
            ->load();
        $mediaData = array();
        foreach ($items as $item) {
            $mediaData[$item->getEntityId()][] = $item->getData();
        }
        foreach ($productCollection as $product) {
            if (isset($mediaData[$product->getId()])) {
                $product->setMediaGallery(
                    array('images' => $mediaData[$product->getId()])
                );
            }
        }
        return $this;
    }
    /**
     * Return media data collection
     *
     * @return Oro_ProductQuickView_Model_Resource_Catalog_Product_Media_Item_Collection
     */
    public function getItemCollection()
    {
        return Mage::getResourceModel('oro_productquickview/catalog_product_media_item_collection');
    }
}