<?php

class Magestore_Luckydraw_Block_Luckydraw_Statistic extends Magestore_Luckydraw_Block_Luckydraw
{
	public function hasStatistic(){
		return true;
	}
	
	public function getRegisteredUser(){
		if (!$this->hasData('registered_user')){
			$this->setData('registered_user',$this->getProgram()->getRegisteredUser());
		}
		return $this->getData('registered_user');
	}
	
	public function getWinnerCode(){
		if (!$this->hasData('winner_code')){
			$winnerCode = $this->getProgram()->getPrizeModel();
			if ($winnerCode && $winnerCode->getId())
				$this->setData('winner_code',$winnerCode);
			else
				$this->setData('winner_code',null);
		}
		return $this->getData('winner_code');
	}
	
	public function userIsWinner(){
		if ($winnerCode = $this->getWinnerCode()){
			$customerId = Mage::getSingleton('customer/session')->getCustomerId();
			if ($winnerCode->getCustomerId() == $customerId) return true;
		}
		return false;
	}
}