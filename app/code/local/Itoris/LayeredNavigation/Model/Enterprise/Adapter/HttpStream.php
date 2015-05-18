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

class Itoris_LayeredNavigation_Model_Enterprise_Adapter_HttpStream extends Enterprise_Search_Model_Adapter_HttpStream {

	protected function _prepareFilters($filters) {
		$result = array();

		if (is_array($filters) && !empty($filters)) {
			foreach ($filters as $field => $value) {
				if (is_array($value)) {
					if ($field == 'price' || isset($value['from']) || isset($value['to'])) {
						if (isset($value['from']) || isset($value['to'])) {
							$fieldCondition = $this->_preparePriceFilterCondition($field, $value);
						} else {
							$fieldCondition = array();
							foreach ($value as $part) {
								$fieldCondition[] = $this->_preparePriceFilterCondition($field, $part);
							}
							$fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
						}
					} else {
						$fieldCondition = array();
						foreach ($value as $part) {
							if (is_array($part) && (isset($part['from']) || isset($part['to']))) {
								$fieldCondition[] = $this->_preparePriceFilterCondition($field, $part);
							} else {
								$part = $this->_prepareFilterQueryText($part);
								$fieldCondition[] = $this->_prepareFieldCondition($field, $part);
							}
						}
						$fieldCondition = '(' . implode(' OR ', $fieldCondition) . ')';
					}
				} else {
					$value = $this->_prepareFilterQueryText($value);
					$fieldCondition = $this->_prepareFieldCondition($field, $value);
				}

				$result[] = $fieldCondition;
			}
		}

		return $result;
	}

	protected function _preparePriceFilterCondition($field, $value) {
		$from = (isset($value['from']) && !empty($value['from']))
			? $this->_prepareFilterQueryText($value['from'])
			: '*';
		$to = (isset($value['to']) && !empty($value['to']))
			? $this->_prepareFilterQueryText($value['to'])
			: '*';
		return "$field:[$from TO $to]";
	}
}
?>