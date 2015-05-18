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
 * @package    Unirgy_DropshipSellYours
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipSellYours_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function processFormVar($var, $decimal=false)
    {
        return ''===$var || null === $var ? '' : ($decimal ? 1*$var : $var);
    }

    public function hookVendorCustomer($vendor, $customer)
    {
        if ($vendor && $vendor->getId() && $customer && $customer->getId()) {
            if ($customer->getVendorId() != $vendor->getId()) {
                $customer->setVendorId($vendor->getId());
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($customer, array('vendor_id'));
            }
            if ($vendor->getCustomerId() != $customer->getId()) {
                $vendor->setCustomerId($customer->getId());
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($vendor, array('customer_id'));
            }
        }
        return $this;
    }
    public function saveSellYoursFormData($data=null, $id=null)
    {
        $formData = Mage::getSingleton('udsell/session')->getSellYoursFormData();
        if (!is_array($formData)) {
            $formData = array();
        }
        $data = !is_null($data) ? $data : Mage::app()->getRequest()->getPost();
        $id = !is_null($id) ? $id : Mage::app()->getRequest()->getParam('id');
        $formData[$id] = $data;
        Mage::getSingleton('udsell/session')->setSellYoursFormData($formData);
    }

    public function fetchSellYoursFormData($id=null)
    {
        $formData = Mage::getSingleton('udsell/session')->getSellYoursFormData();
        if (!is_array($formData)) {
            $formData = array();
        }
        $id = !is_null($id) ? $id : Mage::app()->getRequest()->getParam('id');
        $result = false;
        if (isset($formData[$id]) && is_array($formData[$id])) {
            $result = $formData[$id];
            unset($formData[$id]);
            if (empty($formData)) {
                Mage::getSingleton('udsell/session')->getSellYoursFormData(true);
            } else {
                Mage::getSingleton('udsell/session')->setSellYoursFormData($formData);
            }
        }
        return $result;
    }

    public function processSellRequest($vendor, $product, $data)
    {
        Mage::helper('udsell/protected')->processSellRequest($vendor, $product, $data);
        return $this;
    }

    public function getSRAllowedFields()
    {
        return array('vendor_price', 'vendor_title', 'stock_qty', 'shipping_price', 'state', 'freeshipping', 'state_descr', 'vendor_sku');
    }

    public function getCustomerVendorPortalUrl()
    {
        return Mage::getStoreConfigFlag('udropship/customer/sync_customer_vendor')
            ? 'udsell/index/vendor'
            : 'udropship';
    }

    public function getSellYoursFieldsConfig()
    {
        $editFields = array();
        if (Mage::helper('udropship')->isUdmultiActive()) {
            $editFields['udmulti']['label'] = Mage::helper('udmulti')->__('Vendor Specific Fields');
            $editFields['udmulti']['values']  = Mage::helper('udprod')->getVendorEditFieldsConfig();
        }
        return $editFields;
    }

    public function getSellUrl($_product)
    {
        $params = array('id'=>$_product->getId());
        if ($curCat = Mage::registry('current_category')) {
            $params['c'] = $curCat->getId();
        }
        return Mage::getModel('core/url')->getUrl('udsell/index/sell', $params);
    }

}