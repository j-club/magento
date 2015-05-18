<?php

class Unirgy_DropshipSellYours_Block_ProductView extends Mage_Catalog_Block_Product_View
{
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $product = $this->getProduct();
            $sess = Mage::getSingleton('udropship/session');
            $searchUrlKey = $sess->getData('udsell_search_type') ? 'mysellSearch' : 'sellSearch';
            if ($sess->getData('udsell_search_type')) {
                $breadcrumbsBlock->addCrumb('sellyours', array(
                    'label'=>Mage::helper('catalog')->__('My Sell List'),
                    'title'=>Mage::helper('catalog')->__('My Sell List'),
                    'link'=>$this->getUrl('udsell/index/mysellSearch')
                ));
            } else {
                $breadcrumbsBlock->addCrumb('sellyours', array(
                    'label'=>Mage::helper('catalog')->__('Sell Yours'),
                    'title'=>Mage::helper('catalog')->__('Sell Yours'),
                    'link'=>$this->getUrl('udsell/index/sellSearch')
                ));
            }
            if (Mage::registry('current_category')) {
                $cat = Mage::registry('current_category');
                $pathIds = explode(',', $cat->getPathInStore());
                array_shift($pathIds);
                $cats = Mage::helper('udropship/catalog')->getCategoriesCollection($pathIds);
                foreach ($cats as $c) {
                    $breadcrumbsBlock->addCrumb('sellyours_cat'.$c->getId(), array(
                        'label'=>$c->getName(),
                        'title'=>$c->getName(),
                        'link'=>$this->getUrl('udsell/index/'.$searchUrlKey, array('_current'=>true, 'c'=>$c->getId()))
                    ));
                }
                $breadcrumbsBlock->addCrumb('sellyours_cat'.$cat->getId(), array(
                    'label'=>$cat->getName(),
                    'title'=>$cat->getName(),
                    'link'=>$this->getUrl('udsell/index/'.$searchUrlKey, array('_current'=>true, 'c'=>$cat->getId()))
                ));
            }
            $breadcrumbsBlock->addCrumb('sellyours_query', array(
                'label'=>htmlspecialchars($product->getName()),
                'title'=>htmlspecialchars($product->getName()),
                'link'=>$this->getUrl('*/*/*', array('_current'=>true))
            ));
        }

        return Mage_Catalog_Block_Product_Abstract::_prepareLayout();
    }
}