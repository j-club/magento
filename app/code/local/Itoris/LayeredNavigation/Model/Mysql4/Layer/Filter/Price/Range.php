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
 * Resource model for the price range filter.
 */
class Itoris_LayeredNavigation_Model_Mysql4_Layer_Filter_Price_Range
			extends Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Price {

	/**
	 * Returns minimal price of the collection products.
	 *
	 * @param $filter
	 * @return string
	 */
	public function getMinPrice($filter) {
		$select     = $this->_getSelect($filter);
		$connection = $this->_getReadAdapter();
		$response   = $this->_dispatchPreparePriceEvent($filter, $select);

		$table = $this->_getIndexTableAlias();

		$additional   = join('', $response->getAdditionalCalculations());
		$maxPriceExpr = new Zend_Db_Expr("MIN({$table}.min_price {$additional})");

		if (method_exists($this, '_replaceTableAlias')) {
			$maxPriceExpr = $this->_replaceTableAlias($maxPriceExpr);
		}

		$select->columns(array($maxPriceExpr));

		return $connection->fetchOne($select) * $filter->getCurrencyRate();
	}

	/**
	 * Returns maximal price of the collection products.
	 *
	 * @param $filter
	 * @return string
	 */
	public function getMaxPrice($filter) {
		$select     = $this->_getSelect($filter);
		$connection = $this->_getReadAdapter();
		$response   = $this->_dispatchPreparePriceEvent($filter, $select);

		$table = $this->_getIndexTableAlias();

		$additional   = join('', $response->getAdditionalCalculations());
		$maxPriceExpr = new Zend_Db_Expr("MAX({$table}.max_price {$additional})");

		if (method_exists($this, '_replaceTableAlias')) {
			$maxPriceExpr = $this->_replaceTableAlias($maxPriceExpr);
		}

		$select->columns(array($maxPriceExpr));

		return $connection->fetchOne($select) * $filter->getCurrencyRate();
	}

	/**
	 * Add conditions to the select object of the collection.
	 *
	 * @param Itoris_LayeredNavigation_Model_Layer_Filter_Price_Range $filter
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Layer_Filter_Price_Range
	 */
	public function applyFilterRangeToCollection(Itoris_LayeredNavigation_Model_Layer_Filter_Price_Range $filter) {

		$collection = $filter->getLayer()->getProductCollection();
		$collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

		$select     = $collection->getSelect();
		$response   = $this->_dispatchPreparePriceEvent($filter, $select);

		$table      = $this->_getIndexTableAlias();
		$additional = join('', $response->getAdditionalCalculations());
		$rate       = $filter->getCurrencyRate();


		$priceExprMin  = new Zend_Db_Expr("(({$table}.min_price {$additional}) * {$rate})");
		$priceExprMax  = new Zend_Db_Expr("(({$table}.max_price {$additional}) * {$rate})");

		if ($filter->getAppliedPriceMin()) {
			$select->where($priceExprMax.' >= ?', $filter->getAppliedPriceMin());
		}

		if ($filter->getAppliedPriceMax()) {
			$select->where($priceExprMin. ' <= ?', $filter->getAppliedPriceMax());
		}

		return $this;
	}
}
?>