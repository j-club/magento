<?php
/**
 *
 * @category   Oro
 * @package    Oro_ProductQuickView
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
require_once Mage::getModuleDir('controllers', 'Mage_Catalog').'/CategoryController.php';

class Oro_ProductQuickView_CategoryController extends Mage_Catalog_CategoryController
{
    /**
     * Category view action
     */
    public function viewAction()
    {
        if ($category = $this->_initCatagory()) {
            $this->_prepareCategoryLayout($category);

            if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('p')) {
                echo $this->getLayout()->getBlock('product_list')->toHtml();
                exit;

            }

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                    ->addBodyClass('category-' . $category->getUrlKey());
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');

            $this->renderLayout();
        }
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @return Oro_ProductQuickView_CategoryController
     */
    protected function _prepareCategoryLayout($category)
    {
        $design = Mage::getSingleton('catalog/design');
        $settings = $design->getDesignSettings($category);

        // apply custom design
        if ($settings->getCustomDesign()) {
            $design->applyCustomDesign($settings->getCustomDesign());
        }

        Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');

        if (!$category->hasChildren()) {
            $update->addHandle('catalog_category_layered_nochildren');
        }

        $this->addActionLayoutHandles();
        $update->addHandle($category->getLayoutUpdateHandle());
        $update->addHandle('CATEGORY_' . $category->getId());
        $this->loadLayoutUpdates();

        // apply custom layout update once layout is loaded
        if ($layoutUpdates = $settings->getLayoutUpdates()) {
            if (is_array($layoutUpdates)) {
                foreach($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $this->generateLayoutXml()->generateLayoutBlocks();

        // apply custom layout (page) template once the blocks are generated
        if ($settings->getPageLayout()) {
            $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
        }

        return $this;
    }
}
