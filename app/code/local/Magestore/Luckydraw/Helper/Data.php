<?php

class Magestore_Luckydraw_Helper_Data extends Mage_Core_Helper_Data
{
	public function generateLuckyCode($excludes,$length = 7){
		if (!$length) $length = 7;
		$max = 1000;
		while($max){
			$code = $this->getRandomString($length,'0123456789');
			if (in_array($code,$excludes)) $max--;
			else break;
		}
		if ($max == 0) throw new Exception($this->__('Tried to generate lucky code 1000 times!'));
		return $code;
	}
	
	public function getGeneralConfig($code, $store = null){
		return Mage::getStoreConfig('luckydraw/general/'.$code,$store);
	}
	
	public function getReferConfig($code, $store = null){
		return Mage::getStoreConfig('luckydraw/refer/'.$code,$store);
	}
	
	public function getEmailConfig($code, $store = null){
		return Mage::getStoreConfig('luckydraw/email/'.$code,$store);
	}
    
    public function getRegisterConfig($code, $store = null){
        return Mage::getStoreConfig('luckydraw/register/'.$code,$store);
    }
    
    public function getProgramConfirmUrl($program, $code) {
        $key = "{$program->getId()}_{$code->getDrawCode()}_{$code->getCustomerId()}_{$code->getEmail()}";
        return $this->_getUrl('luckydraw/index/confirmation', array(
            'id'    => $program->getId(),
            'code'  => $code->getData('draw_code'),
            'key'   => md5($key)
        ));
    }
}
