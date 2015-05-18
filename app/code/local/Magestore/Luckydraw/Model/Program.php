<?php

class Magestore_Luckydraw_Model_Program extends Mage_Core_Model_Abstract
{
	/** Program's Status */
	const STATUS_PENDING	= 1;
	const STATUS_PROCESSING	= 2;
	const STATUS_DIALING	= 3;
	const STATUS_COMPLETE	= 4;
	/** Inactive Status */
	const STATUS_PAUSED		= 5;
	const STATUS_CLOSED		= 6;
	
	static public function getStatusArray(){
		$helper = Mage::helper('luckydraw');
		return array(
			self::STATUS_PENDING	=> $helper->__('Pending'),
			self::STATUS_PROCESSING	=> $helper->__('Processing'),
			self::STATUS_DIALING	=> $helper->__('Waiting Complete'),
			self::STATUS_COMPLETE	=> $helper->__('Complete'),
			self::STATUS_PAUSED		=> $helper->__('Paused'),
			// self::STATUS_CLOSED		=> $helper->__('Closed'),
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
	
	public function getStatusLabel(){
		$statusArray = $this->getStatusArray();
		$status = $this->getStatus();
		if (isset($statusArray[$status])) return $statusArray[$status];
	}
	
	public function getLabelByStatus($status, $useDefault = false){
		$statusArray = $this->getStatusArray();
		if (isset($statusArray[$status])) return $statusArray[$status];
		if ($useDefault) return $statusArray[self::STATUS_PENDING];
		return '';
	}
	
	/**
	 * Program Model Constructor
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('luckydraw/program');
	}
	
	protected function _afterLoad(){
		parent::_afterLoad();
		if (!$this->getId()){
			return $this;
		}
		if (strlen($this->getStoreIds()))
			$this->setStoreId(explode(',',$this->getStoreIds()));
		try {
			$this->refreshDatetimeObject();
		} catch (Exception $e){}
		$status = (int)$this->getData('status');
		if ($status < self::STATUS_COMPLETE && $this->getData('start_time_obj')){
			$oldStatus = $status;
			$status = self::STATUS_PENDING;
			if (time() > $this->getData('start_time_obj')->getTimestamp()) $status = self::STATUS_PROCESSING;
			if (time() > $this->getData('end_time_obj')->getTimestamp()) $status = self::STATUS_DIALING;
			$this->setData('status',$status);
			if ($oldStatus != $status){
				$isChangedStatus = true;
				try {
					$this->save();
				} catch (Exception $e){}
			}
		}
		if ($this->getData('status') == self::STATUS_DIALING){
			if ($this->getData('auto_prize')){
				$this->setData('status',self::STATUS_COMPLETE);
				try {
					$this->save();
					$this->dialLuckDraw();
				} catch (Exception $e){}
				try {
					$this->sendAdminEmail();
				} catch (Exception $e){}
			} elseif (isset($isChangedStatus) && $isChangedStatus){
				try {
					$this->sendAdminEmail();
				} catch (Exception $e){}
			}
		}
		return $this;
	}
	
	protected function _beforeSave(){
		parent::_beforeSave();
		if (is_array($this->getData('stores'))){
			$this->setData('store_ids',implode(',',$this->getData('stores')));
		}
		$status = (int)$this->getData('status');
		if ($status < self::STATUS_COMPLETE && $this->getData('start_time_obj')){
			$status = self::STATUS_PENDING;
			if (time() > $this->getData('start_time_obj')->getTimestamp()) $status = self::STATUS_PROCESSING;
			if (time() > $this->getData('end_time_obj')->getTimestamp()) $status = self::STATUS_DIALING;
			$this->setData('status',$status);
		}
		if ($this->getData('time_included_gmt') && $this->getData('start_time_obj')){
			$this->setData('start_time',date('Y-m-d H:i:s',$this->getData('start_time_obj')->getTimestamp()));
			$this->setData('end_time',date('Y-m-d H:i:s',$this->getData('end_time_obj')->getTimestamp()));
			$this->setData('time_included_gmt',false);
		}
		return $this;
	}
	
	/**
	 * Refresh datetime to Object
	 */
	public function refreshDatetimeObject(){
		$startTime = $this->getData('start_time');
		try {
			$startTimeObj = Mage::app()->getLocale()->date($startTime, Varien_Date::DATETIME_INTERNAL_FORMAT);
		} catch (Exception $e){
			$startTimeObj = Mage::app()->getLocale()->date($startTime, Varien_Date::DATETIME_INTERNAL_FORMAT);
		}
		$endTime = $this->getData('end_time');
		try {
			$endTimeObj = Mage::app()->getLocale()->date($endTime, Varien_Date::DATETIME_INTERNAL_FORMAT);
		} catch (Exception $e){
			$endTimeObj = Mage::app()->getLocale()->date($endTime, Varien_Date::DATETIME_INTERNAL_FORMAT);
		}
		if ($this->getData('time_included_gmt')){
			$startTimeObj->addTimestamp($startTimeObj->getGmtOffset());
			$endTimeObj->addTimestamp($endTimeObj->getGmtOffset());
		}
		$this->setData('start_time_obj',$startTimeObj);
		$this->setData('end_time_obj',$endTimeObj);
		
		return $this;
	}
	
	public function checkProgramUrlKey($urlKey, $storeId = null){
		if (!$urlKey) return false;
		if (is_null($storeId)) $storeId = Mage::app()->getStore()->getId();
		$program = $this->getCollection()->addStoreFilter($storeId)
			->addFieldToFilter('url_key',$urlKey)
			->getFirstItem();
		if ($program && $program->getId()) return $program->getId();
		return false;
	}
	
	public function getRegisteredUser(){
		$collection = Mage::getResourceModel('luckydraw/code_collection');
		$collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
			->where('program_id = ?',$this->getId())
            ->where('status != ?',Magestore_Luckydraw_Model_Code::STATUS_INACTIVE)
			->columns(array(
				'users'	=> 'COUNT(DISTINCT email)'
			));
		return $collection->getFirstItem()->getData('users');
	}
	
	public function getPrizeModel(){
		$collection = Mage::getResourceModel('luckydraw/code_collection')
			->addFieldToFilter('program_id',$this->getId())
			->addFieldToFilter('is_prize',1);
		return $collection->getFirstItem();
	}
	
	public function dialLuckDraw(){
		$totalCodes = array();
		$codeCollection = Mage::getResourceModel('luckydraw/code_collection')
			->addFieldToFilter('program_id',$this->getId())
            ->addFieldToFilter('status', array('lt' => Magestore_Luckydraw_Model_Code::STATUS_USED));
		$expiredDate = false;
		if ($this->getData('prize_days')){
			$expire = new Zend_Date();
			$expire->addDay(intval($this->getData('prize_days')));
			$expiredDate = date('Y-m-d H:i:s',$expire->getTimestamp());
		}
		foreach ($codeCollection as $codeModel){
			$totalCodes[] = $codeModel->getData('draw_code');
			$codeModel->setData('is_prize',0)
				->setData('status',Magestore_Luckydraw_Model_Code::STATUS_FAILED)
				->setData('credit_rate',$this->getData('credit_rate'));
			if ($expiredDate) $codeModel->setData('expired_time',$expiredDate);
			try {
				$codeModel->save();
			} catch (Exception $e){}
		}
		if ($this->getRegisteredUser() < $this->getData('min_user')){
			throw new Exception(Mage::helper('luckydraw')->__('Not enough user to complete this draw'));
		}
		$prizeCode = $this->getData('prize_code');
		$winnerModel = $this->findWinnerCode($prizeCode,$totalCodes);
		if ($winnerModel && $winnerModel->getId()){
			try {
				$winnerModel->setData('is_prize',1)
					->setData('status',Magestore_Luckydraw_Model_Code::STATUS_WINNED)
					->setData('credit_rate',0)
					->save();
				$winnerModel->sendWinnerEmail();
			} catch (Exception $e){}
		}
	}
	
	public function findWinnerCode($prizeCode,$totalCodes){
		$prizeCode = intval($prizeCode);
		$winnerCodes = array();
		$distance = -1;
		foreach ($totalCodes as $code){
			if ($distance == -1){
				$distance = abs(intval($code) - $prizeCode);
				$winnerCodes = array($code);
			} else {
				$delta = abs(intval($code) - $prizeCode);
				if ($distance == $delta){
					$winnerCodes[] = $code;
				} elseif ($distance > $delta){
					$distance = $delta;
					$winnerCodes = array($code);
				}
			}
		}
		$collection = Mage::getResourceModel('luckydraw/code_collection')
			->addFieldToFilter('program_id',$this->getId())
			->addFieldToFilter('draw_code',array('in' => $winnerCodes));
		$collection->getSelect()->order('created_time ASC');			
		return $collection->getFirstItem();
	}
	
	/**
	 * get module helper
	 * 
	 * @return Magestore_Luckydraw_Helper_Data
	 */
	protected function _getDataHelper(){
		return Mage::helper('luckydraw');
	}
	
	public function sendAdminEmail(){
		if (!$this->_getDataHelper()->getEmailConfig('is_sent_dial_admin')
			|| ($this->getData('status') != self::STATUS_DIALING
				&& $this->getData('status') != self::STATUS_COMPLETE))
				return $this;
		try {
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$sender = $this->_getDataHelper()->getEmailConfig('email_sender');
			$template = $this->_getDataHelper()->getEmailConfig('admin_template');
			$store = Mage::app()->getStore();
			$mailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array('area' => 'frontend','store' => $store->getId()));
			$mailTemplate->sendTransactional(
				$template,
				$sender,
				$this->_getDataHelper()->getEmailConfig('admin_email'),
				$this->_getDataHelper()->getEmailConfig('admin_name'),
				array(
					'store'	=> $store,
					'program'	=> $this,
					'admin_name'=> $this->_getDataHelper()->getEmailConfig('admin_name'),
					'admin_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('luckydraw/adminhtml_program/edit',array(
						'id'	=> $this->getId()
					))
				)
			);
			$translate->setTranslateInline(true);
		} catch (Exception $e){}
		return $this;
	}
	
	public function isComplete(){
		return ($this->getData('status') == self::STATUS_COMPLETE);
	}
    
    public function getPrizeDays(){
        if ($this->getData('prize_days') > 0) {
            return $this->getData('prize_days');
        }
        return false;
    }
}
