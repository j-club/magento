<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */


class Niveus_ProductVideo_Block_Video extends Mage_Catalog_Block_Product_View_Abstract
{
    protected $_videosCollection = null;
	
	
    public function getCode($video)
    {
	   return $video->getVideoCode();
    }
    
    public function getTitle($video)
    {
	if ($video->getVideoTitle())
	{
		return $video->getVideoTitle();
	}
	else
	{
		return $this->getProduct()->getName();
	}
    }
    public function getVideoCode($videoLink)
    {
        $videoLink = trim(strval($videoLink));
        return substr($videoLink,-11,11);
    }
    
    protected function _getProductVideos()
    {
        $storeId = Mage::app()->getStore()->getId();
        
        if (is_null($this->_videosCollection))
    	{
            //$this->_videosCollection = $this->_getVideosCollection($storeId)->getSize() ? $this->_getVideosCollection($storeId) : $this->_getVideosCollection();
            $videoLinks = array();
            $product = $this->getProduct();
            if ($product->getYoutubeVideoOne()) {
                $videoLinks[] = $product->getYoutubeVideoOne();
            }
            if ($product->getYoutubeVideoTwo()) {
                $videoLinks[] = $product->getYoutubeVideoTwo();
            }
            if ($product->getYoutubeVideoThree()) {
                $videoLinks[] = $product->getYoutubeVideoThree();
            }
            $this->_videosCollection = $videoLinks;
    	}
        
        return $this->_videosCollection;
    }
    
    
    protected function _getVideosCollection($storeId = 0)
    {
        return Mage::getModel('productvideo/videos')
            ->getCollection()
 	    ->addFieldToFilter('product_id', $this->getProduct()->getId())
            ->addFieldToFilter('store_id', $storeId);
    }

    public function _toHtml()
    {
        if(Mage::getStoreConfig('niveus_productvideo/settings/enable')) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
