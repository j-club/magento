<?php

class Magestore_Luckydraw_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $_controllerCache = array();
    
	/**
	 * Main page of program
	 */
	public function indexAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		$programId = $this->getRequest()->getParam('id');
		$program = Mage::getModel('luckydraw/program')->load($programId);
		if ($program->getId()){
			Mage::register('luckydraw_program',$program);
			$this->checkRefer($program->getId());
			
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($program->getName());
			if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')){
				$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
				$breadcrumbs->addCrumb('luckydraw_program', array('label'=>$program->getName(), 'title'=>$program->getName()));
			}
			return $this->renderLayout();
		}
		Mage::getSingleton('core/session')->addError($this->__("Lucky draw program is disabled or doesn't exist"));
		$this->getResponse()->setRedirect(Mage::getBaseUrl());
	}
	
	public function checkRefer($programId){
		if ($codeId = $this->getRequest()->getParam('user')){
			$cookie = Mage::getSingleton('core/cookie');
			try {
				$cookie->set("luckydraw_refer_to_$programId",$codeId);
			} catch (Exception $e){}
		}
		return $this;
	}
	
	/**
	 * login to system
	 */
	public function loginAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		if (!$referrer = $this->getRequest()->getServer('HTTP_REFERER')){
			$referrer = Mage::getUrl('*/*/index',array('id' => $this->getRequest()->getParam('id')));
		}
		if (Mage::getSingleton('customer/session')->isLoggedIn()){
			return $this->getResponse()->setRedirect($referrer);
		}
		Mage::getSingleton('customer/session')->setAfterAuthUrl($referrer);
		$this->_redirect('customer/account/login');
	}
	
	public function responseJson($result){
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	public function responseErrorMessage($message){
		$this->responseJson(array(
			'error'	=> 1,
			'message'	=> $message
		));
	}
	
    public function checkemailregisterAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
        $email_address = $this->getRequest()->getParam('email_address');
        $isvalid_email = true;
        if (!Zend_Validate::is(trim($email_address), 'EmailAddress')) {
            $isvalid_email = false;
        }
        if($isvalid_email){
            $error = false;
            $email = Mage::getResourceModel('customer/customer_collection')
                ->addAttributeToFilter('email',$email_address)
                ->getFirstItem();
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($email->getId() && ( !$customer || !$customer->getId()
                || ($customer && $customer->getId() != $email->getId()))
            ){
                $error = true;
            } 
            if ($error) {
                $html = "<div class='error-msg'>".$this->__('The email %s belongs to a customer. If it is your email address, you can use it to <a href="%s">login</a> our system.',$email_address,Mage::getUrl('*/*/login',array('id' => $this->getRequest()->getParam('id'))))."</div>";
                $html .= '<input type="hidden" id="is_valid_email" value="0"/>';
            } else {
                $html = "<div class='success-msg'>".$this->__('You can use this email address.')."</div>";
                $html .= '<input type="hidden" id="is_valid_email" value="1"/>';
            }
        } else {
            $html = "<div class='error-msg'>".$this->__('Invalid email address.')."</div>";
            $html .= '<input type="hidden" id="is_valid_email" value="1"/>';
        }
        $this->getResponse()->setBody($html);
    }
	
	public function registerAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		$programId = $this->getRequest()->getParam('id');
		$program = Mage::getModel('luckydraw/program')->load($programId);
		if (!$program->getId()){
            return false;
		}
		if ($program->getStatus() != Magestore_Luckydraw_Model_Program::STATUS_PROCESSING) {
			return $this->responseErrorMessage($this->__('Cannot register for this program at this time!'));
		}
		$post = new Varien_Object($this->_filterDates($this->getRequest()->getPost(), array('dob')));
        
        if ($this->_getConfigHelper()->getRegisterConfig('captcha')) {
            $captchaCode = $this->_getCoreSession()->getData('register_account_captcha_code_$programId');
            if (!$captchaCode || $captchaCode != $post->getData('account_captcha')) {
                Mage::getSingleton('core/session')->addError($this->__('Please enter the correct captcha code!'));
                return false;
            }
        }
        
        $sessCus = Mage::getSingleton('customer/session')->getCustomer();
        if ($sessCus && $sessCus->getId()) {
            if ($post->getCustomerId() != $sessCus->getId()){
                return false;
            }
            $customer = Mage::getModel('luckydraw/customer')->load($sessCus->getId());
        } else {
            $customer = Mage::getModel('luckydraw/customer');
        }
        $customer->addData($post->getData())
                ->setFirstname($post->getFirstname())
                ->setLastname($post->getLastname());
        
        $model = Mage::getModel('luckydraw/code');
        $codes = $model->getCollectionByProgramEmail($program->getId(),$customer->getEmail());
        if ($codes->count()) {
            Mage::getSingleton('core/session')->addError($this->__('You have played this program already!'));
            return false;
        }
        
        if ($this->_getConfigHelper()->getRegisterConfig('address')) {
            if ($post->getData('account_address_id')) {
                $model->setData('address_id', $post->getData('account_address_id'));
            } else {
                $address = Mage::getModel('customer/address')
                        ->setData($post->getData('account'))
                        ->setParentId($customer->getId())
                        ->setFirstname($customer->getFirstname())
                        ->setLastname($customer->getLastname())
                        ->setId(null);
                $customer->addAddress($address);
                $errors = $address->validate();
            }
        }
        
        if (!isset($errors) || !is_array($errors)) $errors = array();
        try {
            $validationCustomer = $customer->validate();
            if (is_array($validationCustomer)) {
                $errors = array_merge($validationCustomer,$errors);
            }
            $validationResult = (count($errors) == 0);
            if (true === $validationResult) {
                $customer->save();
                if (isset($address) && !$address->getId()) {
                    $address->save();
                    $model->setData('address_id', $address->getId());
                }
            } else {
                foreach ($errors as $error){
                    Mage::getSingleton('core/session')->addError($error);
                }
                Mage::getSingleton('core/session')->setRegisterLuckydraw($post->getData());
                return false;
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::getSingleton('core/session')->setRegisterLuckydraw($post->getData());
            return false;
        }
        
        $model->setData('program_id', $programId)
                ->setData('customer_id', $customer->getId())
                ->setData('email', $customer->getEmail())
                ->setData('name', $customer->getName())
                ->setData('created_time',  now())
                ->setData('credit_rate',$program->getData('credit_rate'));
        if ($code = $this->_getCodeReferrer($programId)) {
            $model->setData('refer_user', $code->getData('customer_id'))
                    ->setData('refer_email', $code->getData('email'));
        }
        
        $codeStatus = Magestore_Luckydraw_Model_Code::STATUS_INACTIVE;
        $needSendActiveEmail = true;
        if ($sessCus && $sessCus->getId()) {
            if (!$this->_getConfigHelper()->getRegisterConfig('verify')) {
                $codeStatus = Magestore_Luckydraw_Model_Code::STATUS_PENDING;
                $needSendActiveEmail = false;
            }
        } else {
            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail(
                        'confirmation',
                        $this->_getReferrerUrl(),
                        Mage::app()->getStore()->getId()
                );
                Mage::getSingleton('core/session')->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                $needSendActiveEmail = false;
            } else {
                $customer->sendNewAccountEmail(
                        'registered',
                        $this->_getReferrerUrl(),
                        Mage::app()->getStore()->getId()
                );
                Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                if (!$this->_getConfigHelper()->getRegisterConfig('verify')) {
                    $codeStatus = Magestore_Luckydraw_Model_Code::STATUS_PENDING;
                    $needSendActiveEmail = false;
                }
            }
        }
        $model->setData('status',$codeStatus);
        try {
            $model->setData('program',$program)->save();
            if ($needSendActiveEmail) {
                $model->sendActiveEmail();
                Mage::getSingleton('core/session')->addSuccess($this->__('Draw code confirmation is required. Please check your email for the confirmation link. To resend the confirmation email, please <a href="%s">click here</a>.', Mage::getUrl('*/*/resend', array('id' => $programId, 'code' => $model->getDrawCode()))));
                Mage::getSingleton('customer/session')->setLuckycode($model->getData('draw_code'));
            } elseif ($codeStatus == Magestore_Luckydraw_Model_Code::STATUS_PENDING) {
                $model->sendRegisterEmail();
                Mage::getSingleton('customer/session')->setLuckycode($model->getData('draw_code'));
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
	}
    
    public function resendAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
        $programId = $this->getRequest()->getParam('id');
        $code = $this->getRequest()->getParam('code');
        $model = Mage::getResourceModel('luckydraw/code_collection')
                ->addFieldToFilter('program_id',$programId)
                ->addFieldToFilter('draw_code',$code)
                ->getFirstItem();
        if ($model && $model->getId()){
            if ($model->getStatus() == Magestore_Luckydraw_Model_Code::STATUS_INACTIVE) {
                try {
                    $model->sendActiveEmail();
                    Mage::getSingleton('core/session')->addSuccess($this->__('The confirmation email has been sent. Please check your email for the confirmation link.'));
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError($e->getMessage());
                }
            } else {
                Mage::getSingleton('core/session')->addNotice($this->__('This code was activated!'));
            }
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Program or code not found!'));
        }
        $this->_redirectToReferer();
    }
    
    public function confirmationAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
        $programId = $this->getRequest()->getParam('id');
        $code = $this->getRequest()->getParam('code');
        
        $program = Mage::getModel('luckydraw/program')->load($programId);
        $model = Mage::getResourceModel('luckydraw/code_collection')
                ->addFieldToFilter('program_id',$programId)
                ->addFieldToFilter('draw_code',$code)
                ->getFirstItem();
        if ($program && $program->getId() && $model && $model->getId()) {
            if ($model->getStatus() != Magestore_Luckydraw_Model_Code::STATUS_INACTIVE) {
                Mage::getSingleton('core/session')->addNotice($this->__('This code was activated!'));
            } else {
				//check time when customer confirm
				$current_time = Mage::getModel('core/date')->timestamp(time());
				$start_time=Mage::getModel('core/date')->timestamp(strtotime($program->getStartTime()));
                $end_time=Mage::getModel('core/date')->timestamp(strtotime($program->getEndTime()));
                if($current_time >= $start_time && $current_time <= $end_time){		
                $key = $this->getRequest()->getParam('key');
                $keyString = "{$program->getId()}_{$model->getDrawCode()}"
                           . "_{$model->getCustomerId()}_{$model->getEmail()}";
                if (trim($key) == md5($keyString)) {
                    try {
                        $model->setStatus(Magestore_Luckydraw_Model_Code::STATUS_PENDING)
                            ->save();
                        $model->sendRegisterEmail();
                        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                            Mage::getSingleton('customer/session')->loginById($model->getCustomerId());
                        }
                        Mage::getSingleton('core/session')->addSuccess($this->__('Your code has been activated!'));
                    } catch (Exception $e){
                        Mage::getSingleton('core/session')->addError($e->getMessage());
                    }
                } else {
                    Mage::getSingleton('core/session')->addError($this->__('Confirmation key does not match.'));
                }
				}else{
					Mage::getSingleton('core/session')->addError($this->__('The program has expired. You cannot confirm your lucky code now.'));
				}
            }
            return $this->getResponse()->setRedirect(Mage::getUrl(null,array('_direct' => $program->getUrlKey())));
        }
        Mage::getSingleton('core/session')->addError($this->__("Lucky draw program is disabled or doesn't exist"));
		$this->getResponse()->setRedirect(Mage::getBaseUrl());
    }
	
	protected function _getReferrerUrl(){
		if ($this->getRequest()->getServer('HTTP_REFERER'))
			return $this->getRequest()->getServer('HTTP_REFERER');
		return Mage::getUrl('*/*/index',array('id' => $this->getRequest()->getParam('id')));
	}
	
	protected function _redirectToReferer(){
		if ($this->getRequest()->getServer('HTTP_REFERER'))
			return $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER'));
		$this->_redirect('*/*/index',array('id' => $this->getRequest()->getParam('id')));
	}
	
    protected function _getCodeReferrer($programId) {
        if (!isset($this->_controllerCache['code_referrer'])) {
            $cookie = Mage::getSingleton('core/cookie');
            if ($codeId = $cookie->get("luckydraw_refer_to_$programId")) {
                $code = Mage::getModel('luckydraw/code')->load($codeId);
                if ($code->getData('program_id') == $programId
                    && $code->getData('status') == Magestore_Luckydraw_Model_Code::STATUS_PENDING
                ) {
                    $this->_controllerCache['code_referrer'] = $code;
                    return $code;
                }
            }
            $this->_controllerCache['code_referrer'] = false;
        }
        return $this->_controllerCache['code_referrer'];
    }
	
	/** Refer friend Actions */
	protected function _getConfigHelper(){
		return Mage::helper('luckydraw');
	}
	
	/**
	 * get Core Session
	 *
	 * @return Mage_Core_Model_Session
	 */
	protected function _getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
	public function facebookAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		try {
    		$isAuth = $this->getRequest()->getParam('auth');
    		if (!class_exists('Facebook'))
				require_once(Mage::getBaseDir('lib') . DS .'Facebookv3'. DS .'facebook.php');
			$facebook = new Facebook(array(
				'appId'		=> $this->_getConfigHelper()->getReferConfig('fbapp_id'),
				'secret'	=> $this->_getConfigHelper()->getReferConfig('fbapp_secret'),
				'cookie'	=> true
			));
			$userId = $facebook->getUser();
			if ($isAuth && !$userId){
				$loginUrl = $facebook->getLoginUrl(array('scope' => 'publish_stream,email'));
				die("<script type='text/javascript'>top.location.href = '$loginUrl';</script>");
			}
			$params = $this->getRequest()->getParams();
			if (!isset($params['message'])){
				echo "<html><head></head><body><script type='text/javascript'>
				var newUrl = window.location.href;
				var message = '';
				try{
					message = window.opener.document.getElementById('luckydraw-facebook-content').value;
					message = encodeURIComponent(message);
				}catch(e){}
				var fragment = '';
				if (newUrl.indexOf('#')){
					fragment = '#' + newUrl.split('#')[1];
					newUrl = newUrl.split('#')[0];
				}
				if (newUrl.indexOf('?') != -1) newUrl += '&message=' + message;
				else newUrl += '?message=' + message;
				newUrl += fragment;
				top.location.href = newUrl;
				</script></body></html>";
				exit();
			}
			$message = $params['message'];
			
			$facebook->api("/$userId/feed",'POST',array('message' => $message));
            echo "<html><head></head><body><script type='text/javascript'>"
                . "alert('".$this->__('Your message has been posted successfully!')."');"
                . "window.close();"
                . "</script></body></html>";
            exit();
    	} catch (Exception $e){
            echo "<html><head></head><body><script type='text/javascript'>"
                . "alert('".$e->getMessage()."');"
                . "window.close();"
                . "</script></body></html>";
            exit();
    	}
    	echo "<script type='text/javascript'>
    	window.close();
    	</script>";
    	exit();
	}
	
	public function gmailAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		$gmail = Mage::getSingleton('luckydraw/refer_gmail');
		if (!$gmail->isAuth())
			return $this->_redirectUrl($gmail->getAuthUrl());
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function yahooAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		$yahoo = Mage::getSingleton('luckydraw/refer_yahoo');
		if (!$yahoo->hasSession() || !$this->getRequest()->getParam('oauth_token') || !$this->getRequest()->getParam('oauth_verifier'))
			return $this->_redirectUrl($yahoo->getAuthUrl());
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function emailAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
		if ($data = $this->getRequest()->getPost()){
    		$emails = $data['emails'];
    		$subject = $data['email_subject'];
    		$content = $data['email_content'];
    		$session = $this->_getCoreSession();
    		if (strpos($emails,'@') === false){
    			$session->addError($this->__('No email address is available!'));
    			$session->setEmailFormData($data);
    			return $this->_redirectEmail();
    		}
    		$account = Mage::getBlockSingleton('luckydraw/luckydraw_referfriend')->getAccount();
    		if (!$account->getId() || !$subject || !$content){
    			$session->addError($this->__('Form data is not available!'));
    			$session->setEmailFormData($data);
    			return $this->_redirectEmail();
   			}
    		$contacts = array_unique(explode(',',$emails));
    		$totalSent = 0;
    		
    		$translate = Mage::getSingleton('core/translate');
    		$translate->setTranslateInline(false);
    		$sender = array('name' => $account->getName(), 'email' => $account->getEmail());
    		$template = $this->_getConfigHelper()->getEmailConfig('refer_template');
    		
    		$store = Mage::app()->getStore();
    		$mailTemplate = Mage::getModel('core/email_template')
    			->setDesignConfig(array('area' => 'frontend','store' => $store->getId()));
    		foreach ($contacts as $contact){
				$content = $data['email_content'];
				$subject = $data['email_subject'];
    			if (strpos($contact,'@') === false) continue;
    			$name = '';
    			if (strpos($contact,'<') !== false){
    				$name = substr($contact,0,strpos($contact,'<'));
    				$contact = substr($contact,strpos($contact,'<')+1);
    			}
    			$email = rtrim(rtrim($contact),'>');
    			if (!$name){
    				$emailExtract = explode('@',$email);
    				$name = $emailExtract[0];
    			}
    			$subject = str_replace(array('{{friend_name}}','{{friend_email}}'),array($name,$email),$subject);
    			$content = str_replace(array('{{friend_name}}','{{friend_email}}'),array($name,$email),$content);
    			try {
	    			$mailTemplate->sendTransactional(
	    				$template,
	    				$sender,
	    				$email,
	    				$name,
	    				array(
	    					'store'	=> $store,
	    					'contact_name'	=> $name,
	    					'sender_name'	=> $account->getName(),
	    					'subject'	=> $subject,
	    					'content'	=> $content,
	    				)
	    			);
	    			$totalSent++;
    			} catch (Exception $e) {
    			}
    		}
    		$translate->setTranslateInline(true);
    		
    		if ($totalSent){
    			$session->addSuccess($this->__('Total %s email(s) have been sent successfully!',$totalSent));
    		} else {
    			$session->addError($this->__('No email has been sent.'));
    			$session->setEmailFormData($data);
    			return $this->_redirectEmail();
    		}
    	}
    	if ($this->getRequest()->getServer('HTTP_REFERER'))
			return $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER'));
		$this->_redirect('*/*/index',array('id' => $this->getRequest()->getParam('id')));
	}
	
	protected function _redirectEmail(){
		return $this->_redirectToReferer();
	}
	
	public function imagecaptchaAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
    	require_once(Mage::getBaseDir('lib') . DS .'captcha'. DS .'class.simplecaptcha.php');
		$config['BackgroundImage'] = Mage::getBaseDir('lib') . DS .'captcha'. DS . "white.png";
		$config['BackgroundColor'] = "FF0000";
		$config['Height']=30;
		$config['Width']=100;
		$config['Font_Size']=23;
		$config['Font']= Mage::getBaseDir('lib') . DS .'captcha'. DS . "ARLRDBD.TTF";
		$config['TextMinimumAngle']=15;
		$config['TextMaximumAngle']=30;
		$config['TextColor']='2B519A';
		$config['TextLength']=4;
		$config['Transparency']=80;
		$captcha = new SimpleCaptcha($config);
		
		$programId = $this->getRequest()->getParam('id');
		$this->_getCoreSession()->setData('register_account_captcha_code_$programId',$captcha->Code);
    }
    
    public function refreshcaptchaAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
    	echo Mage::getUrl('*/*/imageCaptcha',array('time' => time(),'id' => $this->getRequest()->getParam('id')));
    }
    
    public function playersAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)) { return; }
        $listBlock = $this->getLayout()->createBlock('luckydraw/luckydraw_players', 'list_players');
        $this->getResponse()->setBody($listBlock->toHtml());
    }
}