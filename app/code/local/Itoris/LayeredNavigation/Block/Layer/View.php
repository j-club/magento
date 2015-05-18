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
 * Block of entire layered navigation. Handles main sequence of the filter operations.
 */
class Itoris_LayeredNavigation_Block_Layer_View extends Mage_Catalog_Block_Layer_View {

	protected function _construct() {
		parent::_construct();
		$this->setTemplate('itoris/layerednavigation/layer/view.phtml');

		if ($this->getSettings()->getMulticategoryEnabled()) {
			$this->_categoryBlockName           = 'itoris_layerednavigation/layer_multiFilter_category_tree';
		} else {
			$this->_categoryBlockName           = 'itoris_layerednavigation/layer_filter_category_links';
		}

		if ($this->getSettings()->getGraphicalPriceEnabled()) {
			$this->_priceFilterBlockName        = 'itoris_layerednavigation/layer_filter_price_range';
			$this->_priceBlockNameInLayout 		= 'layer_filter_price_range';
		} else {
			$this->_priceFilterBlockName        = 'itoris_layerednavigation/layer_multiFilter_price';
		}

		$this->_attributeFilterBlockName    = 'itoris_layerednavigation/layer_multiFilter_attribute';
		$this->_decimalFilterBlockName = 'itoris_layerednavigation/layer_multiFilter_decimal';
	}

	/**
	 * @return Itoris_LayeredNavigation_Model_Settings
	 */
	public function getSettings() {
		return Mage::getSingleton('itoris_layerednavigation/settings');
	}

	/**
	 * Register our layered navigation model as current layered navigation.
	 *
	 * @return Itoris_LayeredNavigation_Model_Layer
	 */
	public function getLayer() {
		/** @var $layer Itoris_LayeredNavigation_Model_Layer */
		$layer = Mage::registry('current_layer');

		if (!($layer instanceof Itoris_LayeredNavigation_Model_Layer)) {
			$layer = null;
			Mage::unregister('current_layer');
		}

		if ($layer == null) {
			Mage::register('current_layer', Mage::getSingleton('itoris_layerednavigation/layer'));
			$layer = Mage::registry('current_layer');
			if (!method_exists('Mage_Catalog_Block_Product_List', 'getLayer')) {
				Mage::unregister('_singleton/catalog/layer');
				Mage::register('_singleton/catalog/layer', $layer);
			} else {
				Mage::getSingleton('catalog/layer')->setState($layer->getState());
			}
		}

		if ($layer instanceof Itoris_LayeredNavigation_Model_Layer) {
			return $layer;
		} else {
			throw new Exception('Invalid layer class');
		}
	}

	/**
	 * Handles sequence of the layered navigation initialization, filters impact to the collection
	 * and calculation of filter items to show.
	 */
	protected function _prepareLayout() {
		$thirdEngineEnabled = $this->getDataHelper()->isEnabledThirdEngineSearch();
		Mage::app()->setUseSessionInUrl(false);

		$stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
			->setLayer($this->getLayer());

		/** @var $categoryBlock Itoris_LayeredNavigation_Block_Layer_MultiFilter_Category */
		$categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
			->setLayer($this->getLayer())
			->init();

		$this->setChild('layer_state', $stateBlock);
		$this->setChild('category_filter', $thirdEngineEnabled ? $categoryBlock->addFacetCondition() : $categoryBlock);

		$blocks = array();
		if ($categoryBlock->getFilter()) {
			$blocks[] = $categoryBlock;
		}

		$this->getLayer()->getProductCollection()->cloneSelect();
		$hasItems = count($this->getLayer()->getProductCollection()->getAllIds());

		$filterableAttributes = $this->_getFilterableAttributes();
		foreach ($filterableAttributes as $attribute) {
			if ($attribute->getAttributeCode() == 'price') {
				$block = $this->getLayout()->createBlock($this->_priceFilterBlockName, $this->_priceBlockNameInLayout);
			} elseif ($attribute->getBackendType() == 'decimal') {
				$block = $this->getLayout()->createBlock($this->_decimalFilterBlockName);
			} else {
				$block = $this->getLayout()->createBlock($this->_attributeFilterBlockName);
			}

			$block->setLayer($this->getLayer())
					->setAttributeModel($attribute)
					->init();

			$this->setChild($attribute->getAttributeCode() . '_filter', $thirdEngineEnabled ? $block->addFacetCondition() : $block);

			if ($block->getFilter()) {
				$blocks[] = $block;
			}
		}
		$applyAfter = array();
		foreach ($blocks as $block) {
			$filter = $block->getFilter();

			if (!$this->isFilterCleared($filter)) {
				if ($filter->getRequestVar() == 'price') {
					$applyAfter[] = $filter;
					continue;
				}
				$filter->apply($this->getRequest(),$filter);
			}
		}

		foreach ($applyAfter as $filter) {
			$filter->apply($this->getRequest(),$filter);
		}

		if ($hasItems && !count($this->getLayer()->getProductCollection()->getAllIds())) {
			$this->getLayer()->getProductCollection()->useClonedSelect();
			$this->getDataHelper()->setNotUseFilter(true);
		}

		foreach ($blocks as $block) {
			$block->getFilter()->getItems();
		}

		foreach ($blocks as $block) {
			$block->getFilter()->updateStateItemsStatus();
		}

		$this->getLayer()->apply();
	}

	/**
	 * Check if $filter must not be applied to the collection.
	 *
	 * @param $filter
	 * @return bool
	 */
	protected function isFilterCleared($filter) {
		$clear = $this->getRequest()->getParam('clear');
		if ($clear == 'all') {
			return true;
		}

		if ($clear == $filter->getRequestVar()) {
			return true;
		}

		return false;
	}

	/**
	 * Check if at least one filter item was applied to the collection.
	 *
	 * @return bool
	 */
	public function isStateNotEmpty() {
		return count($this->getLayer()->getState()->getFilters()) > 0;
	}

	/**
	 * @return Itoris_LayeredNavigation_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_layerednavigation');
	}

	protected $_stateBlockName = 'catalog/layer_state';
	protected $_categoryBlockName = '';
	protected $_priceFilterBlockName = '';
	protected $_attributeFilterBlockName = '';

	protected $_priceBlockNameInLayout = '';


}
?>