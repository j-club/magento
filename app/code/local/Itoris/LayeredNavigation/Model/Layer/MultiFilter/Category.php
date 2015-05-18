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
 * Model that represents category filter as list of subcategories.
 */
class Itoris_LayeredNavigation_Model_Layer_MultiFilter_Category extends Mage_Catalog_Model_Layer_Filter_Category {

	/**
	 * Apply filters from the request
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param $filterBlock
	 * @return Itoris_LayeredNavigation_Model_Layer_MultiFilter_Category
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
		$filter = $request->getParam($this->getRequestVar());
		if (!is_array($filter)) {
			return $this;
		}

		$this->_categoryId = null;

		Mage::register('current_category_filter', $this->getCategory(), true);

		/** @var $categoryResource Mage_Catalog_Model_Resource_Category */
		$categoryResource = Mage::getResourceModel('catalog/category');
		$filter = $categoryResource->verifyIds($filter);
		$filter = $this->filerCategoriesByItsParent($filter, $this->getCategory());

		if (count($filter) == 0) {
			return $this;
		}

		$this->updateCategoryFilter($this->getLayer()->getProductCollection(), $filter);

		foreach ($filter as $categoryId) {
			$this->getLayer()->getState()->addFilter(
				$this->_createItem("Category : $categoryId", $categoryId)
			);
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

	/**
	 * Add filters to the collection.
	 * @param Itoris_LayeredNavigation_Model_Mysql4_Product_Collection $collection
	 * @param $categories
	 */
	protected function updateCategoryFilter(Itoris_LayeredNavigation_Model_Mysql4_Product_Collection $collection,
											$categories) {

		$collection->addCategoriesFilter($categories);
	}

	/**
	 * Returns list of subcategories recursively.
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @return mixed
	 */
	protected function getSubcategories(Mage_Catalog_Model_Category $category) {
		if (!isset($this->subcategories[$category->getId()])) {
			$list = array();
			$categories = $category->getChildrenCategories();
			$this->getAllChildCategories($categories, $list);
			$this->subcategories[$category->getId()] = $list;
		}

		return $this->subcategories[$category->getId()];
	}

	/**
	 * Adds child categories of the current roots to the array and recursively
	 * execute this action on these child categories.
	 *
	 * @param $roots
	 * @param $array
	 */
	protected function getAllChildCategories($roots, &$array) {
		/** @var $root Mage_Catalog_Model_Category */
		foreach($roots as $root) {
			$array[] = $root;
			$childrenCategories = $root->getChildrenCategories();
			$root->setLoadedChildrenCategories($childrenCategories);
			if (count($childrenCategories) > 0) {
				$this->getAllChildCategories($childrenCategories, $array);
			}
		}
	}

	/**
	 * Method used to don't allow apply category filters that doesn't
	 * belong to the current category subcategories.
	 *
	 * @param array $categories
	 * @param Mage_Catalog_Model_Category $parent
	 * @return array
	 */
	protected function filerCategoriesByItsParent(array $categories, Mage_Catalog_Model_Category $parent) {
		$trueCategories = $this->getSubcategories($parent);
		$trueCategoryIds = array();
		foreach ($trueCategories as $trueCategory) {
			$trueCategoryIds[$trueCategory->getId()] = $trueCategory;
		}

		foreach ($categories as $key => $categoryIdToVerify) {
			if (!isset($trueCategoryIds[$categoryIdToVerify])) {
				unset($categories[$key]);
			}
		}

		return $categories;
	}

	protected $subcategories = array();
}
?>