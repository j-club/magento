<?php

class Magestore_Luckydraw_Model_Total_Creditmemo_Luckydraw extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo){
		$order = $creditmemo->getOrder();
		$baseDiscount = $order->getBaseLuckydrawDiscount();
		$discount = $order->getLuckydrawDiscount();
		
		if (floatval($baseDiscount)){
			$creditmemo->setBaseLuckydrawDiscount($baseDiscount);
			$creditmemo->setLuckydrawDiscount($discount);
			
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseDiscount);
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $discount);
		}
		return $this;
	}
}