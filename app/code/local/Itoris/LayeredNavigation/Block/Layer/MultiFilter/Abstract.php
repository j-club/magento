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
 * An abstract class that represents filter block with ability to
 * apply many filter items at the same time.
 *
 * @method Itoris_LayeredNavigation_Block_Layer_MultiFilter_Abstract setLayer(Itoris_LayeredNavigation_Model_Layer $value)
 * @method Itoris_LayeredNavigation_Block_Layer_MultiFilter_Abstract setAttributeModel($attribute)
 * @method Itoris_LayeredNavigation_Model_Layer getLayer()
 */
abstract class Itoris_LayeredNavigation_Block_Layer_MultiFilter_Abstract
		extends Mage_Catalog_Block_Layer_Filter_Abstract {

	/**
	 * Instantiate and prepare filter model.
	 *
	 * @return Itoris_LayeredNavigation_Block_Layer_MultiFilter_Abstract
	 */
	protected function _initFilter() {
		if (!$this->_filterModelName) {
			Mage::throwException(Mage::helper('catalog')->__('Filter model name must be declared.'));
		}

		$this->_filter = Mage::getModel($this->_filterModelName)
			->setLayer($this->getLayer());

		$this->_prepareFilter();

		return $this;
	}

	/**
	 * @return Itoris_LayeredNavigation_Model_Layer_MultiFilter_Attribute
	 */
	public function getFilter() {
		return $this->_filter;
	}

	/**
	 * Check if some item of the current filter was applied to the collection.
	 *
	 * @return bool
	 */
	public function hasFiltersInState() {
		$filterItems = $this->getLayer()->getState()->getFilters();
		/** @var $filterItem Mage_Catalog_Model_Layer_Filter_Item */
		foreach ($filterItems as $filterItem) {
			if ($filterItem->getFilter() == $this->getFilter()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if this filter can be shown.
	 *
	 * @return bool
	 */
	public function canBeShown() {
		return $this->getItemsCount() > 0;
	}

	/**
	 * Check if this filter must be shown with hidden filter items
	 * (customer can hide filter items by clicking on it's title)
	 *
	 * @return bool
	 */
	public function isClosed() {
		$closedFilters = $this->getRequest()->getPost('closed_filters', array());
		return in_array($this->getFilter()->getRequestVar(), $closedFilters);
	}

	public function addFacetCondition() {
		$this->_filter->addFacetCondition();
		return $this;
	}

	/**
	 * @return Itoris_LayeredNavigation_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_layerednavigation');
	}
}
?>