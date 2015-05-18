<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset Observer
 */
class Oro_Asset_Model_Merge
{
    const CACHE_TAG = 'ORO_ASSET_MERGE';
    const CACHE_TYPE = 'oro_asset';

    const TYPE_CSS = 'css';
    const TYPE_JS = 'js';

    /**
     * List of file packages per handle
     *
     * @var array
     */
    protected $_handles;

    /**
     * List of assets data
     *
     * @var array
     */
    protected $_assets;

    /**
     * Current area name
     *
     * @var string
     */
    protected $_area;

    /**
     * Current design package name
     *
     * @var string
     */
    protected $_package;

    /**
     * Current design theme name (skin)
     *
     * @var string
     */
    protected $_theme;

    /**
     * Defines scope and initializes assets data
     *
     * @param null|string $area
     * @param null|string $package
     * @param null|string $theme
     * @return $this
     */
    protected function _init($area = null, $package = null, $theme = null)
    {
        $area = $this->_getArea($area);
        $package = $this->_getPackage($package);
        $theme = $this->_getTheme($theme);

        if ($area == $this->_area && $package == $this->_package && $theme == $this->_theme) {
            return $this;
        }

        $this->_area = $area;
        $this->_package = $package;
        $this->_theme = $theme;


        if (!$this->_loadCache($area, $package, $theme)) {
            $this->_loadData($area, $package, $theme);
        }

        return $this;
    }

    /**
     * Returns info about merged JS files
     *
     * @param null|string $area
     * @param null|string $package
     * @param null|string $theme
     * @param null|string $handles
     * @return array
     */
    public function getJsFiles($area = null, $package = null, $theme = null, $handles = null)
    {
        return $this->getAssetFiles(self::TYPE_JS, $area, $package, $theme, $handles);
    }

    /**
     * Returns info about merged CSS files
     *
     * @param null|string $area
     * @param null|string $package
     * @param null|string $theme
     * @param null|string $handles
     * @return array
     */
    public function getCssFiles($area = null, $package = null, $theme = null, $handles = null)
    {
        return $this->getAssetFiles(self::TYPE_CSS, $area, $package, $theme, $handles);
    }

    /**
     * Returns assets data
     *
     * Each item has following keys:
     * file;        string; The merged file name with relative skin path
     * file_min;    string; The merged and compressed file with relative skin path
     * if;          string; The IF condition
     * params;      string; The additional tag arguments
     * remove;      array;  The list of merged files
     * behavior;    string; The behavior
     *
     * @param string $type
     * @param null|string $area
     * @param null|string $package
     * @param null|string $theme
     * @param null|array $handles
     * @return array
     */
    public function getAssetFiles($type, $area = null, $package = null, $theme = null, $handles = null)
    {
        $this->_init($area, $package, $theme);
        $handles = $this->_getHandles($handles);
        $assets = array();
        foreach ($handles as $handle) {
            if (!isset($this->_handles[$type][$handle])) {
                continue;
            }

            foreach ($this->_handles[$type][$handle] as $assetName) {
                $assets[$assetName] = $this->_assets[$type][$assetName];
            }
        }

        return $assets;
    }

    /**
     * Returns active area
     *
     * @param string|null $area
     * @return string
     */
    protected function _getArea($area)
    {
        if ($area === null) {
            $design = Mage::getSingleton('core/design_package');
            $area = $design->getArea();
        }

        return $area;
    }

    /**
     * Returns active package
     *
     * @param string|null $package
     * @return string
     */
    protected function _getPackage($package)
    {
        if ($package === null) {
            $design = Mage::getSingleton('core/design_package');
            $package = $design->getPackageName();
        }

        return $package;
    }

    /**
     * Returns active theme
     *
     * @param string|null $theme
     * @return string
     */
    protected function _getTheme($theme)
    {
        if ($theme === null) {
            $design = Mage::getSingleton('core/design_package');
            $theme = $design->getTheme('skin');
        }

        return $theme;
    }

    /**
     * Returns active handles
     *
     * @param array|null $handles
     * @return array
     */
    protected function _getHandles($handles)
    {
        if (!is_array($handles) || empty($handles)) {
            $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
        }

        return $handles;
    }

    /**
     * Returns cache key
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return string
     */
    protected function _getCacheKey($area, $package, $theme)
    {
        return sprintf('oro_asset_%s_%s_%s', $area, $package, $theme);
    }

