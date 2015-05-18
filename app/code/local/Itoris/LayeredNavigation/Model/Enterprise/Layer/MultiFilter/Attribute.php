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
 * Model that represents product attribute filter.
 */
class Itoris_LayeredNavigation_Model_Enterprise_Layer_MultiFilter_Attribute extends Enterprise_Search_Model_Search_Layer_Filter_Attribute {

	/**
	 * Apply filters from the request.
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param $filterBlock
	 * @return Itoris_LayeredNavigation_Model_Layer_MultiFilter_Attribute
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
		$filter = $request->getParam($this->_requestVar);
		if (!is_array($filter)) {
			return $this;
		}

		$addToFilter = array();
		foreach($filter as $filterValue) {
			$this->getLayer()->getState()->addFilter($this->_createItem($filterValue, $filterValue));
			$addToFilter[] = $filterValue;
		}
		if (count($addToFilter)) {
			$this->applyFilterToCollection($this, $addToFilter);
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
}
?>