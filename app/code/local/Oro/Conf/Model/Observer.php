<?php
/**
 * Rewrite Amasty color swatches observer to make it work with Unirgy
 *
 * @category   MageCore
 * @package    Module_Name
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_Conf_Model_Observer extends Amasty_Conf_Model_Observer
{
    public function onListBlockHtmlBefore($observer)//core_block_abstract_to_html_after
    {
        if (($observer->getBlock() instanceof Mage_Catalog_Block_Product_List) && Mage::getStoreConfig('amconf/list/enable_list')) {
            $html = $observer->getTransport()->getHtml();

            $collection = $observer->getBlock()->getLoadedProductCollection();
            foreach ($collection as $_product) {
                $productId =  $_product->getId();
                if($_product->isSaleable() && $_product->isConfigurable()){
                    $addButton = (Mage::getStoreConfig('amconf/product_image_size/have_button'))?"(.*?)/button>":"";
                    $template = '@(ud-product-price-'.$productId.'">(.*?)div>)' . $addButton . '@s';
                    preg_match_all($template, $html, $res);
                    if(!$res[0]) {
                        $template = '@(price-including-tax-'.$productId.'">(.*?)div>)' . $addButton . '@s';
                        preg_match_all($template, $html, $res);
                        if(!$res[0]){
                            $template = '@(price-excluding-tax-'.$productId.'">(.*?)div>)' . $addButton . '@s';
                            preg_match_all($template, $html, $res);
                        }
                    }

                    if($res[0]) {
                        $replace =  Mage::helper('amconf')->getHtmlBlock($_product, $res[1][0]);
                        if(strpos($html, $replace) === false) {
                            $html= str_replace($res[0][0], $replace, $html);
                        }
                    }
                }
            }
            $observer->getTransport()->setHtml($html);
        }
    }
}