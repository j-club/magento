<?php

class Magestore_Luckydraw_Model_Code extends Mage_Core_Model_Abstract
{
	/** Program's Status */
	const STATUS_PENDING	= 1;
	const STATUS_FAILED		= 2;
	const STATUS_WINNED		= 3;
	const STATUS_USED		= 4;
	const STATUS_EXPIRED	= 5;
    const STATUS_INACTIVE   = 6;
	
	static public function getStatusArray(){
		$helper = Mage::helper('luckydraw');
		return array(
			self::STATUS_PENDING	=> $helper->__('Pending'),
			self::STATUS_FAILED		=> $helper->__('Failed'),
			self::STATUS_WINNED		=> $helper->__('Winned'),
			self::STATUS_USED		=> $helper->__('Used'),
			self::STATUS_EXPIRED	=> $helper->__('Expired'),
            self::STATUS_INACTIVE   => $helper->__('Inactive')
		);
	}
	
	static public function getStatusHash(){
		$statuses = array();
		foreach (self::getStatusArray() as $value => $label)
			$statuses[] = array(
				'value'	=> $value,
				'label'	=> $label
			);
		return $statuses;
	}
	
	/**
	 * Lucky Draw Code Model Constructor
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('luckydraw/code');
	}
	
	public function getProgram(){
		if (!$this->hasData('program')){
			$program = Mage::getModel('luckydraw/program')->load($this->getData('program_id'));
			if ($program->getId()) $this->setData('program',$program);
		}
		return $this->getData('program');
	}
	
	public function getCodeLenght(){
		if (!$this->hasData('code_length')){
			$codeLenght = 0;
			if ($this->getProgram()) $codeLenght = $this->getProgram()->getData('code_length');
			if (!$codeLenght) $codeLenght = 7;
			$this->setData('code_length',$codeLenght);
		}
		return $this->getData('code_length');
	}
	
	protected function _afterLoad(){
		parent::_afterLoad();
		if ($this->getData('expired_time')
			&& strtotime($this->getData('expired_time'))<time()
			&& $this->getData('status') < self::STATUS_USED){
			$this->setData('status',self::STATUS_EXPIRED);
			try {
				$this->save();
			} catch (Exception $e){}
		}
		return $this;
	}
	
	protected function _beforeSave(){
		parent::_beforeSave();
		if ($this->getData('draw_code')) return $this;
		$this->setData('draw_code',Mage::helper('luckydraw')->generateLuckyCode($this->getExistedCodes(),$this->getCodeLenght()));
		return $this;
	}
	
    protected function _afterSave() {
        parent::_afterSave();
        // Create code for Referer
        if ($this->getData('refer_user') && $this->getData('refer_email')
            && intval($this->_getDataHelper()->getReferConfig('user_per_code')) > 0
            && $this->getData('status') == self::STATUS_PENDING
        ) {
            $program = $this->getProgram();
            $programId = $program->getId();
            
            /* total code of the referer */
            $refererCodes = $this->getCollectionByProgramEmail(
                $programId,
                $this->getData('refer_email')
            );
            
            /* total referal of referer */
            $referralList = $this->getCollection()
                ->addFieldToFilter('refer_user',$this->getData('refer_user'))
                ->addFieldToFilter('refer_email',$this->getData('refer_email'))
                ->addFieldToFilter('status',self::STATUS_PENDING);
            
