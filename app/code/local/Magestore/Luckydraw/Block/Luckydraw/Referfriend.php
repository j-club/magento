<?php

class Magestore_Luckydraw_Block_Luckydraw_Referfriend extends Magestore_Luckydraw_Block_Luckydraw
{
	public function _getHelper(){
		return Mage::helper('luckydraw');
	}
	
	/** Personal URL */
	public function getPersonalUrl(){
		if (!$this->hasData('personal_url')){
			$personalUrl = $this->getUrl(null,array('_direct' => $this->getProgram()->getUrlKey()));
			if ($this->getAccount()->getId())
				$personalUrl .= '?user='.$this->getAccount()->getId();
			$this->setData('personal_url',$personalUrl);
		}
		return $this->getData('personal_url');
	}
	
	public function getReferDescription(){
		return $this->_getHelper()->getReferConfig('refer_description');
	}
	
	/** Email Account */
	public function getAccount(){
		return $this->getCurrentCodes()->getFirstItem();
	}
	
	public function getAccountEmail(){
		return $this->getAccount()->getEmail();
	}
	
	/** Email Template */
	public function getDefaultEmailSubject(){
		return $this->_getHelper()->getReferConfig('email_subject');
	}
	
	public function getDefaultEmailContent(){
		$content = $this->_getHelper()->getReferConfig('email_content');
		return str_replace(
			array(
				'{{store_name}}',
				'{{personal_url}}',
				'{{account_name}}'
			),
			array(
				Mage::app()->getStore()->getFrontendName(),
				$this->getPersonalUrl(),
				$this->getAccount()->getName()
			),
			$content
		);
	}
	
	public function getEmailFormData(){
		if (!$this->hasData('email_form_data')){
			$data = Mage::getSingleton('core/session')->getEmailFormData();
			Mage::getSingleton('core/session')->setEmailFormData(null);
			$dataObj = new Varien_Object($data);
			$this->setData('email_form_data',$dataObj);
		}
		return $this->getData('email_form_data');
	}
	
	public function getJsonEmail(){
		$result = array(
			'yahoo'	=> $this->getUrl('*/*/yahoo'),
			'gmail'	=> $this->getUrl('*/*/gmail'),
		);
		return Zend_Json::encode($result);
	}
	
	public function getDefaultSharingContent(){
		$content = $this->_getHelper()->getReferConfig('sharing_message');
		return str_replace(
			array(
				'{{store_name}}',
				'{{personal_url}}'
			),
			array(
				Mage::app()->getStore()->getFrontendName(),
				$this->getPersonalUrl()
			),
			$content
		);
	}
	
	/** Facebook */
	public function getFbLoginUrl(){
		try {
			if (!class_exists('Facebook'))
				require_once(Mage::getBaseDir('lib') . DS .'Facebookv3'. DS .'facebook.php');
			$facebook = new Facebook(array(
				'appId'		=> $this->_getHelper()->getReferConfig('fbapp_id'),
				'secret'	=> $this->_getHelper()->getReferConfig('fbapp_secret'),
				'cookie'	=> true
			));
			$loginUrl = $facebook->getLoginUrl(array(
				'display'		=> 'popup',
				'redirect_uri'	=> $this->getUrl('*/*/facebook',array('auth' => 1)),
				'scope' 		=> 'publish_stream,email',
			));
			return $loginUrl;
		} catch (Exception $e){
			
		}
	}
	
	/** Tab functions */
	public function getActiveTab(){
		if ($tab = $this->getRequest()->getParam('tab')){
			if (in_array($tab,array('email','facebook','twitter','google'))){
				return "luckydraw-opc-$tab-content";
			}
		}
		return '';
	}
	
	public function isActiveTab($_tab){
		if ($tab = $this->getRequest()->getParam('tab')){
			return ($tab == $_tab);
		}
		return false;
	}
}