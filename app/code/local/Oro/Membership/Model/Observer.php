<?php
/**
 * Observer
 *
 * @category   Oro
 * @package    Oro_Membership
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Membership_Model_Observer extends Magestore_Membership_Model_Observer
{

    /*
		Create member or add new member package after the order is completed
		This function is called when the event sales_order_save_after occur.
	*/
    public function sales_order_save_after($observer)
    {
        $orderStateActivePackage = Mage::getStoreConfig('membership/general/active_package_when_state_order');
        $orderStateUpdatePackage = Mage::getStoreConfig('membership/general/update_package_when_state_order');

        //get the current order
        $order = $observer->getEvent()->getOrder();
        //get the customer id in the order
        $customerId = $order->getCustomerId();

        //check if the customer is enabled or not.
        if (!Mage::helper('membership')->getMemberStatus($customerId)) {
            return;
        }

        $helper = Mage::helper('membership');
        foreach ($order->getAllVisibleItems() as $item) {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);
            $productId = $product->getId();
            $packageId = $helper->getPackageFromMembershipProduct($productId)->getId();
            $memberId = $helper->addMember($customerId);
            if ($packageId) {
                $helper->addPaymentHistory($memberId, $packageId, $order->getId());
            }
            //in this case, customer buy a new package.
            if ($order->getStatus() == $orderStateActivePackage) {
                if ($packageId) {
                    $helper->addPackageToMember($memberId, $packageId, $order->getId());
                }
            }

            //in this case, customer buy a normal product of a membership package.
            if ($order->getStatus() == $orderStateUpdatePackage) {
                if ($helper->isProductDiscount($customerId, $productId)) {

                    $memberPackages = $helper->getMemberpackage($customerId, $productId);
                    //find a package with min final price
                    $minPrice = -1;
                    foreach ($memberPackages as $item) {
                        $package = Mage::getModel('membership/package')->load($item->getPackageId());
                        $membershipPrice = $helper->getMembershipPrice($product, $package);
                        if ($minPrice == -1) {
                            $minPrice = $membershipPrice;
                            $memberPackage = $item;
                        } else if ($membershipPrice <= $minPrice) {
                            $minPrice = $membershipPrice;
                            $memberPackage = $item;
                        }
                    }//end foreach($memberPackages as $item)
                    $saved = $product->getPrice() -  $minPrice;
                    $memberPackage->setBoughtItemTotal($memberPackage->getBoughtItemTotal()+1)
                        ->setSavedTotal($memberPackage->getSavedTotal()+$saved)
                        ->setStatus(1);
                    try {
                        $memberPackage->save();
                    } catch(Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }

    public function catalog_product_get_final_price($observer)
    {
        $finalPrices = array();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return;
        }

        $product = $observer['product'];

        if (!Mage::helper('membership')->getMemberStatus($customerId)) {
            return;
        }

        if (!Mage::helper('membership')->isProductDiscount($customerId, $product->getId())) {
            return;
        }

        $memberPackages = Mage::helper('membership')->getMemberpackage($customerId, $product->getId());
        foreach ($memberPackages as $memberPackage) {
            $package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());

            $finalPrices[] = Mage::helper('membership')->getMembershipPrice($product, $package);
        }

        sort($finalPrices, SORT_NUMERIC);
        $product->setData('final_price', $finalPrices[0]);
    }

}