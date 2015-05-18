<?php

class Magestore_Luckydraw_Model_Observer
{
	public function paypalPrepareItems($observer){
		if (version_compare(Mage::getVersion(),'1.9.1.1','<')){
			$salesEntity = $observer->getSalesEntity();
			$additional = $observer->getAdditional();
			if ($salesEntity && $additional){
				$totalDiscount = 0;
				if ($salesEntity->getBaseLuckydrawDiscount())
					$totalDiscount = $salesEntity->getBaseLuckydrawDiscount();
				else
					foreach ($salesEntity->getAddressesCollection() as $address)
						if ($address->getBaseLuckydrawDiscount())
							$totalDiscount = $address->getBaseLuckydrawDiscount();
				if ($totalDiscount){
					$items = $additional->getItems();
					$items[] = new Varien_Object(array(
						'name'	=> Mage::helper('luckydraw')->__('Lucky Draw Discount'),
						'qty'	=> 1,
						'amount'	=> -(abs((float)$totalDiscount)),
					));
					$additional->setItems($items);
				}
			}
		} else {
			$paypalCart = $observer->getEvent()->getPaypalCart();
			if ($paypalCart){
				$salesEntity = $paypalCart->getSalesEntity();
				$totalDiscount = 0;
				if ($salesEntity->getBaseLuckydrawDiscount())
					$totalDiscount = $salesEntity->getBaseLuckydrawDiscount();
				else
					foreach ($salesEntity->getAddressesCollection() as $address)
						if ($address->getBaseLuckydrawDiscount())
							$totalDiscount = $address->getBaseLuckydrawDiscount();
				if ($totalDiscount)
					$paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,abs((float)$totalDiscount),Mage::helper('luckydraw')->__('Lucky Draw Discount'));
			}
		}
	}
	
	public function orderPlaceAfter($observer){
		$codes = Mage::getSingleton('core/session')->getUseLuckydrawCodes();
		if (!$codes || !is_array($codes)) return $this;
		
		$order = $observer['order'];
		if (!$order->getData('luckydraw_discount')){
			Mage::getSingleton('core/session')->setUseLuckydrawCodes(null);
			return $this;
		}
		foreach ($codes as $codeId){
			$codeModel = Mage::getModel('luckydraw/code')->load($codeId);
			if (!$codeModel->getId()) continue;
			$codeModel->setData('order_id',$order->getId())
				->setData('order_increment_id',$order->getIncrementId())
				->setData('status',Magestore_Luckydraw_Model_Code::STATUS_USED);
			try {
				$codeModel->save();
			} catch (Exception $e){}
		}
		Mage::getSingleton('core/session')->setUseLuckydrawCodes(null);
	}
	
	public function orderSaveAfter($observer){
		$order = $observer['order'];
		if (!$order->getData('luckydraw_discount')) return $this;
		$cancelStatus = explode(',',Mage::helper('luckydraw')->getGeneralConfig('refund_orderstatus',$order->getStoreId()));
		if (in_array($order->getStatus(),$cancelStatus)){
			$refundCodes = Mage::getResourceModel('luckydraw/code_collection')
				->addFieldToFilter('order_id',$order->getId());
			foreach ($refundCodes as $codeModel){
				try {
					$codeModel->setStatus(Magestore_Luckydraw_Model_Code::STATUS_FAILED)
						->save();
				} catch (Exception $e){}
			}
		}
	}
    
    public function refreshDrawProgram(){
        $programs = Mage::getResourceModel('luckydraw/program_collection');
        $programs->addFieldToFilter('status',Magestore_Luckydraw_Model_Program::STATUS_PROCESSING);
        $programs->walk('afterLoad');
    }
    
    public function customerSaveAfter($observer) {
        $customer = $observer['customer'];
        if ($customer->getOrigData('confirmation')
            && !$customer->getData('confirmation')
        ) {
            $codes = Mage::getResourceModel('luckydraw/code_collection')
                    ->addFieldToFilter('status',Magestore_Luckydraw_Model_Code::STATUS_INACTIVE)
                    ->addFieldToFilter('customer_id',$customer->getId());
            foreach ($codes as $code) {
                try {
                    $code->setStatus(Magestore_Luckydraw_Model_Code::STATUS_PENDING)
                        ->save();
                    $code->sendRegisterEmail();
                } catch (Exception $e) {
                    
                }
            }
        }
    }
}