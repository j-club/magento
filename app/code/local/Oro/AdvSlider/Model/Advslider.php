<?php
/**
 * AdvSlider Model
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */


class Oro_AdvSlider_Model_AdvSlider extends Mage_Core_Model_Abstract
{

    protected $_resourceName = 'oro_advslider/advslider';
    public function getRelatedBannersByCategoryId($categoryId)
    {
        if (!$this->hasRelatedCategoryBanners()) {
            $banners = $this->_getResource()->getRelatedBannersByCategoryId($categoryId);
            $this->setRelatedCategoryBanners($banners);
        }
        return $this->_getData('related_category_banners');
    }
}

