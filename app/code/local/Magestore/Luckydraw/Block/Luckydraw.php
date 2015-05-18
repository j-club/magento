<?php

class Magestore_Luckydraw_Block_Luckydraw extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
	}
	
	public function getTemplateProcessor(){
		if (!$this->hasData('template_processor')){
			$processor = Mage::helper('cms')->getBlockTemplateProcessor();
			$this->setData('template_processor',$processor);
		}
		return $this->getData('template_processor');
	}
	
	/**
	 * get Current Lucky Draw Program
	 * 
	 * @return Magestore_Luckydraw_Model_Program
	 */
	public function getProgram(){
		$program = Mage::registry('luckydraw_program');
		if (!$program){
			$program = Mage::getModel('luckydraw/program');
			$program->load(Mage::app()->getRequest()->getParam('id'));
			Mage::register('luckydraw_program',$program);
		}
		return $program;
	}
	
	/**
	 * check Customer is logged in
	 * 
	 * @return bool
	 */
	public function getIsCustomerLogin(){
		return Mage::getSingleton('customer/session')->isLoggedIn();
	}
	
	public function getEmailAddress(){
		if ($this->hasData('email_address')) return $this->getData('email_address');
		$session = Mage::getSingleton('customer/session');
		if ($this->getIsCustomerLogin()){
			$customer = $session->getCustomer();
			$emailAddress = $customer->getEmail();
		}
		$this->setData('email_address',$emailAddress);
		return $emailAddress;
	}
	
	/**
	 * get list of lucky code
	 * 
	 * @return Magestore_Luckydraw_Model_Mysql4_Code_Collection
	 */
	public function getCurrentCodes(){
		if ($this->hasData('current_codes')) return $this->getData('current_codes');
		if ($this->getEmailAddress()){
			$collection = Mage::getModel('luckydraw/code')->getCollectionByProgramEmail($this->getProgram()->getId(),$this->getEmailAddress());
			$this->setData('current_codes',$collection);
		} else {
			$this->setData('current_codes',false);
		}
		return $this->getData('current_codes');
	}
	
	public function getIsRegistered(){
		if ($this->hasData('is_registered')) return $this->getData('is_registered');
		$isRegistered = false;
		if ($this->getCurrentCodes())
			if ($this->getCurrentCodes()->count())
				$isRegistered = true;
		$this->setData('is_registered',$isRegistered);
		return $isRegistered;
	}
	
	public function getCodesHtml(){
		$codes = array();
		$programIsComplete = false;
		if ($this->getProgram()->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_COMPLETE
			|| $this->getProgram()->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_CLOSED)
			$programIsComplete = true;
		foreach ($this->getCurrentCodes() as $code){
			if ($code->getData('is_prize') && $programIsComplete)
				$codes[] = '<span class="winner">'.$code->getDrawCode().' ('.$this->__('winner').')</span>';
			else
				$codes[] = $code->getDrawCode();
		}
		return implode(', ',$codes);
	}
	
	/** program detail */
	public function getShortDescription(){
		return $this->getTemplateProcessor()->filter($this->getProgram()->getData('short_description'));
	}
	
	public function getAwardImageUrl(){
		if ($this->getProgram()->getData('award_image'))
			return Mage::getBaseUrl('media').'luckydraw/program/'.$this->getProgram()->getData('award_image');
		return false;
	}
	
    public function codeIsActived(){
        if (!$this->hasData('code_is_activated')) {
            if ($this->getCurrentCodes()) {
                $currentCode = $this->getCurrentCodes()->getFirstItem();
                if ($currentCode && $currentCode->getId()
                    && $currentCode->getStatus() != Magestore_Luckydraw_Model_Code::STATUS_INACTIVE
                ) {
                    $this->setData('current_code',$currentCode->getDrawCode());
                    $this->setData('code_is_activated',true);
                } else {
                    $this->setData('code_is_activated',false);
                }
            } else {
                $this->setData('code_is_activated',false);
            }
        }
        return $this->getData('code_is_activated');
    }
	
	public function getCurrentCode(){
        if ($this->hasData('current_code')) return $this->getData('current_code');
		if ($this->getCurrentCodes()){
			$currentCode = $this->getCurrentCodes()->getFirstItem();
			if ($currentCode && $currentCode->getId()){
				return $currentCode->getData('draw_code');
			}
		}
		return false;
	}
	
	public function getSessionCode(){
		if (!$this->hasData('session_code')){
			$sessionCode = Mage::getSingleton('customer/session')->getLuckycode();
			Mage::getSingleton('customer/session')->setLuckycode(null);
			if ($sessionCode != $this->getCurrentCode()) $sessionCode = null;
			$this->setData('session_code',$sessionCode);
		}
		return $this->getData('session_code');
	}
	
	public function getTermHtml(){
		return $this->getTemplateProcessor()->filter($this->getProgram()->getData('term_condition'));
	}
	
	public function formatCurrency($baseCurrency){
		$store = Mage::app()->getStore();
		$currency = $store->convertPrice($baseCurrency);
		return $store->formatPrice($currency);
	}
    
    public function requiredAddress(){
        return (bool) Mage::helper('luckydraw')->getRegisterConfig('address');
    }
    
    public function getLastPlayers($limit = 1) {
        $codes = Mage::getResourceModel('luckydraw/code_collection')
                ->addFieldToFilter('program_id',$this->getProgram()->getId())
                ->addFieldToFilter('status',array('neq' => Magestore_Luckydraw_Model_Code::STATUS_INACTIVE));
        $codes->getSelect()
                ->group('customer_id')
                ->order('created_time DESC')
                ->limit($limit);
        return $codes;
    }
    
    public function getLastPlayer(){
        $code = $this->getLastPlayers()->getFirstItem();
        if ($code && $code->getId()) {
            return $code;
        }
        return false;
    }
}