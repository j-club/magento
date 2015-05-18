<?php
/**
 * AdvSlider Observer
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_AdvSlider_Model_Adminhtml_Observer
{

    /**
     * Bind specified banners to category
     *
     * @param   Varien_Event_Observer $observer
     * @return  Oro_AdvSlider_Model_Adminhtml_Observer
     */
    public function bindRelatedBannersToCategory(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $resource = Mage::getResourceModel('oro_advslider/advslider');
        $banners = Mage::helper('adminhtml/js')->decodeGridSerializedInput(
            Mage::app()->getRequest()->getPost('related_banners')
        );
        if (empty($banners)) {
            $banners = array();
        }
        $resource->bindBannersToCategory($category->getId(), $banners);
        return $this;
    }
}
