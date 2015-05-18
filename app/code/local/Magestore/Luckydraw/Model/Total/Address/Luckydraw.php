<?php

class Magestore_Luckydraw_Model_Total_Address_Luckydraw extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function __construct(){
		$this->setCode('luckydraw');
	}
	
	/**
	 * get Config Helper
	 *
	 * @return Magestore_Luckydraw_Helper_Data
	 */
	protected function _getConfigHelper(){
		return Mage::helper('luckydraw');
	}
	
	public function collect(Mage_Sales_Model_Quote_Address $address){
		if (!$this->_getConfigHelper()->getGeneralConfig('enable',$address->getQuote()->getStoreId())) return $this;
		$customer = $address->getQuote()->getCustomer();
		$baseGrandTotal = $address->getBaseGrandTotal();
		if (!$customer || !$customer->getId() || $baseGrandTotal<=0) return $this;
		
		$baseDiscount = 0;
		$luckyCodes = Mage::getModel('luckydraw/code')->getFailedCodes($customer->getId());
		$useCodes = array();
		foreach ($luckyCodes as $code){
			$codeDiscount = $code->getData('credit_rate');
			$useCodes[] = $code->getId();
			if ($baseGrandTotal - $codeDiscount <= 0){
				$baseGrandTotal = 0;
				$baseDiscount = $address->getBaseGrandTotal();
				break;
			}
			$baseGrandTotal -= $codeDiscount;
			$baseDiscount += $codeDiscount;
		}
		
		if ($baseDiscount){
			Mage::getSingleton('core/session')->setUseLuckydrawCodes($useCodes);
			$discount = Mage::app()->getStore()->convertPrice($baseDiscount);
			$address->setBaseLuckydrawDiscount(-$baseDiscount);
			$address->setLuckydrawDiscount(-$discount);
			
			$address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
			$address->setGrandTotal($address->getGrandTotal() - $discount);
		}
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address){
		$amount = $address->getLuckydrawDiscount();
		$title = $this->_getConfigHelper()->__('Lucky Draw Discount');
		if ($amount != 0){
			$address->addTotal(array(
				'code'	=> $this->getCode(),
				'title'	=> $title,
				'value'	=> $amount,
			));
		}
		return $this;
	}
}