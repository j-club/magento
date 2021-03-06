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
 * Class that represents category filter as links to these categories.
 */
class Itoris_LayeredNavigation_Model_Layer_Filter_Category_Links extends Mage_Catalog_Model_Layer_Filter_Abstract {

	/**
	 * Prepare filter items: child categories of the current category.
	 *
	 * @return Itoris_LayeredNavigation_Model_Layer_Filter_Category_Links
	 */
	protected function _initItems() {

		$items=array();
		$childrenCategories = $this->getLayer()->getCurrentCategory()->getChildrenCategories();
		$this->getLayer()->getProductCollection()->addCountToCategories($childrenCategories);
		/** @var $category Mage_Catalog_Model_Category */
		foreach ($childrenCategories as $category) {
			if ($category->getIsActive() && $category->getProductCount() > 0) {
				$items[] = $category;
			}
		}

		$this->_items = $items;
		return $this;
	}

	/**
	 * Return title fo the filter.
	 *
	 * @return string
	 */
	public function getName() {
		return Mage::helper('catalog')->__('Category');
	}

	/**
	 * Applied filter items should be marked to be shown as checked checkbox
	 * otherwise they will be passed to the browser as hidden inputs.
	 */
	public function updateStateItemsStatus() {
		// leave it empty intentionally
	}
}
?>