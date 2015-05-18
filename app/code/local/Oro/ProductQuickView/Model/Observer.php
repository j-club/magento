<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_ProductQuickView
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_ProductQuickView_Model_Observer
{
    const MEDIA_FLAG = 'oro_productquickview_media';
    protected $_mediaAttributes = false;
    /**
     * Add media gallery data to product collection
     *
     * @return Oro_ProductQuickView_Model_Observer
     */
    public function addMediaGalleryToCollection(Varien_Event_Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $controllerAction = $this->_getControllerAction();
        if (!$controllerAction->getFlag('',self::MEDIA_FLAG)) {
            return $this;
        }
        Mage::getModel('oro_productquickview/media')->addMediaGalleryDataToProducts($productCollection);
        return $this;
    }
    /**
     * Add media attributes to product collection
     *
     * @return Oro_ProductQuickView_Model_Observer
     */
    public function addMediaAttributesToCollection(Varien_Event_Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $controllerAction = $this->_getControllerAction();
        if (!$controllerAction->getFlag('',self::MEDIA_FLAG)) {
            return $this;
        }
        $collection = $this->_getMediaAttributes();
        foreach($collection as $mediaAttribute) {
            $productCollection->addAttributeToSelect($mediaAttribute->getAttributeCode());
        }
        return $this;
    }
    /**
     * Return collection of existing media attributes
     *
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    protected function _getMediaAttributes()
    {
        if (!$this->_mediaAttributes) {
            $this->_mediaAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addVisibleFilter()
                ->addFieldToFilter('frontend_input', array('eq' => 'media_image'))
                ->load();
        }
        return $this->_mediaAttributes;
    }
    /**
     * Set flag to controller to init gallery data
     *
     * @return Oro_ProductQuickView_Model_Observer
     */
    public function initGalleryFlag(Varien_Event_Observer $observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $controller->setFlag('',self::MEDIA_FLAG, true);
    }

    protected function _getControllerAction()
    {
        return Mage::app()->getFrontController()->getAction();
    }
}
