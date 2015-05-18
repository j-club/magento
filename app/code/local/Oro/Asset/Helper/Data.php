<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset Data Helper
 */
class Oro_Asset_Helper_Data extends Mage_Core_Helper_Abstract
{
    const STYLE_DEFAULT             = 1;
    const STYLE_MERGE_MIN           = 2;
    const STYLE_MERGE               = 3;

    const XML_PATH_ENABLED          = 'dev/oro_asset/enabled';
    const XML_PATH_STYLE            = 'dev/oro_asset/style';
    const XML_PATH_AUTO_INCREMENT   = 'dev/oro_asset/auto_increment';
    const XML_PATH_CSS_VERSION      = 'dev/oro_asset/css_version';
    const XML_PATH_JS_VERSION       = 'dev/oro_asset/js_version';
    const XML_PATH_UPDATED_AT       = 'dev/oro_asset/updated_at';

    /**
     * Checks is module enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Returns assets style
     *
     * @return string
     */
    public function getStyle()
    {
        return Mage::getStoreConfig(self::XML_PATH_STYLE);
    }

    /**
     * Check is increase assets version after flush cache automatically
     *
     * @return bool
     */
    public function isAutoIncrement()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_INCREMENT);
    }

    /**
     * Returns JS version
     *
     * @return int
     */
    public function getJsVersion()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_JS_VERSION);
    }

    /**
     * Returns CSS version
     *
     * @return int
     */
    public function getCssVersion()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_CSS_VERSION);
    }

    /**
     * Returns last version update date
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return Mage::getStoreConfig(self::XML_PATH_UPDATED_AT);
    }

    /**
     * Increases assets version and clean config cache
     *
     * @param bool $cleanCache
     * @return $this
     */
    public function increaseVersion($cleanCache = false)
    {
        $config = Mage::getConfig();

        $jsVersion  = $this->getJsVersion() + 1;
        $config->saveConfig(self::XML_PATH_JS_VERSION, $jsVersion);
        $cssVersion = $this->getCssVersion() + 1;
        $config->saveConfig(self::XML_PATH_CSS_VERSION, $cssVersion);
        $updatedAt  = time();
        $config->saveConfig(self::XML_PATH_UPDATED_AT, $updatedAt);

        if ($cleanCache) {
            Mage::app()->cleanCache(array(Mage_Core_Model_Config::CACHE_TAG));
        }

        return $this;
    }
}
