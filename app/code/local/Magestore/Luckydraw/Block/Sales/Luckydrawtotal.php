<?php

class Magestore_Luckydraw_Block_Sales_Luckydrawtotal extends Mage_Sales_Block_Order_Totals
{
	public function getLuckydrawDiscount(){
		$order = $this->getOrder();
		return $order->getLuckydrawDiscount();
	}
	
	public function getBaseLuckydrawDiscount(){
		$order = $this->getOrder();
		return $order->getBaseLuckydrawDiscount();
	}
	
	public function initTotals(){
		$amount = $this->getLuckydrawDiscount();
		if(floatval($amount)){
			$total = new Varien_Object();
			$total->setCode('luckydraw_discount');
			$total->setValue($amount);
			$total->setBaseValue($this->getBaseLuckydrawDiscount());
			$total->setLabel('Lucky Draw Discount');
			$parent = $this->getParentBlock();
			$parent->addTotal($total,'subtotal');
		}
	}
	
	public function getOrder(){
		if(!$this->hasData('order')){
			$order = $this->getParentBlock()->getOrder();
			$this->setData('order',$order);
		}
		return $this->getData('order');
	}
}