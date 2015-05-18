<?php

class Magestore_Luckydraw_Model_Total_Pdf_Luckydraw extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    public function getTotalsForDisplay(){
		$discount = $this->getAmount();
		$fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
		if(floatval($discount)){
			$discount = $this->getOrder()->formatPriceTxt($discount);
			if ($this->getAmountPrefix()){
				$discount = $this->getAmountPrefix().$discount;
			}
			$totals = array(
				array(
					'label' => Mage::helper('luckydraw')->__('Lucky Draw Discount'),
					'amount' => $discount,
					'font_size' => $fontSize,
				)
			);
			return $totals;
		}
	}
	
    public function getAmount(){
        return $this->getOrder()->getLuckydrawDiscount();
    }
}