<?php
/**
 * Catalog Block
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_AdvSlider_Block_Catalog extends Mage_Core_Block_Template
{

    /**
     * Get items collection
     *
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    public function getItemsCollection()
    {
        $currentCategoryId = Mage::registry('current_category')->getId();
        $banners = Mage::getModel('oro_advslider/advslider')->getRelatedBannersByCategoryId($currentCategoryId);
        $bannerIds = array();
        foreach ($banners as $banner) {
            $bannerIds[] = $banner['banner_id'];
        }
        $collection = Mage::getResourceModel('enterprise_banner/banner')->getBannersContent($bannerIds, Mage::app()->getStore()->getId());

        return $collection;
    }
}
