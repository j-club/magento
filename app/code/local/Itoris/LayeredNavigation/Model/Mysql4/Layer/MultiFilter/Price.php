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
 * Resource Model for price filter with set of fixed price ranges.
 */
class Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Price
			extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price {

	/**
	 * Add conditions to the collection.
	 *
	 * @param Itoris_LayeredNavigation_Model_Layer_MultiFilter_Price $filter
	 * @param array $filtersToApply
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Price
	 */
	public function applyFiltersToCollection(Itoris_LayeredNavigation_Model_Layer_MultiFilter_Price $filter,
											array $filtersToApply) {

		$collection = $filter->getLayer()->getProductCollection();
		$collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

		$select     = $collection->getSelect();
		$response   = $this->_dispatchPreparePriceEvent($filter, $select);

		$table      = $this->_getIndexTableAlias();
		$additional = join('', $response->getAdditionalCalculations());
		$rate       = $filter->getCurrencyRate();


		$priceExpr  = new Zend_Db_Expr("(({$table}.min_price {$additional}) * {$rate})");

	//	if (method_exists($this, '_replaceTableAlias')) {
	//		$priceExpr = $this->_replaceTableAlias($priceExpr);
	//	}
		$i = 1;
		$from = null;
		$to = null;
		foreach ($filtersToApply as $filter) {
			if (isset($filter['from'])) {
				$from = $filter['from'];
				$to = $filter['to'];
			} else {
				$range = $filter['range'];
				$index = $filter['index'];
			}
			if ($i == 1) {
				$i++;
				$select->where($priceExpr.' >= ?', is_null($from) ? $range * ($index - 1) : $from);
			} else {
				$select->orWhere($priceExpr.' >= ?', is_null($from) ? $range * ($index - 1) : $from);
			}
			if (is_null($to) || $to) {
				$select->where($priceExpr.' < ?', is_null($to) ? $range * $index : $to);
			}
		}

		return $this;
	}

	/**
	 * Remove conditions from the select related to this filter
	 * to select all filter items of this filter.
	 *
	 * @param $filter
	 * @return Varien_Db_Select
	 */
	protected function _getSelect($filter) {
		$select = parent::_getSelect($filter);
		$wherePart = $select->getPart(Varien_Db_Select::WHERE);
		$newWherePart = array();

		// exclude price filters
		$firstPart = true;
		foreach($wherePart as $where) {
			$tableAlias = 'price_index.';
			if (method_exists($this, '_replaceTableAlias')) {
				$tableAlias = $this->_replaceTableAlias($tableAlias);
			}

			if (strpos($where, $tableAlias . 'min_price')) {
				continue;
			}

			if (strpos($where, $tableAlias . 'max_price')) {
				continue;
			}

			if ($firstPart && method_exists($this, '_replaceTableAlias')) {
				$where = preg_replace('/^AND/', '', trim($where));
				$firstPart = false;;
			}

			$newWherePart[] = $where;
		}

		$select->setPart(Varien_Db_Select::WHERE, $newWherePart);

		return $select;
	}

	/**
	 * Return products count.
	 *
	 * @param $filter
	 * @return string
	 */
	public function getProductsCount($filter) {
		$select = $this->_getSelect($filter);
		$select->columns('COUNT(distinct e.entity_id) as count');
		$conn = $this->_getReadAdapter();
		return $conn->fetchOne($select);
	}

	public function getCount($filter, $range) {
		if (method_exists($this, '_getFullPriceExpression')) {
			$select = $this->_getSelect($filter);
			$priceExpression = $this->_getFullPriceExpression($filter, $select);

			/**
			 * Check and set correct variable values to prevent SQL-injections
			 */
			$range = floatval($range);
			if ($range == 0) {
				$range = 1;
			}
			$countExpr = new Zend_Db_Expr('COUNT(distinct e.entity_id)');
			$rangeExpr = new Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1");

			$select->columns(array(
				'range' => $rangeExpr,
				'count' => $countExpr
			));
			$select->group($rangeExpr)->order("$rangeExpr ASC");

			return $this->_getReadAdapter()->fetchPairs($select);
		} else {
			return parent::getCount($filter, $range);
		}
	}
}
?>