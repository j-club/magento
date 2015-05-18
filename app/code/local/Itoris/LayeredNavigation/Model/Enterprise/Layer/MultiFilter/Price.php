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
 * Model represents price filter as list of the fixed price ranges with ability of multi-select them.
 */
class Itoris_LayeredNavigation_Model_Enterprise_Layer_MultiFilter_Price extends Enterprise_Search_Model_Catalog_Layer_Filter_Price {

	/**
	 * Apply filters from the request.
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param $filterBlock
	 * @return Itoris_LayeredNavigation_Model_Layer_MultiFilter_Price
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
		$filters = $request->getParam($this->getRequestVar());
		if (!is_array($filters)) {
			return $this;
		}

		if (!method_exists($this, '_applyPriceRange')) {
			$filtersToApply = array();
			foreach ($filters as $filterStr) {
				$filter = explode(',', $filterStr);
				if (count($filter) != 2) {
					continue;
				}

				list($index, $range) = $filter;
				if ((int)$index && (int)$range) {
					$this->getLayer()->getState()->addFilter(
						$this->_createItem($this->_renderItemLabel($range, $index), $filterStr)
					);

					$to = $range * $index;
					if ($to < $this->getMaxPriceInt()) {
						$to -= 0.001;
					}
					$from = $range * ($index - 1);
					$filtersToApply[] = array('from' => $from, 'to' => $to);
				}
			}
			if (count($filtersToApply)) {
				$this->getLayer()->getProductCollection()->addFqFilter(array(
					$this->_getFilterField() => $filtersToApply
				));
			}
		} else {
			$filtersToApply = array();
			foreach ($filters as $filterStr) {
				$filter = explode('-', $filterStr);
				$this->getLayer()->getState()->addFilter($this->_createItem(
					$this->_renderRangeLabel($filter[0], $filter[1]),
					$filterStr
				));
				$filtersToApply[] = array(
					'from' => $filter[0],
					'to'   => $filter[1],
				);
			}
			if (!empty($filtersToApply)) {
				$this->getLayer()->getProductCollection()->addFqFilter(array(
					$this->_getFilterField() => $filtersToApply
				));
			}
		}

		return $this;
	}

	/**
	 * Applied filter items should be marked to be shown as checked checkbox
	 * otherwise they will be passed to the browser as hidden inputs.
	 */
	public function updateStateItemsStatus() {
		/** @var $helper Itoris_LayeredNavigation_Helper_Data */
		$helper = Mage::helper('itoris_layerednavigation');
		$helper->initFilterItems($this->getLayer()->getState(), $this->_items);
	}

	/**
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Price
	 */
	protected function _getResource() {
		if (is_null($this->_resource)) {
			$this->_resource = Mage::getResourceModel('itoris_layerednavigation/layer_multiFilter_price');
		}
		return $this->_resource;
	}
}
?>