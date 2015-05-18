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
 * Shows categories filter as tree with all subcategories.
 */
class Itoris_LayeredNavigation_Block_Layer_MultiFilter_Category_Tree
			extends Itoris_LayeredNavigation_Block_Layer_MultiFilter_Category {

	public function __construct() {
		parent::__construct();
		$this->setTemplate('itoris/layerednavigation/layer/filter/category/tree.phtml');
		if ($this->getDataHelper()->isEnabledThirdEngineSearch()) {
			$this->_filterModelName = 'itoris_layerednavigation/enterprise_layer_multiFilter_category_tree';
		} else {
			$this->_filterModelName = 'itoris_layerednavigation/layer_multiFilter_category_tree';
		}
	}

	/**
	 * Returns html of categories layer and all sublayers. (Layer in this case is a set of
	 * categories from the same nesting level).
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @return string
	 */
	public function getLayerHtml(Mage_Catalog_Model_Category $category) {
		if (count($category->getLoadedChildrenCategories()) == 0
				|| !$this->isSubcategoriesContainProducts($category)) {

			return '';
		}

		/** @var $filter Itoris_LayeredNavigation_Model_Layer_MultiFilter_Category_Tree */
		$filter = $this->_filter;
		$oldItems = $filter->getItems();
		$filter->setItems($category->getLoadedChildrenCategories());
		$html = $this->_toHtml();
		$filter->setItems($oldItems);
		return $html;
	}

	/**
	 * Check if category or it's subcategories contains at least one product.
	 *
	 * @param $category
	 * @return bool
	 */
	protected function isSubcategoriesContainProducts($category) {
		$subs = $category->getLoadedChildrenCategories();
		foreach ($subs as $sub) {
			if ($sub->getProductCount() > 0) {
				return true;
			}
		}

		return false;
	}

	public function canBeShown() {
		foreach ($this->getItems() as $item) {
			if ($item->getProductCount() > 0) {
				return true;
			}
		}
		return false;
	}
}
?>