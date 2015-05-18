<?php

class Magestore_Luckydraw_Block_Luckydraw_Countdown extends Magestore_Luckydraw_Block_Luckydraw
{
	public function getTimeLeft($inputTime){
		$deltaTime = max($inputTime->getTimestamp() - time(),0);
		
		$timeLeft = array();
		$timeLeft['year'] = 0;
		$timeLeft['month'] = 0;
		
		$timeLeft['day'] = floor($deltaTime/(24 * 60 * 60));
		$deltaTime -= 24 * 60 * 60 * $timeLeft['day'];
		
		$timeLeft['hour'] = floor($deltaTime/(60 * 60));
		$deltaTime -= 60 * 60 * $timeLeft['hour'];
		
		$timeLeft['minute'] = floor($deltaTime/60);
		$timeLeft['second'] = $deltaTime - 60 * $timeLeft['minute'];
		
		return Zend_Json::encode($timeLeft);
	}
}