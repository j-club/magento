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
 * @package	ITORIS_PRODUCTIMAGELABELS
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license	http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
class Itoris_LayeredNavigation_Model_Enterprise_Search_Collection extends Enterprise_Search_Model_Resource_Collection {


	public function getFacetedData($field) {
		if (empty($this->_facetedConditions)) {
			$this->_facetedData = array();
			return $this;
		}

		list($query, $params) = $this->_prepareBaseParams();

		$params['solr_params']['facet'] = 'on';
		$params['facet'] = $this->_facetedConditions;
		if (isset($params['filters'][$field])) {
			unset($params['filters'][$field]);
		}
		$result = $this->_engine->getResultForRequest($query, $params);

//		if ($field == 'categories' && isset($result['faceted_data']['categories'])) {
//			if (isset($result['ids'])) {
//				foreach ($result['ids'] as $product) {
//					if (isset($product['show_in_categories']) && is_array($product['show_in_categories'])) {
//						foreach ($product['show_in_categories'] as $categoryId) {
//							if (!isset($result['faceted_data']['categories'][$categoryId])) {
//								$result['faceted_data']['categories'][$categoryId] = 0;
//							}
//							$result['faceted_data']['categories'][$categoryId] += 1;
//						}
//					}
//				}
//			}
//		}

		if (isset($result['faceted_data'][$field])) {
			return $result['faceted_data'][$field];
		}
		return array();
	}

	public function cloneSelect() {
		//$this->clonedSelect = $this->_searchQueryFilters;
		return $this;
	}

	public function useClonedSelect() {
//		$this->_setIsLoaded(false);
//		$this->_items = array();
//		$this->_data = null;
//		$this->_isFiltersRendered = false;
//		$this->_searchQueryFilters = $this->clonedSelect;
//		$this->load();
		return $this;
	}
}
?>