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


class AW_Advancednewsletter_Model_Observer extends Mage_Core_Block_Abstract {

    /**
     * updating names for subscriber after customer names update
     * @param Mage_Customer_Model_Customer $customer 
     */
    protected function updateNames($customer) {
        $subscriber = Mage::getModel('advancednewsletter/subscriber')->load($customer->getEntityId(), 'customer_id');

        if (!$subscriber->getId()) {
            return;
        }

        if ($subscriber->getData('last_name') != $customer->getLastname() || $subscriber->getData('first_name') != $customer->getFirstname()) {
            $subscriber->forceWrite(
                    array(
                        'first_name' => $customer->getFirstname(),
                        'last_name' => $customer->getLastname()
                    )
            );
        }
        if ($subscriber->getEmail() != $customer->getEmail()) {
            $duplicate = Mage::getModel('advancednewsletter/subscriber')->load($customer->getEmail(), 'email');
            if (!$duplicate->getId()) {
                $subscriber->forceWrite(
                        array(
                            'email' => $customer->getEmail(),
                        )
                );
            }
        }
    }

    /**
     * Subscribe customer after registration
     * @param Mage_Customer_Model_Customer $customer 
     */
    protected function subscribeAfterRegistration($customer) {

        if (Mage::helper('advancednewsletter')->showSegmentsAtCustomerRegistration()) {
            $subscribedSegments = Mage::app()->getRequest()->getParam('segments_select');
            if (!empty($subscribedSegments)) {
                $customer->setIsSubscribed(true);
                $params = array(
                    'first_name' => $customer->getFirstname(),
                    'last_name' => $customer->getLastname()
                );
                try {
                    Mage::getModel('advancednewsletter/subscriber')->setCustomer($customer)->subscribe($customer->getEmail(), $subscribedSegments);
                } catch (Exception $ex) {
                    
                }
            }
        } else if ($customer->getIsSubscribed()) {
            $segmentsToSubscribe = array();
            $params = array(
                'first_name' => $customer->getFirstname(),
                'last_name' => $customer->getLastname()
            );
            $segmentsCollection = Mage::getModel('advancednewsletter/segment')
                    ->getCollection()
                    ->addFieldToFilter('frontend_visibility', array('eq' => array(1)))
                    ->addDefaultStoreFilter(Mage::app()->getStore()->getId());
            foreach ($segmentsCollection as $segment) {
                $segmentsToSubscribe[] = $segment->getCode();
            }
            try {
                Mage::getModel('advancednewsletter/subscriber')->setCustomer($customer)->subscribe($customer->getEmail(), $segmentsToSubscribe);
            } catch (Exception $ex) {
                
            }
        }
    }

    /**
     * Change customers segments after changing them at customer edit > newsletter section
     * @param Mage_Customer_Model_Customer $customer 
     */
    protected function updateSegmentsFromAdmin($customer) {
        if (Mage::app()->getRequest()->getParam('an_segments_changed')) {
            $segments = $this->getRequest()->getParam('segments_codes') ? $this->getRequest()->getParam('segments_codes') : array();
            $subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($customer->getEmail());
            if ($subscriber->getId()) {
                if (empty($segments))
                    $subscriber->unsubscribeFromAll();
                else
                    $subscriber->forceWrite(array('segments_codes' => $segments, 'status' => AW_Advancednewsletter_Model_Subscriber::STATUS_SUBSCRIBED));
            }
            else {
                if (!empty($segments))
                    $subscriber->setCustomer($customer)->subscribe($customer->getEmail(), $segments);
            }
        }
    }