    /**
     * Loads cache for scope
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return bool
     */
    protected function _loadCache($area, $package, $theme)
    {
        if (!Mage::app()->useCache(self::CACHE_TYPE)) {
            return false;
        }

        $value = Mage::app()->loadCache($this->_getCacheKey($area, $package, $theme));
        if ($value) {
            $value = unserialize($value);
            $this->_assets = $value['assets'];
            $this->_handles = $value['handles'];

            return true;
        }

        return false;
    }

    /**
     * Saves data to cache
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param string $value
     * @return bool
     */
    protected function _saveCache($area, $package, $theme, $value)
    {
        if (!Mage::app()->useCache(self::CACHE_TYPE)) {
            return false;
        }

        $id = $this->_getCacheKey($area, $package, $theme);
        Mage::app()->saveCache($value, $id, array(self::CACHE_TAG));

        return true;
    }

    /**
     * Loads assets and handles for active theme
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return $this
     */
    protected function _loadData($area, $package, $theme)
    {
        $xml = Mage::getConfig()->loadModulesConfiguration('merge.xml');

        list($jsAssets, $jsHandles) = $this->_getAssetData($xml, self::TYPE_JS, $area, $package, $theme);
        list($cssAssets, $cssHandles) = $this->_getAssetData($xml, self::TYPE_CSS, $area, $package, $theme);

        $this->_assets = array(
            self::TYPE_JS => $jsAssets,
            self::TYPE_CSS => $cssAssets,
        );

        $this->_handles = array(
            self::TYPE_JS => $jsHandles,
            self::TYPE_CSS => $cssHandles,
        );

        $cache = serialize(array(
            'assets' => $this->_assets,
            'handles' => $this->_handles,
        ));

        $this->_saveCache($area, $package, $theme, $cache);

        return $this;
    }

    /**
     * Returns assets and handles
     *
     * @param Mage_Core_Model_Config_Base $xml
     * @param string $type
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return array
     */
    protected function _getAssetData(Mage_Core_Model_Config_Base $xml, $type, $area, $package, $theme)
    {
        $assets = array();
        $handles = array();

        /** @var $node Mage_Core_Model_Config_Element */
        foreach ($xml->getNode($type)->children() as $node) {
            $assetName = $node->getName();
            $assetMethod = $node->method ? (string)$node->method : 'default';
            $assetType = $node->type ? (string)$node->type : 'skin';
            $assetArea = $node->area ? (string)$node->area : 'frontend';
            $assetPackage = $node->package ? (string)$node->package : 'base';
            $assetTheme = $node->theme ? (string)$node->theme : 'default';
            $assetIf = $node->if ? (string)$node->if : null;
            $assetParams = $node->params ? (string)$node->params : null;
            if ($type == self::TYPE_CSS) {
                $assetPath = $node->path ? rtrim((string)$node->path, '/') : 'css';
                $assetFile = sprintf('/%s.css', $node->filename);
                $assetFileMin = sprintf('/%s.min.css', $node->filename);
            } else if ($type == self::TYPE_JS) {
                $assetPath = $node->path ? rtrim((string)$node->path, '/') : 'css';
                $assetFile = sprintf('/%s.js', $node->filename);
                $assetFileMin = sprintf('/%s.min.js', $node->filename);
            } else {
                continue;
            }

            if ($assetType != 'skin') {
                continue;
            }
            if ($assetArea != $area || $assetPackage != $package || $assetTheme != $theme) {
                continue;
            }

            if (!$node->handles || !$node->files) {
                continue;
            }

            $removeList = array();
            /** @var $fileNode Mage_Core_Model_Config_Element */
            foreach ($node->files->children() as $fileNode) {
                if (!$fileNode->remove) {
                    continue;
                }
                $sourceName = $fileNode->getName();
                $attributes = $fileNode->remove->attributes();
                if (empty($attributes['type']) || empty($attributes['file'])) {
                    continue;
                }

                $removeList[$sourceName] = array(
                    (string)$attributes['type'],
                    (string)$attributes['file'],
                );
            }

            /** @var $handle Mage_Core_Model_Config_Element */
            foreach ($node->handles->children() as $handle) {
                $handles[$handle->getName()][] = $assetName;
            }

            $assets[$assetName] = array(
                'file' => $assetPath . $assetFile,
                'file_min' => $assetPath . $assetFileMin,
                'if' => $assetIf,
                'params' => $assetParams,
                'remove' => $removeList,
                'behavior' => $assetMethod,
            );
        }

        return array($assets, $handles);
    }

    /**
     * Clean Assets merge info cache
     *
     * @return void
     */
    public function cleanCache()
    {
        Mage::app()->cleanCache(array(self::CACHE_TAG));
    }
}
