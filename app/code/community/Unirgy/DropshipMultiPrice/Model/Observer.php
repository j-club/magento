<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipMultiPrice
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipMultiPrice_Model_Observer
{
    public function catalog_product_type_prepare_full_options($observer)
    {
        $this->_catalog_product_type_prepare_cart_options($observer);
    }
    public function catalog_product_type_prepare_lite_options($observer)
    {
        $this->_catalog_product_type_prepare_cart_options($observer);
    }
    public function catalog_product_type_prepare_cart_options($observer)
    {
        $this->_catalog_product_type_prepare_cart_options($observer);
    }
    protected function _catalog_product_type_prepare_cart_options($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $buyRequest = $observer->getBuyRequest();
        $product = $observer->getProduct();
        Mage::helper('udmultiprice')->addBRVendorOption($product, $buyRequest);
    }
    public function udropship_quote_item_setUdropshipVendor($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $item = $observer->getItem();
        Mage::helper('udmultiprice')->addVendorOption($item);
    }
    public function catalog_product_get_final_price($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $product = $observer->getProduct();
        if (!in_array($product->getTypeId(), array('simple','configurable','virtual'))) return;
        $qty     = $observer->getQty();
        if (Mage::helper('udmultiprice')->canUseVendorPrice($product)) {
            if (!$product->getUdmultiPriceUsedVendorPriceFlag()) {
                Mage::helper('udmultiprice')->useVendorPrice($product);
                $product->setUdmultiPriceUsedVendorPriceFlag(true);
                try {
                    $product->getFinalPrice($qty);
                } catch (Unirgy_DropshipMultiPrice_Exception $e) {}
            } else {
                Mage::helper('udmultiprice')->revertVendorPrice($product);
                $product->unsUdmultiPriceUsedVendorPriceFlag();
                throw new Unirgy_DropshipMultiPrice_Exception();
            }
        }
    }
    public function sales_quote_product_add_after($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $items = $observer->getItems();
        foreach ($items as $item) {
            if (!$item->getParentItem()) {
                Mage::helper('udmultiprice')->addBRVendorOption($item);
            }
        }
    }
    public function sales_quote_item_set_product($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $item = $observer->getEvent()->getQuoteItem();
        Mage::helper('udmultiprice')->addBRVendorOption($item);
        Mage::helper('udmultiprice')->addVendorOption($item);
    }
    public function sales_convert_quote_item_to_order_item($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $qItem = $observer->getEvent()->getItem();
        $oItem = $observer->getEvent()->getOrderItem();
        if ($qItem instanceof Mage_Sales_Model_Quote_Address_Item) {
            $qItem = $qItem->getQuoteItem();
        }
        $oItem->setProduct($qItem->getProduct());
        Mage::helper('udmultiprice')->addVendorOption($oItem);
    }

    public function catalog_product_collection_apply_limitations_after($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        $select = $observer->getCollection()->getSelect();
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['price_index'])) {
            $fromPart = $select->getPart(Zend_Db_Select::COLUMNS);
            $alreadyAdded = false;
            foreach ($fromPart as $columnEntry) {
                list($correlationName, $column, $alias) = $columnEntry;
                if ('udmp_new_min_price' == $alias) {
                    $alreadyAdded = true;
                    break;
                }
            }
            if (!$alreadyAdded) {
                $canStates = Mage::getSingleton('udmultiprice/source')
                    ->setPath('vendor_product_state_canonic')
                    ->toOptionHash();
                $columns = array();
                foreach ($canStates as $csKey=>$csLbl) {
                    foreach (array('_min_price','_max_price','_cnt') as $csSuf) {
                        $columns['udmp_'.$csKey.$csSuf] = 'price_index.udmp_'.$csKey.$csSuf;
                    }
                }
                $select->columns($columns);
            }
        }
    }

    public function controller_action_layout_load_before($observer)
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        if ($observer->getAction()
            && $observer->getAction()->getFullActionName()=='catalog_product_view'
            && ($product = Mage::registry('current_product'))
            && in_array($product->getTypeId(), array('simple','configurable','virtual'))
        ) {
            if (Mage::getStoreConfigFlag('udprod/general/product_info_tabbed')) {
                $observer->getAction()->getLayout()->getUpdate()->addHandle('udmultiprice_catalog_product_view_tabbed');
            } else {
                $observer->getAction()->getLayout()->getUpdate()->addHandle('udmultiprice_catalog_product_view');
            }
        } elseif ($observer->getAction()
            && in_array($observer->getAction()->getFullActionName(), array('catalog_category_view'))
        ) {
            $observer->getAction()->getLayout()->getUpdate()->addHandle('_udmp_change_product_list_tpl');
        } elseif ($observer->getAction()
            && in_array($observer->getAction()->getFullActionName(), array('catalogsearch_result_index','catalogsearch_advanced_result'))
        ) {
            $observer->getAction()->getLayout()->getUpdate()->addHandle('_udmp_change_product_list_searchtpl');
        }
    }

    public function controller_front_init_before($observer)
    {
        $this->_initConfigRewrites();
    }

    public function udropship_init_config_rewrites()
    {
        $this->_initConfigRewrites();
    }
    protected function _initConfigRewrites()
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        if (Mage::helper('udropship')->isEE()
            && Mage::helper('udropship')->compareMageVer('1.8.0.0', '1.13.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_default', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Default');
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_grouped', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Grouped');
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_configurable', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Configurable');
            Mage::getConfig()->setNode('global/models/downloadable_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Downloadable');
            Mage::getConfig()->setNode('global/models/bundle_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Bundle');
            Mage::getConfig()->setNode('global/models/enterprise_giftcard_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_EE11300_GiftCard');

        } elseif (
            Mage::helper('udropship')->compareMageVer('1.7.0.0', '1.12.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_default', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Default');
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_grouped', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Grouped');
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_configurable', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Configurable');
            Mage::getConfig()->setNode('global/models/downloadable_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Downloadable');
            Mage::getConfig()->setNode('global/models/bundle_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Bundle');
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.6.0.0', '1.11.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_default', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1600_Default');
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_grouped', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1600_Grouped');
            Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/product_indexer_price_configurable', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1600_Configurable');
            Mage::getConfig()->setNode('global/models/downloadable_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1600_Downloadable');
            Mage::getConfig()->setNode('global/models/bundle_resource/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1600_Bundle');
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.4.1.0', '1.8.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/product_indexer_price_default', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1410_Default');
            Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/product_indexer_price_grouped', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1410_Grouped');
            Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/product_indexer_price_configurable', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1410_Configurable');
            Mage::getConfig()->setNode('global/models/downloadable_mysql4/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1410_Downloadable');
            Mage::getConfig()->setNode('global/models/bundle_mysql4/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1410_Bundle');
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.4.2.0', '1.9.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/product_indexer_price_default', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1420_Default');
            Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/product_indexer_price_grouped', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1420_Grouped');
            Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/product_indexer_price_configurable', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1420_Configurable');
            Mage::getConfig()->setNode('global/models/downloadable_mysql4/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1420_Downloadable');
            Mage::getConfig()->setNode('global/models/bundle_mysql4/rewrite/indexer_price', 'Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1420_Bundle');
        }
    }
}
