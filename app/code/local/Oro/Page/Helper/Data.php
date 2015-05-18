<?php
/**
 * Custom top menu
 *
 * @category   MageCore
 * @package    Oro_Page
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Page_Helper_Data extends Mage_Core_Helper_Abstract
{
    const POPUP_SUBSCRIPTION_COOKIE = '_isNewUser_';

    /**
     * @return bool
     */
    public function isNewUser()
    {
        $cookieModel = Mage::getSingleton('core/cookie');
        return !$cookieModel->get(self::POPUP_SUBSCRIPTION_COOKIE) && !Mage::helper('customer')->isLoggedIn();
    }
}
