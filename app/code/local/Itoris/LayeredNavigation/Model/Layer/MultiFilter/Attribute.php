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
class Itoris_LayeredNavigation_Model_Layer_MultiFilter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute {

	/**
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Attribute
	 */
	protected function _getResource() {
		if (is_null($this->_resource)) {
			$this->_resource = Mage::getResourceModel('itoris_layerednavigation/layer_multiFilter_attribute');
		}
		return $this->_resource;
	}

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
			$text = $this->_getOptionText($filterValue);
			if ($text) {
				$this->getLayer()->getState()->addFilter($this->_createItem($text, $filterValue));
				$addToFilter[] = $filterValue;
			}
		}

		if (count($addToFilter) > 0) {
			$this->_getResource()->applyFilterToCollection($this, $addToFilter);
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