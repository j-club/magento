<?php

/**
 * Module's helper
 *
 * @category   MageCore
 * @package    Oro_LayeredNavigation
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_LayeredNavigation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NAVIGATION_FILTER_ITEMS_COUNT_DISPLAY = 5;

    /**
     * Get count of Navigation Filter Items Display
     *
     * @return int
     */
    public function getFilterNavigationItemsCount()
    {
        return self::NAVIGATION_FILTER_ITEMS_COUNT_DISPLAY;
    }
}
