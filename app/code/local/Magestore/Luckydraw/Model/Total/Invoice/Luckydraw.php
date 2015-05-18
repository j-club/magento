<?php

class Magestore_Luckydraw_Model_Total_Invoice_Luckydraw extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Invoice $invoice){
		$order = $invoice->getOrder();
		$baseDiscount = $order->getBaseLuckydrawDiscount();
		$discount = $order->getLuckydrawDiscount();
		
		if (floatval($baseDiscount)){
			$invoice->setBaseLuckydrawDiscount($baseDiscount);
			$invoice->setLuckydrawDiscount($discount);
			
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseDiscount);
			$invoice->setGrandTotal($invoice->getGrandTotal() + $discount);
		}
		return $this;
	}
}