            $userPerCode = intval($this->_getDataHelper()->getReferConfig('user_per_code'));
            if (($referralList->count() / $userPerCode) >= $refererCodes->count()
                && $refererCodes->count()
            ) {
                $code = $refererCodes->getFirstItem();
                $referCode = Mage::getModel('luckydraw/code');
                $referCode->setData(array(
                    'program_id'	=> $programId,
                    'customer_id'	=> $code->getData('customer_id'),
                    'name'			=> $code->getData('name'),
                    'email'			=> $code->getData('email'),
                    'address_id'    => $code->getData('address_id'),
                    'created_time'	=> now(),
                    'status'		=> Magestore_Luckydraw_Model_Code::STATUS_PENDING,
                    'credit_rate'	=> $program->getData('credit_rate'),
                    'program'		=> $program
                ))->save();
                $referCode->sendReferEmail();
            }
        }
        return $this;
    }
    
	/**
	 * get list of code for a customer email by a program
	 * 
	 * @param int $program
	 * @param string $email
	 * @return Magestore_Luckydraw_Model_Mysql4_Code_Collection
	 */
	public function getCollectionByProgramEmail($program, $email){
		$collection = $this->getCollection()
			->addFieldToFilter('program_id',$program)
			->addFieldToFilter('email',$email);
		$collection->getSelect()->order('created_time ASC');
		return $collection;
	}
	
	/**
	 * get Existed code for a program
	 * 
	 * @param mixed $programId
	 * @return array
	 */
	public function getExistedCodes($programId = null){
		if (is_null($programId)) $programId = $this->getData('program_id');
		return $this->getResource()->getExistedCodes($programId);
	}
	
	/**
	 * get module helper
	 * 
	 * @return Magestore_Luckydraw_Helper_Data
	 */
	protected function _getDataHelper(){
		return Mage::helper('luckydraw');
	}
	
    public function sendActiveEmail(){
        try {
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$sender = $this->_getDataHelper()->getEmailConfig('email_sender');
			$template = $this->_getDataHelper()->getEmailConfig('confirmation_template');
			$store = Mage::app()->getStore();
			$mailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array('area' => 'frontend','store' => $store->getId()));
			$mailTemplate->sendTransactional(
				$template,
				$sender,
				$this->getEmail(),
				$this->getName(),
				array(
					'store'	=> $store,
					'code'	=> $this,
					'program'	=> $this->getProgram(),
                    'confirm_url'   => $this->_getDataHelper()->getProgramConfirmUrl($this->getProgram(), $this)
				)
			);
			$translate->setTranslateInline(true);
		} catch (Exception $e){}
		return $this;
    }
	
	public function sendRegisterEmail(){
		if (!$this->_getDataHelper()->getEmailConfig('is_sent_register_user')) return $this;
		try {
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$sender = $this->_getDataHelper()->getEmailConfig('email_sender');
			$template = $this->_getDataHelper()->getEmailConfig('register_user_template');
			$store = Mage::app()->getStore();
			$mailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array('area' => 'frontend','store' => $store->getId()));
			$mailTemplate->sendTransactional(
				$template,
				$sender,
				$this->getEmail(),
				$this->getName(),
				array(
					'store'	=> $store,
					'code'	=> $this,
					'program'	=> $this->getProgram()
				)
			);
			$translate->setTranslateInline(true);
		} catch (Exception $e){}
		return $this;
	}
	
	public function sendReferEmail(){
		if (!$this->_getDataHelper()->getEmailConfig('is_sent_refer_user')) return $this;
		try {
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$sender = $this->_getDataHelper()->getEmailConfig('email_sender');
			$template = $this->_getDataHelper()->getEmailConfig('refer_user_template');
			$store = Mage::app()->getStore();
			$mailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array('area' => 'frontend','store' => $store->getId()));
			$mailTemplate->sendTransactional(
				$template,
				$sender,
				$this->getEmail(),
				$this->getName(),
				array(
					'store'	=> $store,
					'code'	=> $this,
					'program'	=> $this->getProgram()
				)
			);
			$translate->setTranslateInline(true);
		} catch (Exception $e){}
		return $this;
	}
	
	public function sendWinnerEmail(){
		if (!$this->_getDataHelper()->getEmailConfig('is_sent_winner')
			|| !$this->getData('is_prize')) return $this;
		try {
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$sender = $this->_getDataHelper()->getEmailConfig('email_sender');
			$template = $this->_getDataHelper()->getEmailConfig('winner_template');
			$store = Mage::app()->getStore();
			$mailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array('area' => 'frontend','store' => $store->getId()));
			$mailTemplate->sendTransactional(
				$template,
				$sender,
				$this->getEmail(),
				$this->getName(),
				array(
					'store'	=> $store,
					'code'	=> $this,
					'program'	=> $this->getProgram()
				)
			);
			$translate->setTranslateInline(true);
		} catch (Exception $e){}
		return $this;
	}
	
	public function getFailedCodes($customerId){
		$codes = $this->getCollection()
			->addFieldToFilter('customer_id',$customerId)
			->addFieldToFilter('status',self::STATUS_FAILED)
			->addFieldToFilter('credit_rate',array('gt' => '0.000'));
		$codes->getSelect()
			->where('(expired_time IS NULL) OR (expired_time > ?)',now())
			->order('created_time ASC');
		return $codes;
	}
}