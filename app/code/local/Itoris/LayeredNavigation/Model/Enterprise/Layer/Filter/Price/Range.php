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
 * Model that represents price filter as arbitrary range.
 *
 * @method int getAppliedPriceMin()
 * @method int getAppliedPriceMax()
 * @method setAppliedPriceMin($n)
 * @method setAppliedPriceMax($n)
 */
class Itoris_LayeredNavigation_Model_Enterprise_Layer_Filter_Price_Range extends Enterprise_Search_Model_Catalog_Layer_Filter_Price {

	/**
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Layer_Filter_Price_Range
	 */
	protected function _getResource() {
		if (is_null($this->_resource)) {
			$this->_resource = Mage::getResourceModel('itoris_layerednavigation/layer_filter_price_range');
		}
		return $this->_resource;
	}

	/**
	 * Apply filters from request to the collection.
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param $filterBlock
	 * @return Itoris_LayeredNavigation_Model_Layer_Filter_Price_Range
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {

		$filter = $request->getParam($this->getRequestVar());
		if (!is_array($filter)) {
			return $this;
		}

		if (isset($filter['min']) && $filter['min'] != '') {
			$this->setAppliedPriceMin((int) $filter['min']);
		}

		if (isset($filter['max']) && $filter['max'] != '') {
			$this->setAppliedPriceMax((int) $filter['max']);
		}

		if ($this->getAppliedPriceMin() !== null) {
			$this->addStateItem('Minimal Price', $this->getAppliedPriceMin())
								->setRequestVarKey('min');
		}

		if ($this->getAppliedPriceMax() !== null) {
			$this->addStateItem('Maximal Price', $this->getAppliedPriceMax())
							->setRequestVarKey('max');
		}

		if ($this->getAppliedPriceMax() || $this->getAppliedPriceMin()) {
			$this->applyToCollection();
		}

		return $this;
	}

	protected function applyToCollection() {
		$from = (float)$this->getAppliedPriceMin();
		if (!$from) {
			$from = $this->getMinPriceInt();
		}
		$to = (float) $this->getAppliedPriceMax();
		if (!$to) {
			$to = $this->getMaxPriceInt();
		}

		$value = array(
			$this->_getFilterField() => array(
				'from' => $from,
				'to'   => $to
			)
		);

		$this->getLayer()->getProductCollection()->addFqFilter($value);

		return $this;
	}

	/**
	 * Add item to the state
	 *
	 * @param $label
	 * @param $value
	 * @return Mage_Catalog_Model_Layer_Filter_Item
	 */
	protected function addStateItem($label, $value) {
		$stateItem = $this->_createItem($label, $value);
		$this->stateItems[] = $stateItem;

		$this->getLayer()->getState()->addFilter($stateItem);
		return $stateItem;
	}

	/**
	 * Returns minimal price of the current collection products.
	 *
	 * @return mixed|string
	 */
	public function getMinPriceInt() {
		$minPrice = $this->getData('min_price_int');
		if (is_null($minPrice)) {
			$searchParams = $this->getLayer()->getProductCollection()->getExtendedSearchParams();
			$uniquePart = strtoupper(md5(serialize($searchParams)));
			$cacheKey = 'MINPRICE_' . $this->getLayer()->getStateKey() . '_' . $uniquePart;

			$cachedData = Mage::app()->loadCache($cacheKey);
			if (!$cachedData) {
				$stats = $this->getLayer()->getProductCollection()->getStats($this->_getFilterField());
				$min = $stats[$this->_getFilterField()]['min'];
				if (!is_numeric($min)) {
					$min = parent::getMinPriceInt();
				}

				$cachedData = (float) $min;
				$tags = $this->getLayer()->getStateTags();
				$tags[] = self::CACHE_TAG;
				Mage::app()->saveCache($cachedData, $cacheKey, $tags);
			}

			$this->setData('min_price_int', $cachedData);
		}

		return $minPrice;
	}

	/**
	 * Returns maximal price of the current collection products.
	 *
	 * @return mixed|string
	 */
	public function getMaxPriceInt() {
		$maxPrice = $this->getData('max_price_int');
		if (is_null($maxPrice)) {
			$maxPrice = parent::getMaxPriceInt();
			$this->setData('max_price_int', $maxPrice);
		}

		return $maxPrice;
	}

	/**
	 * Check conditions to show this filter.
	 *
	 * @return bool
	 */
	public function canBeShown() {
		if (abs($this->getMaxPriceInt() - $this->getMinPriceInt()) <= 0.001) {
			return false;
		}

		if ($this->getAppliedPriceMax() && $this->getAppliedPriceMax() < $this->getMinPriceInt()) {
			return false;
		}

		if ($this->getAppliedPriceMin() && $this->getMaxPriceInt() < $this->getAppliedPriceMin()) {
			return false;
		}

		if ($this->_getResource()->getProductsCount($this) <= 1) {
			return false;
		}

		return true;
	}

	/**
	 * Applied filter items should be marked to be shown as checked checkbox
	 * otherwise they will be passed to the browser as hidden inputs.
	 */
	public function updateStateItemsStatus() {
		if ($this->canBeShown()) {
			foreach ($this->stateItems as $stateItem) {
				$stateItem->setOutputInCheckbox(true);
			}
		}
	}

	protected $stateItems  = array();
}
?>