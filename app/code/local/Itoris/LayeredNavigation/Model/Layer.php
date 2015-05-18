<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_LAYEREDNAVIGATION
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

/**
 * Layer class
 */
class Itoris_LayeredNavigation_Model_Layer extends Mage_Catalog_Model_Layer {

	/**
	 * Change type of the collection to which has ability to filter by multiple categories.
	 *
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Product_Collection
	 */
	public function getProductCollection() {

		if (!$this->productCollection) {
			if (Mage::helper('itoris_layerednavigation')->isEnabledThirdEngineSearch()) {
				/** @var $collection Itoris_LayeredNavigation_Model_Enterprise_Search_Collection */
				$collection = Mage::getModel('itoris_layerednavigation/enterprise_search_collection');
				$collection->setEngine(Mage::helper('catalogsearch')->getEngine());
				$collection->setStoreId($this->getCurrentCategory()->getStoreId());
				$this->prepareProductCollection($collection);
				$this->productCollection = $collection;
			} else {
				/** @var $collection Itoris_LayeredNavigation_Model_Mysql4_Product_Collection */
				$collection = Mage::getResourceModel('itoris_layerednavigation/product_collection');
				if ($this->getCurrentCategory()->getIsAnchor()) {
					$collection
						->setStoreId(Mage::app()->getStore()->getId())
						->addCategoryFilter($this->getCurrentCategory());
				}

				$this->prepareProductCollection($collection);
				$this->productCollection = $collection;
			}
		}

		return $this->productCollection;
	}

	public function prepareProductCollection($collection) {
		$collection
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents();
		$queryText = Mage::helper('catalogsearch')->getQueryText();
		if (!$this->getCurrentCategory()->getIsAnchor() || !empty($queryText)) {
			$collection
				->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
				->setStore(Mage::app()->getStore())
				->addStoreFilter()
				->addUrlRewrite();
		} else {
			$collection->addUrlRewrite($this->getCurrentCategory()->getId());
		}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		if (Mage::app()->getRequest()->getParam('q')) {
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
		} else {
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		}

		return $this;
	}

	public function getFilterableAttributes() {
		if (Mage::helper('itoris_layerednavigation')->isEnabledThirdEngineSearch()) {
			$setIds = $this->_getSetIds();
			if (!$setIds) {
				return array();
			}
			/* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
			$collection = Mage::getResourceModel('catalog/product_attribute_collection')
				->setItemObjectClass('catalog/resource_eav_attribute');

			if (Mage::helper('enterprise_search')->getTaxInfluence()) {
				$collection->removePriceFilter();
			}

			$collection
				->setAttributeSetFilter($setIds)
				->addStoreLabel(Mage::app()->getStore()->getId())
				->setOrder('position', 'ASC');
			$collection = $this->_prepareAttributeCollection($collection);
			$collection->addIsFilterableInSearchFilter();
			$collection->load();

			return $collection;
		} else {
			return parent::getFilterableAttributes();
		}
	}

	/**
	 * @var Itoris_LayeredNavigation_Model_Mysql4_Product_Collection
	 */
	protected $productCollection;

}
?>