<?php
/**
 * Resource Category
 *
 * @category   Oro
 * @package    Oro_Category
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Category_Model_Resource_Category extends Mage_Catalog_Model_Resource_Category
{
    /**
     * Prepare base collection setup for get categories list
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getChildrenCategoriesBase($category)
    {
        $collection = $category->getCollection();
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToSelect('thumbnail')
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->joinUrlRewrite();

        return $collection;
    }
}