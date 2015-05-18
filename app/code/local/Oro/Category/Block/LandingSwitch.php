<?php
/**
 * Landing switcher
 *
 * @category   Oro
 * @package    Oro_Category
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
 
class Oro_Category_Block_LandingSwitch extends Mage_Core_Block_Template
{
    /**
     * Is landing page?
     *
     * @return bool
     */
    public function isLanding()
    {
        $category = Mage::registry('current_category');

        if ($category instanceof Mage_Catalog_Model_Category) {

            if ($category->getDisplayMode() == Mage_Catalog_Model_Category::DM_MIXED &&
                $this->getCmsIdentifier($category) == 'categorylanding' &&
                $this->isPaginationPage() == FALSE
            ) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get static block identifier
     *
     * @param Mage_Catalog_Model_Category $category
     *
     * @return string
     */
    protected function getCmsIdentifier(Mage_Catalog_Model_Category $category)
    {
        $cmsBlockId = intval($category->getLandingPage());

        if (is_integer($cmsBlockId) && $cmsBlockId > 0) {
            $cmsBlock = Mage::getModel('cms/block')->load($cmsBlockId);
            return $cmsBlock->getIdentifier();
        }

        return NULL;
    }

    /**
     * Is current page with pagination?
     *
     * @return bool
     */
    protected function isPaginationPage()
    {
        return is_numeric(Mage::app()->getRequest()->getParam('p'));
    }
}