    /**
     * Save customer observer
     * @param mixed $observer
     */
    public function saveCustomer($observer) {
        //TODO: Optimize observing may be. subscribeAfterRegistration function espessially
        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof Mage_Customer_Model_Customer)) {
            $this->updateNames($customer);
            $this->subscribeAfterRegistration($customer);
            $this->updateSegmentsFromAdmin($customer);
        }
    }

    /**
     * Observing order status changes to apply automanagement rules
     * @param mixed $observer 
     */
    public function orderStatusChanged($observer) {
        $order = $observer->getEvent()->getOrder();
        $order_status = $order->getStatus();
        if ($order_status) {

            /* checkout onepage subscription */
            if (Mage::app()->getRequest()->getParam('aw_an_onepage')) {
                $this->_subscribeOnOnepageCheckout($observer);
            }

            $customer_email = $order->getCustomerEmail();
            $store_id = $order->getStoreId();

            $sku = array();
            $categoryids = array();
            $product = Mage::getModel('catalog/product');

            foreach ($order->getAllItems() as $item) {
                $product->load($item->getProductId());

                $productCategoriesWithParents = $product->getCategoryIds();
                foreach ($productCategoriesWithParents as $productCategory) {
                    $productCategoriesWithParents = array_merge($productCategoriesWithParents, Mage::getModel('catalog/category')->load($productCategory)->getParentIds());
                }
                $categoryids[] = array_unique($productCategoriesWithParents);
                $sku[] = $item->getSku();
            }

            $allcategories = array();
            foreach ($categoryids as $categoryid) {
                foreach ($categoryid as $item) {
                    $flag = false;
                    foreach ($allcategories as $allcategory) {
                        if ($allcategory == $item) {
                            $flag = true;
                        }
                    }
                    if (!$flag) {
                        $allcategories[] = $item;
                    }
                }
            }


            $to_validate = new Varien_Object();
            $to_validate->setData('categories', $allcategories);
            $to_validate->setData('sku', $sku);
            $to_validate->setData('store', $store_id);
            $to_validate->setData('order_status', $order_status);
            $to_validate->setData('customer_email', $customer_email);
            $to_validate->setData('order', $order);

            Mage::getModel('advancednewsletter/automanagement')->checkRule($to_validate);
        }
    }

    /*
     *   Subscribe on OnepageCheckout (called from $this->orderStatusChanged())
     * 
     */

    protected function _subscribeOnOnepageCheckout($observer) {
        try {

            $segments = Mage::app()->getRequest()->getParam('segments_select'); // get subscribe form data
            $email = $this->helper('advancednewsletter')->getCheckoutStoredEmail();  // get email to subscribe

            $subscriber = Mage::getSingleton('advancednewsletter/subscriber')->loadByEmail($email);

            $subscriberId = $subscriber->getData('id');

            if ($segments == '') {
                if ($subscriberId) {
                    // unsubscribeFromAll On Checkout
                    $checkoutSegments = $this->helper('advancednewsletter')->getSegmentsCodesOnCheckout();

                    switch ($subscriber->getStatus()) {
                        case AW_Advancednewsletter_Model_Subscriber::STATUS_NOTACTIVE:

                            $subscriber->removeSegments($checkoutSegments);
                            if (!$subscriber->getSegmentsCodes()) {
                                $subscriber->setStatus(AW_Advancednewsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                            }
                            $subscriber->save();

                            break;

                        case AW_Advancednewsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                            $subscriber->unsubscribe($checkoutSegments);

                            break;

                        case AW_Advancednewsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                            // nothing to do
                            break;

                        default:
                            break;
                    }
                } else {
                    // nothing to do
                }
            } else {

                if (!is_array($segments)) {
                    $segments = explode(',', $segments);
                }

                if ($subscriberId) {
                    // unsubscribe from all segments visible on checkout
                    // subscribe to selected segments

                    $checkoutSegments = $this->helper('advancednewsletter')->getSegmentsCodesOnCheckout();

                    $unsubsSegments = array_diff($checkoutSegments, $segments);
                    if ($unsubsSegments) {
                        $subscriber->removeSegments($unsubsSegments);
                    }
                    $subscriber->addSegments($segments);
                    $subscriber->setStatus(AW_Advancednewsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                    $subscriber->save();
                } else {
                    // new subscriber
                    $orderData = $observer->getOrder()->getData();

                    $params = array(
                        'store_id' => Mage::app()->getStore()->getId(),
                        'first_name' => isset($orderData['customer_firstname']) ? $orderData['customer_firstname'] : '',
                        'last_name' => isset($orderData['customer_lastname']) ? $orderData['customer_lastname'] : '',
                    );

                    // subscribe to selected
                    $subscriber->subscribe($email, $segments, $params);
                }
            }
        } catch (Exception $exc) {
            Mage::helper('awcore/logger')->log($this, 'OnepageCheckout Segments update failed - ' . $exc->getMessage());
        }
    }

}

