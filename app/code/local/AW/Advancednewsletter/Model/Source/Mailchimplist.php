<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento enterprise edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento enterprise edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancednewsletter
 * @version    2.3.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_AdvancedNewsletter_Model_Source_Mailchimplist {

    public function toOptionArray() {
        $store = null;
        if (Mage::app()->getRequest()->getParam('store')) {
            $store = Mage::app()->getRequest()->getParam('store');
        } else if (Mage::app()->getRequest()->getParam('website')) {
            $store = Mage::app()->getWebsite(Mage::app()->getRequest()->getParam('website'))->getDefaultStore()->getId();
        }
        
        if(!Mage::getStoreConfig('advancednewsletter/mailchimpconfig/mailchimpenabled', $store)) {
            return array();
        }

        $xmlrpcurl = Mage::getStoreConfig('advancednewsletter/mailchimpconfig/xmlrpc', $store);
        $apikey = Mage::getStoreConfig('advancednewsletter/mailchimpconfig/apikey', $store);

        if (!$apikey || !$xmlrpcurl)
            return array();

        $lists = array();
        try {
            $arr = explode('-', $apikey, 2);
            $dc = (isset($arr[1])) ? $arr[1] : 'us1';

            list($aux, $host) = explode('http://', $xmlrpcurl);
            $api_host = 'http://' . $dc . '.' . $host;

            $client = new Zend_XmlRpc_Client($api_host);

            /*
             *   Mailchimp API 1.3
             *   lists(string apikey, [array filters], [int start], [int limit])
             *   
             */

            $lists = $client->call('lists', $apikey);
        } catch (Exception $e) {
            /*
             * #test connection button is responsible now for connection check
             */
            return array();
        }

        if (is_array($lists) && isset($lists['data']) && count($lists['data'])) {

            $options = array();
            $options[] = array(
                'label' => 'Select a list..',
                'value' => '',
            );

            foreach ($lists['data'] as $list) {
                $options[] = array(
                    'value' => $list['id'],
                    'label' => $list['name']
                );
            }
            return $options;
        }
        return array();
    }

}