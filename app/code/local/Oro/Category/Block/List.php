<?php
/**
 * Category List
 *
 * @category   Oro
 * @package    Oro_Category
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
 
class Oro_Category_Block_List extends Mage_Core_Block_Template
{
    protected $_topCategoryCollection;
    protected $_categoryTreeCollection;

    /**
     * Get category root id
     *
     * @return int
     */
    public function getRootCategoryId()
    {
        return Mage::app()->getStore()->getRootCategoryId();
    }

    /**
     * Get category children
     *
     * @param int $categoryId
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryChildren($categoryId)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect('name');
        if ($categoryId == $this->getRootCategoryId()) {
            $collection->addAttributeToSelect('thumbnail');
        }
        $collection
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('include_in_menu', 1)
            ->addAttributeToFilter('parent_id', $categoryId)
            ->setOrder('position', Varien_Db_Select::SQL_ASC);
        return $collection;
    }

    /**
     * Return children categories of root category
     *
     * @return array
     */
    public function getTopCategories()
    {
        if (!$this->_topCategoryCollection) {
            $rootCategoryId = $this->getRootCategoryId();
            /** @var  $collection Mage_Catalog_Model_Resource_Category_Collection */
            $collection = $this->getCategoryChildren($rootCategoryId);
            $categories = array();
            foreach ($collection as $key => $child) {
                $subcategories = $this->getCategoryChildren($child->getId());
                $child->setSubcategories($subcategories);
                $categories[] = $child;
            }
            $this->_topCategoryCollection = $categories;
        }
        return $this->_topCategoryCollection;
    }

    /**
     * Get Categories Tree
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryTree()
    {
        if (!$this->_categoryTreeCollection) {
            $rootCategoryId = $this->getRootCategoryId();
            $path = Mage_Catalog_Model_Category::TREE_ROOT_ID . '/' . $rootCategoryId . '/%';
            /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
            $collection = Mage::getModel('catalog/category')->getCollection();
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('path', array('like' => $path))
               // ->addAttributeToFilter('include_in_menu', 1)
                ->addAttributeToFilter('level', array('lteq' => 3))
                ->setOrder('level', Varien_Db_Select::SQL_ASC)
                ->setOrder('position', Varien_Db_Select::SQL_ASC);
            $this->_categoryTreeCollection = $collection;
        }
        return $this->_categoryTreeCollection;
    }

    /**
     * Get Categories list
     *
     * @return array
     */
    public function getListCategories()
    {
        $tree = $this->getCategoryTree();
        $categories = array();
        $subCategories = array();
        $level = 2;
        foreach ($tree as $category) {
            if ($category->getLevel() == $level) {
                $categories[$category->getId()] = $category;
            } elseif ($category->getLevel() == $level + 1) {
                $subCategories[$category->getParentId()][] = $category;
            }
        }
        foreach ($subCategories as $chKey => $child) {
            if (isset($categories[$chKey])) {
                $parent = $categories[$chKey];
                $parent->setSubcategories($subCategories[$chKey]);
                $categories[$chKey] = $parent;
            }
        }
        return $categories;
    }
}
