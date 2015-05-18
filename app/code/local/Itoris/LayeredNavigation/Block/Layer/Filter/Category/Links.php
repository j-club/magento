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
 * Block that shows category filter items as simple list
 */
class Itoris_LayeredNavigation_Block_Layer_Filter_Category_Links extends Mage_Catalog_Block_Layer_Filter_Abstract {

	public function __construct() {
		parent::__construct();
		if ($this->getDataHelper()->isEnabledThirdEngineSearch()) {
			$this->_filterModelName = 'itoris_layerednavigation/enterprise_layer_filter_category_links';
		} else {
			$this->_filterModelName = 'itoris_layerednavigation/layer_filter_category_links';
		}
		$this->setTemplate('itoris/layerednavigation/layer/filter/category/links.phtml');
	}

	/**
	 * Determine if the filter can be shown. Only if it has some items to show.
	 *
	 * @return bool
	 */
	public function canBeShown() {
		return count($this->getFilter()->getItems()) > 0;
	}

	public function getFilter() {
		return $this->_filter;
	}

	/**
	 * Determine if the filter must be shown with hidden items, just the filter title.
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