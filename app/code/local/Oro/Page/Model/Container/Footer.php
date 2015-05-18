<?php
/**
 *
 * @category   MageCore
 * @package    Oro_Page
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Page_Model_Container_Footer extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get container individual cache id
     *
     * @return string|false
     */
    protected function _getCacheId()
    {
        $cacheId = $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN, 0)
            . '_' . $this->_getCookieValue(Oro_Page_Helper_Data::POPUP_SUBSCRIPTION_COOKIE, 0);
        return 'SUBSCRIBER_POPUP_' . $cacheId;
    }
}
