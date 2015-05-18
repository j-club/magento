<?php
/**
 * Helper
 *
 * @category   Oro
 * @package    Oro_EarnPoints
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */ 
class Oro_EarnPoints_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get earning points block html
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getRateHtml(Mage_Catalog_Model_Product $product)
    {
        return Mage::app()->getLayout()->createBlock('oro_earnpoints/rate')->setProduct($product)->toHtml();
    }

    /**
     * Retrieve points earned for order
     *
     * @param Mage_Sales_Model_Order $order
     * @return int
     */
    public function getPointsForOrder($order)
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $collection = Mage::getModel('enterprise_reward/reward_history')->getCollection()
            ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
            ->addWebsiteFilter($websiteId)
            ->setExpiryConfig(Mage::helper('enterprise_reward')->getExpiryConfig())
            ->addExpirationDate($websiteId)
            ->skipExpiredDuplicates()
            ->addFieldToFilter('action', Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER_EXTRA)
            ->addFieldToFilter('entity', $order->getId())
            ->setDefaultOrder();

        if(0 == $collection->getSize()){
            return 0;
        }

        $count = 0;
        foreach($collection as $item){
            $count += $item->getPointsDelta();
        }

        return $count;

    }
  //Added by Santosh Kumar Start
    public function getPointsByProduct(Mage_Catalog_Model_Product $product)
    {
        $price = $product->getFinalPrice();
        return $this->_calculatePoints($price);
    }
   /**
     * @param float $price
     * @return int
     */
    protected function _calculatePoints($price)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return Mage::getModel('enterprise_reward/reward')
            ->setCustomer($customer)
            ->getRateToPoints()
            ->calculateToPoints($price);
    }
    //Added by Santosh Kumar End      
}