<?php/** * Video Plugin for Magento *  * This source file is subject to the Open Software License (OSL 3.0) * that is bundled with this package in the file LICENSE.txt. * It is also available through the world-wide-web at this URL: * http://opensource.org/licenses/osl-3.0.php * * @category   Niveus * @package    Niveus_ProductVideo * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com) * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) * @author     Niveus Solutions <support@niveussolutions.com> *//** * Widget displays the vedio plugin list  */class Niveus_ProductVideo_Block_Adminhtml_Catalog_Product_Edit_Tab_Videos extends Mage_Adminhtml_Block_Widget    implements Mage_Adminhtml_Block_Widget_Tab_Interface{        /**     * List of all videos collection     * @var type      */    protected $videosCollection = null;	     /**     * Initialization     */	public function __construct()        {		parent::__construct();        $this->setTemplate('niveusproductvideo/tab/videos.phtml');	}	      /**       * Loads the product        * @return type       */	public function getProduct()    {        return Mage::registry('current_product');    }	        /**          * Get the list of videos          * @return type          */   	public function getVideosCollection()        {    	       $storeId = (int) $this->getRequest()->getParam('store', false);               if (is_null($this->videosCollection))    	       {    		   $this->videosCollection = Mage::getModel('productvideo/videos')    			->getCollection()    			->addFieldToFilter('product_id', $this->getProduct()->getId())                        ->addFieldToFilter('store_id', $storeId);    	       }    	    	       return $this->videosCollection;       }    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Videos');
    }
    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Videos');
    }
    public function canShowTab()
    {        if (!$this->getProduct()->getData('attribute_set_id')) {            return false;        }        return true;
    }
    public function isHidden()
    {
        return false;
    }}