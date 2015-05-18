<?php

class Magestore_Membership_Model_Observer {

	/*
		Create member or add new member package after the order is completed
		This function is called when the event sales_order_save_after occur.
	*/
	public function sales_order_save_after($observer){
		
		$orderStateActivePackage = Mage::getStoreConfig('membership/general/active_package_when_state_order');
		$orderStateUpdatePackage = Mage::getStoreConfig('membership/general/update_package_when_state_order');
		
		//get the current order
		$order = $observer->getEvent()->getOrder();
		//get the customer id in the order
		$customerId = $order->getCustomerId();
		
		//check if the customer is enabled or not.
		if(!Mage::helper('membership')->getMemberStatus($customerId))//disabled
			return;
			
		$helper = Mage::helper('membership');
		foreach ($order->getAllVisibleItems() as $item) {
			$productId = $item->getProductId();
			$packageId = $helper->getPackageFromMembershipProduct($productId)->getId();	
			$memberId = $helper->addMember($customerId);
			if($packageId){
				$helper->addPaymentHistory($memberId, $packageId, $order->getId());
			}
			//in this case, customer buy a new package. 
			if($order->getStatus() == $orderStateActivePackage){
				if($packageId){
					$helper->addPackageToMember($memberId, $packageId, $order->getId());
				}				
			}
			
			//in this case, customer buy a normal product of a membership package.
			if($order->getStatus() == $orderStateUpdatePackage){
				if($helper->isProductDiscount($customerId, $productId)){
					
					$memberPackages = $helper->getMemberpackage($customerId, $productId);
					//find a package with min final price
					$minPrice = -1;
					foreach($memberPackages as $item)
					{					
						$package = Mage::getModel('membership/package')->load($item->getPackageId());						
						$membershipPrice = $helper->getMembershipPrice($productId, $package);
						if($minPrice == -1)
						{
							$minPrice = $membershipPrice;
							$memberPackage = $item;
						}
						else if($membershipPrice <= $minPrice)						
						{
							$minPrice = $membershipPrice;
							$memberPackage = $item;
						}
					}//end foreach($memberPackages as $item)	
					$saved = Mage::getModel('catalog/product')->load($productId)->getPrice() -  $minPrice;
					$memberPackage->setBoughtItemTotal($memberPackage->getBoughtItemTotal()+1)
								->setSavedTotal($memberPackage->getSavedTotal()+$saved)
								->setStatus(1);
					try{
						$memberPackage->save();
					}catch(Exception $e){
					}
				}	
			}
		}		
	}//sales_order_save_after($observer)
	
	public function catalog_product_get_final_price($observer)
	{	
		$finalPrices = array();
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		if(!$customerId)
			return;
			
		$product = $observer['product'];
		
		if(!Mage::helper('membership')->getMemberStatus($customerId))//disabled
			return;
		
		if(!Mage::helper('membership')->isProductDiscount($customerId, $product->getId()))
			return;
		
		$memberPackages = Mage::helper('membership')->getMemberpackage($customerId, $product->getId());
		foreach($memberPackages as $memberPackage)
		{
			$package = Mage::getModel('membership/package')->load($memberPackage->getPackageId());
		
			$finalPrices[] = Mage::helper('membership')->getMembershipPrice($product->getId(), $package);
		}	
		
		sort($finalPrices, SORT_NUMERIC);
		$product->setData('final_price', $finalPrices[0]);
	}
	
	public function customer_save_after($observer)
	{
		$customer = $observer['customer'];
		$member = Mage::getModel('membership/member')->getCollection()
					->addFieldToFilter('customer_id', $customer->getId())
					->getFirstItem();
		if(!$member->getId())
			return;
		$member->setMemberName($customer->getName())
			->setMemberEmail($customer->getEmail());
		try{
			$member->save();
		}catch(Exception $e){
		}
	}
	
	public function autopayment(){
		
	}
	
	
	/*
		this function runs on cron job.
	*/
	public function noticeStatusPackage(){
		
		//update member package status
		$collection = Mage::getModel('membership/memberpackage')->getCollection()
					->addFieldToFilter('end_time', array('datetime'=>true, 'to'=>now()));
		if(count($collection)){
			foreach($collection as $memberpackage)
			{
				$memberpackage->updatePackageStatus();
				if($memberpackage->getStatus() ==Magestore_Membership_Model_Memberpackage::STATUS_WARNING)
				{
					Mage::helper('membership/email')->sendEmailNotifyRenewPackage($memberpackage);
				}
			}		
		}
	}//noticeStatusPackage
}