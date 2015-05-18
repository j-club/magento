<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */


require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'facebook.php';
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'twitter_class.php';
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'postToPinterest.php';
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'postToGooglePlus.php';


class Be_Sociable_Adminhtml_SociableController extends Mage_Adminhtml_Controller_Action
{
		
		public function authorizefacebookAction(){

			$appId = Mage::getStoreConfig('besociable/facebook/appid');
			$appSecret = Mage::getStoreConfig('besociable/facebook/appsecret');
			$pageId = Mage::getStoreConfig('besociable/facebook/pageid');
			
			if($appId != '' && $appSecret != ''){
				$facebook = new Facebook(array(
						'appId'  => $appId,
						'secret' => $appSecret,
				));
					
				$user = $facebook->getUser();
				if ($user) {					
					try {						
						// Proceed knowing you have a logged in user who's authenticated.
						$user_profile = $facebook->api('/me', 'GET', array('ref' => 'fbwpp'));
						
					} catch (FacebookApiException $e) {
						Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__("Cannot login! Please double check your data!"));
						$user = null;
					}
					
					
				} else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__("Please enter your facebook app info!"));
				}
				
				if($user){
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__("You have successfully authorized with facebook."));
					$this->_redirect("*/system_config/edit/section/besociable/");
				}else {
					try{
						$loginUrl = $facebook->getLoginUrl(array('scrope'=>array('manage_pages', 'publish_actions', 'publish_stream')));
						$this->_redirect($loginUrl);
					
					}catch (Exception $ex){
						Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__("Cannot login! Please double check your data!"));
					}
					
				}				
				
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__("Please enter your facebook app info!"));
			}
			
			$this->_redirect("*/system_config/edit/section/besociable/");
		}	
	
		public function getBoardsAction(){
			$email = Mage::getStoreConfig('besociable/pinterest/email');
			$password = Mage::getStoreConfig('besociable/pinterest/password');
			
			if($email != '' && $password != ''){
				$loginError = doConnectToPinterest($email, $password);
					
				if (!$loginError)
				{
					$re = doGetBoardsFromPinterest();
					
					if(count($re)){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__('Here is the list of your Pinterest board(s): '));
						$i=1;
						foreach ($re as $item){
							$result = $i.'. '.$item['label'].' ('.$item['value'].')<a href="#" onClick="$(\'besociable_pinterest_boardid\').value=\''.$item['value'].'\'"> '.Mage::helper("sociable")->__('Use this board').' </a>';
							Mage::getSingleton("adminhtml/session")->addSuccess($result);
							$i++;
						}
					}else {
						Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('No board found! Please create a board.'));
					}
					
					
				}else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Login error!'));
				}
				
				
				//echo Mage::helper("sociable")->__('<a href="%s">Click here</a> to go back.', $this->getUrl('adminhtml/system_config/edit', array('section'=>'besociable')));
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Please check your data!'));
			}
			$this->_redirect("*/system_config/edit/section/besociable/");
		}
		public function getPagesAction(){
			$appId = Mage::getStoreConfig('besociable/facebook/appid');
			$appSecret = Mage::getStoreConfig('besociable/facebook/appsecret');

			$currentAccessToken = Mage::getStoreConfig('besociable/facebook/access_token');
			
			if($appId != '' && $appSecret != ''){
				$facebook = new Facebook(array(
						'appId'  => $appId,
						'secret' => $appSecret,
				));
					
				$facebook->setAccessToken($currentAccessToken);
				
				$user = $facebook->getUser();
				if($user){
					$user_profile = $facebook->api('/me', 'GET', array('ref' => 'fbwpp'));
					
					$accounts = $facebook->api('/me/accounts', 'GET', array('ref' => 'fbwpp'));
					$accounts = $accounts['data'];
			
					$accounts_options = array();
					foreach( $accounts as $account ) {
						if (isset($account['name']) && isset($account['category']) && $account['category'] != 'Application') {
							//$account_options_key = $account['name'] . "@@!!" . $account['id'] . "@@!!" . $account['access_token'];
							$accounts_options[] = array('value'=>$account['id'], 'label'=>$account['name']);
						}
					}
			
					if(count($accounts_options)){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__('Here is the list of your Facebook page(s): '));
						$i=1;
						foreach ($accounts_options as $item){
							$result = $i.'. '.$item['label'].' ('.$item['value'].')<a href="#" onClick="$(\'besociable_facebook_pageid\').value=\''.$item['value'].'\'"> '.Mage::helper("sociable")->__('Use this page').' </a>';
							Mage::getSingleton("adminhtml/session")->addSuccess($result);
							$i++;
						}
					}else {
						Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('No page found! Please create a page.'));
					}
			
				}else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Login error!'));
				}
			}
			
			$this->_redirect("*/system_config/edit/section/besociable/");
		}
		
		public function testtwitterAction(){
			$consumerKey = Mage::getStoreConfig('besociable/twitter/consumer_key');
			$consumerSecret = Mage::getStoreConfig('besociable/twitter/consumer_secret');
			$userToken = Mage::getStoreConfig('besociable/twitter/access_token');
			$userSecret = Mage::getStoreConfig('besociable/twitter/access_token_secret');
			
			if($consumerKey != '' && $consumerSecret != '' && $userSecret != '' && $userToken != ''){
				$tmhOAuth = new tmhOAuth(array(
						'consumer_key' => $consumerKey,
						'consumer_secret' => $consumerSecret,
						'user_token' => $userToken,
						'user_secret' => $userSecret,
				));
					
				$status = 'Tweet from '.Mage::app()->getStore()->getFrontendName().' '.Mage::getModel('core/date')->timestamp(time());
					
				//$image = 'example.png';
				$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array(
						'status' => $status
				));
				
				/*
				$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update_with_media'), array(
						'status' => $status,
						'media[]' =>"@$image"
				),true,true
				);
					*/
				if ($code == 200) {
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__('Test tweet has been posted. Please check your twitter page.'));
				} else {
						Mage::getSingleton("adminhtml/session")->addError($tmhOAuth->response['response']);
						}
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Please check your data!'));
			}
			
			$this->_redirect("*/system_config/edit/section/besociable/");
				
		}
		
		
		public function testfacebookAction(){
			$appId = Mage::getStoreConfig('besociable/facebook/appid');
			$appSecret = Mage::getStoreConfig('besociable/facebook/appsecret');
			$pageId = Mage::getStoreConfig('besociable/facebook/pageid');
			
			
			$accessToken = Mage::getStoreConfig('besociable/facebook/access_token');
			
			$message = 'Posted from '.Mage::app()->getStore()->getFrontendName().' '.Mage::getModel('core/date')->timestamp(time());
			
				
			try {
				$facebook = new Facebook(array(
						'appId'  => $appId,
						'secret' => $appSecret,
				));
					
				$facebook->setAccessToken($accessToken);
				
				$user = $facebook->getUser();
				if ($user) {
					$user_profile = $facebook->api('/me', 'GET', array('ref' => 'fbwpp'));
						
						
						
						
					$accounts = $facebook->api('/me/accounts', 'GET', array('ref' => 'fbwpp'));
					$accounts = $accounts['data'];
					
					$accounts_options = array();
					foreach( $accounts as $account ) {
						if (isset($account['name']) && isset($account['category']) && $account['category'] != 'Application') {
							$account_options_key = $account['name'] . "@@!!" . $account['id'] . "@@!!" . $account['access_token'];
							//$accounts_options[$account_options_key] = $account['name'];
							if($account['id'] == $pageId){
								$accounts_options[$account['id']] = $account_options_key; break;
							}
						}
					}
					
					
					
					$pageInfo = array_shift($accounts_options);
					
					preg_match_all("/(.*?)@@!!(.*?)@@!!(.*?)$/su", $pageInfo, $fan_page_info, PREG_SET_ORDER);
					
					if ( isset( $fan_page_info ) && isset( $fan_page_info[0] ) && isset( $fan_page_info[0][2] ) ) {
						$args = array(
								'access_token' => $fan_page_info[0][3],
								'from' => $fan_page_info[0][2],
								'link' => $this->getFrontendUrl(''),
								'picture'=>Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'), array('_area'=>'frontend')),
								'name' => $message,
								'caption' => $message,
								'description' => $message,
								'message' => $message
						);
						$args['ref'] = 'fbwpp';
					
							
						$publish_result = $facebook->api('/' . $fan_page_info[0][2] . '/feed', 'POST', $args);
					
						if(isset($publish_result) && isset($publish_result['id'])){
							Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__('Test post has been posted. Please check your facebook page.'));
						}
					}
					
					
				}else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Please check your data!'));
				}
				
				
			}catch (Exception $e){
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			
				
			$this->_redirect("*/system_config/edit/section/besociable/");
		
		}
		public function testpinterestAction(){
			$email = trim(Mage::getStoreConfig('besociable/pinterest/email'));
	    	$pass = Mage::getStoreConfig('besociable/pinterest/password');
	    	$boardId = trim(Mage::getStoreConfig('besociable/pinterest/boardid'));
				
				
				
			$message = 'Pinned from '.Mage::app()->getStore()->getFrontendName().' '.Mage::getModel('core/date')->timestamp(time());
				
		
			try {
				$login = doConnectToPinterest($email, $pass);
				
				
				if (!$login)
				{
					$result = doPostToPinterest($message, Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'), array('_area'=>'frontend')), $this->getFrontendUrl(''), $boardId);
				
						
					if($result == 'OK'){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__('Test pin has been posted. Please check your pinterest board.'));
					}
				} else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Could not login! Please check your Pinterest Email and Password!'));
				} 
		
		
			}catch (Exception $e){
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
				
		
			$this->_redirect("*/system_config/edit/section/besociable/");
		
		}
		public function testgplusAction(){
			
			$message = 'Posted from '.Mage::app()->getStore()->getFrontendName().'---'.Mage::getModel('core/date')->timestamp(time());
		
		
			$email = Mage::getStoreConfig('besociable/gplus/email');
			$pass = Mage::getStoreConfig('besociable/gplus/password');
			$pageID = Mage::getStoreConfig('besociable/gplus/pageid');
			
			
			$login = doConnectToGooglePlus2($email, $pass);
			if (!$login)
			{
				//$lnk = doGetGoogleUrlInfo2($params);
			
				$picture = Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'), array('_area'=>'frontend'));
				$img = substr($picture, strlen($picture)-3, strlen($picture));
				if($img == 'jpg'){
					$imageType = 'image/jpeg';
				}else if($img == 'gif'){
					$imageType = 'image/gif';
				}else if ($img == 'png'){
					$imageType = 'image/png';
				}
				
				$lnk = array(
						'link' => $this->getFrontendUrl(''),
						'fav' => '//s2.googleusercontent.com/s2/favicons?domain='.$_SERVER['HTTP_HOST'],
						'title' => $message,
						'domain' => $_SERVER['HTTP_HOST'],
						'txt' =>  $message,
						'img' => Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'), array('_area'=>'frontend')),
						'imgType' => $imageType
				);
				
			
				$result = doPostToGooglePlus2($message, $lnk, $pageID);
				
				
				
				
				
				if($result == 'OK'){
					Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("sociable")->__('Test post has been posted to Google+. Please check your Goolge+ page.'));
				}
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("sociable")->__('Could not login! Please check your Google+ Email and Password!'));
			} 
		
		
			$this->_redirect("*/system_config/edit/section/besociable/");
		
		}
		
	public function getFrontendUrl($routePath)
	{
	
		$secure = Mage::getStoreConfigFlag(
				Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT
		);
		//$websiteUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure);
		$websiteUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	
		return $websiteUrl . $routePath;
	}
		
}
