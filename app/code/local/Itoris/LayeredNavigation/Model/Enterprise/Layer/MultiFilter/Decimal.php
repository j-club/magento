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
 * @package	   ITORIS_LAYEREDNAVIGATION
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license	   http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

class Itoris_LayeredNavigation_Model_Enterprise_Layer_MultiFilter_Category extends Enterprise_Search_Model_Catalog_Layer_Filter_Decimal {

	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
		/**
		 * Filter must be string: $index, $range
		 */
		$filters = $request->getParam($this->getRequestVar());
		if (!is_array($filters)) {
			return $this;
		}
		foreach ($filters as $filter) {
			$filter = explode(',', $filter);
			if (count($filter) != 2) {
				return $this;
			}

			list($index, $range) = $filter;
			if ((int)$index && (int)$range) {
				$this->setRange((int)$range);

				$this->applyFilterToCollection($this, $range, $index);
				$this->getLayer()->getState()->addFilter(
					$this->_createItem($this->_renderItemLabel($range, $index), $filter)
				);

				$this->_items = array();
			}
		}

		return $this;
	}
}
?>