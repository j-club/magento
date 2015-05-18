<?php
/**
 * Helper for Aw_Onsale extension
 *
 * @category   MageCore
 * @package    Oro_Onsale
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Onsale_Helper_Data extends AW_Onsale_Helper_Data
{
    public function urlExists($url = null)
    {
        if ($url == null) {
            return false;
        }
        $key = md5($url);
        if (isset($this->_urlExistsCache[$key])) {
            return $this->_urlExistsCache[$key];
        }

        $existsCache = $this->_getUrlExistsCache($key);
        if ($existsCache) {
            $this->_urlExistsCache[$key] = $existsCache;
            return $this->_urlExistsCache[$key];
        }
        $baseUrl = str_replace('index.php/','',Mage::getBaseUrl());
        if(strpos($url, $baseUrl) === 0){
            $path = Mage::getBaseDir().'/'.str_replace($baseUrl, '', $url);
            $connectable = file_exists($path) && is_file($path);
            $this->_urlExistsCache[$key] = $connectable;
            $this->_setUrlExistsCache($key, $connectable);
            return $this->_urlExistsCache[$key];
        }

        return parent::urlExists($url);
    }

    public function getImageSize($url)
    {
        $baseUrl = str_replace('index.php/','',Mage::getBaseUrl());
        if(strpos($url, $baseUrl) === 0){
            $path = Mage::getBaseDir().'/'.str_replace($baseUrl, '', $url);
            $key = md5($url);
            if (!isset($this->_imageSizeCache[$key])) {

                $size = $this->_getImageSizeCache($key);
                if ($size) {
                    $this->_imageSizeCache[$key] = $size;
                    return $this->_imageSizeCache[$key];
                }

                $res= $this->_getImageSizeFromFile($path);

                $this->_imageSizeCache[$key] = $res;
                $this->_setImageSizeCache($key, $res);

                return $this->_getImageSizeFromFile($path);
            }

            return $this->_imageSizeCache[$key];
        }
        return parent::getImageSize($url);
    }

    protected function _getImageSizeFromFile($path)
    {
        if(file_exists($path) && is_file($path)){
            $result = getimagesize($path);

            $_w = $result[0];
            $_h = $result[1];

            return array($_w, $_h);
        }

        return array(
            AW_Onsale_Model_Label::DEFAULT_IMAGE_WIDTH,
            AW_Onsale_Model_Label::DEFAULT_IMAGE_HEIGHT,
        );
    }

}
