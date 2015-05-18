<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset Observer
 */
class Oro_Asset_Model_Observer
{
    /**
     * Increase assets version and add message to session
     *
     * @return void
     */
    public function cleanMediaCacheAfter()
    {
        /** @var $helper Oro_Asset_Helper_Data */
        $helper     = Mage::helper('oro_asset');
        /** @var $session Mage_Admin_Model_Session */
        $session    = Mage::getSingleton('adminhtml/session');
        if ($helper->isEnabled()) {
            $helper->increaseVersion(true);

            $session->addSuccess($helper->__('The JavaScript/CSS version has been updated.'));
        }
    }
}
