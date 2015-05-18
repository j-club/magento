<?php
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'facebook.php';
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'twitter_class.php';
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'postToPinterest.php';
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'postToGooglePlus.php';

class Be_Sociable_Model_Observer
{
	public function handleSuccess($observer){
		$orderIds = $observer->getOrderIds();
		$orderId = array_shift($orderIds);
		
		
		
		if($orderId){
			$order = Mage::getModel('sales/order')->load($orderId);
			if($order->getId()){
				$orderItems = $order->getAllItems();
				if(count($orderItems)){
					foreach ($orderItems as $item){
						if(!$item->getParentItemId()){
							$productId = $item->getProductId();
							$this->processProduct($productId, 'sold');
						}
						
					}
				}
			}
		}
	}
	
	public function handleFacebook($product, $picture, $type, $url){
		$productId = $product->getId();
		//Facebook
		$newEnabled = Mage::getStoreConfigFlag('besociable/facebook/newproduct_enabled');
		$editedEnabled = Mage::getStoreConfigFlag('besociable/facebook/editedproduct_enabled');
		$soldEnabled  = Mage::getStoreConfigFlag('besociable/facebook/soldproduct_enabled');
		$maxPost = (int)Mage::getStoreConfig('besociable/facebook/maxposts');
		if($maxPost == 0) {
			$maxPost = 9999999;
		}
		
		$notExceed = true;
		if($type != 'cron'){
			$currentPosts = $this->getDailyCount('facebook');
			if($currentPosts >= $maxPost){
				$notExceed = false;
			}
		}
		
		
		$message = '';
		$shouldPost = false;
		if($type == 'new'){//New product
			if($newEnabled){
				$message = Mage::getStoreConfig('besociable/facebook/newproductext');
				$shouldPost = true;
			}
		}else if($type == 'edit'){//edited product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/facebook/editedproductext');
				$shouldPost = true;
			}
				
		}else if($type == 'sold'){//sold product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/facebook/soldproductext');
				$shouldPost = true;
			}
		}else if($type == 'cron'){//random product
			
			$message = Mage::getStoreConfig('besociable/facebook/randomtext');
			$shouldPost = true;
			
		}
			
		if($shouldPost){
			if($notExceed){
				$defaultMessage = (string)$product->getFacebookText();
				
				if($defaultMessage == ''){
					$defaultMessage = Mage::getStoreConfig('besociable/facebook/defaulttext');
				}
				
				
				$msg = $this->getMessage($product, $defaultMessage);
				
				
				$shortener = Mage::getStoreConfig('besociable/facebook/shortener');
				$shortenerLogin = Mage::getStoreConfig('besociable/facebook/shortener_login');
				$shortenerPass = Mage::getStoreConfig('besociable/facebook/shortener_api_key');
				
				$shortener = (string)$shortener;
				
				$link = Mage::helper('sociable')->shortenLink($url, $shortener, $shortenerLogin, $shortenerPass);
				
				
				try {
						
					$result = $this->postToFacebook($link, $picture, $msg['name'], $msg['caption'], $msg['description'], $message);
					if($result){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('sociable')->__('Posted to Facebook!'));
				
						$fullMessage = $message.'-----'.$msg['name'].'-----'.$msg['description'];
						$re = $this->saveLog($productId,$type,'facebook',$fullMessage);
							
						return true;
					}
						
						
						
				}catch (Exception $e){
					Mage::log($e->getMessage());
				}
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Maximum number of daily posts for Facebook reached!'));
			}
			
		}else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Could not post to Facebook!'));
		}
		return false;		
	}
	public function handleTwitter($product, $picture, $picturePath, $type, $url){
		//Twitter
		$productId = $product->getId();
		$newEnabled = Mage::getStoreConfigFlag('besociable/twitter/newproduct_enabled');
		$editedEnabled = Mage::getStoreConfigFlag('besociable/twitter/editedproduct_enabled');
		$soldEnabled  = Mage::getStoreConfigFlag('besociable/twitter/soldproduct_enabled');
		
		$maxPost = (int)Mage::getStoreConfig('besociable/twitter/maxposts');
		if($maxPost == 0) {
			$maxPost = 9999999;
		}
		
		$notExceed = true;
		if($type != 'cron'){
			$currentPosts = $this->getDailyCount('twitter');
			if($currentPosts >= $maxPost){
				$notExceed = false;
			}
		}
		
		$message = '';
		$shouldPost = false;
		if($type == 'new'){//New product
			if($newEnabled){
				$message = Mage::getStoreConfig('besociable/twitter/newproductext');
				$shouldPost = true;
			}
		}else if($type == 'edit'){//edited product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/twitter/editedproductext');
				$shouldPost = true;
			}
				
		}else if($type == 'sold'){//sold product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/twitter/soldproductext');
				$shouldPost = true;
			}
		}else if($type == 'cron'){//random product
			
			$message = Mage::getStoreConfig('besociable/twitter/randomtext');
			$shouldPost = true;
			
		}
		
	
		if($shouldPost){
			if($notExceed){
				$defaultMessage = (string)$product->getTwitterText();
				
				if($defaultMessage == ''){
					$defaultMessage = Mage::getStoreConfig('besociable/twitter/defaulttext');
				}
				
				
				$msg = $this->getMessage($product, $defaultMessage);
				
				
				$shortener = Mage::getStoreConfig('besociable/twitter/shortener');
				$shortenerLogin = Mage::getStoreConfig('besociable/twitter/shortener_login');
				$shortenerPass = Mage::getStoreConfig('besociable/twitter/shortener_api_key');
				
				$shortener = (string)$shortener;
				
				$link = Mage::helper('sociable')->shortenLink($url, $shortener, $shortenerLogin, $shortenerPass);
				
				$includeImage = Mage::getStoreConfigFlag('besociable/twitter/withimage');
				
				$image = '';
				if($includeImage){
					$image = $picturePath;
				}
				try {
					$status = $message.': '.$msg['name'];
					$status = Mage::helper('sociable')->fitTweetAuto($status, $link);
				
					$result = $this->postToTwitter($status, $image);
						
					if($result){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('sociable')->__('Tweeted to Twitter!'));
						$re = $this->saveLog($productId,$type,'twitter',$status);
							
						return true;
					}
				
				
				}catch (Exception $e){
					Mage::log($e->getMessage());
				}
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Maximum number of daily tweet for Twitter reached!'));
			}
			
		}else {
			//Mage::log('maximum number of daily post reached!!!');
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Could not tweet to Twitter!'));
		}
		
		return false;
	}
	public function handleGplus($product, $picture, $type, $url){
		$productId = $product->getId();
		$gplusEnable = Mage::getStoreConfigFlag('besociable/gplus/enabled');
		$newEnabled = Mage::getStoreConfigFlag('besociable/gplus/newproduct_enabled');
		$editedEnabled = Mage::getStoreConfigFlag('besociable/gplus/editedproduct_enabled');
		$soldEnabled  = Mage::getStoreConfigFlag('besociable/gplus/soldproduct_enabled');
		
		$maxPost = (int)Mage::getStoreConfig('besociable/gplus/maxposts');
		if($maxPost == 0) {
			$maxPost = 9999999;
		}
		
		$notExceed = true;
		if($type != 'cron'){
			
			$currentPosts = $this->getDailyCount('gplus');
			if($currentPosts >= $maxPost){
				$notExceed = false;
			}
		}
		$message = '';
		$shouldPost = false;
		if($type == 'new'){//New product
			if($newEnabled){
				$message = Mage::getStoreConfig('besociable/gplus/newproductext');
				$shouldPost = true;
			}
		}else if($type == 'edit'){//edited product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/gplus/editedproductext');
				$shouldPost = true;
			}
				
		}else if($type == 'sold'){//sold product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/gplus/soldproductext');
				$shouldPost = true;
			}
		}else if($type == 'cron'){//random product
			
			$message = Mage::getStoreConfig('besociable/gplus/randomtext');
			$shouldPost = true;
			
		}
	
		if($shouldPost){
			if($notExceed){
				$defaultMessage = (string)$product->getGplusText();
		
				if($defaultMessage == ''){
					$defaultMessage = Mage::getStoreConfig('besociable/gplus/defaulttext');
				}
		
				$msg = $this->getMessage($product, $defaultMessage);
				$shortener = Mage::getStoreConfig('besociable/gplus/shortener');
				$shortenerLogin = Mage::getStoreConfig('besociable/gplus/shortener_login');
				$shortenerPass = Mage::getStoreConfig('besociable/gplus/shortener_api_key');
		
				$shortener = (string)$shortener;
		
				$link = Mage::helper('sociable')->shortenLink($url, $shortener, $shortenerLogin, $shortenerPass);
		
				try {
					$params = array();
					$params['link'] = $link;
					$params['name'] = $msg['name'];
					$params['description'] = $msg['description'];
					$params['image'] = $picture;
					$params['image_type'] = '';
						
					$img = substr($picture, strlen($picture)-3, strlen($picture));
					if($img == 'jpg'){
						$params['image_type'] = 'image/jpeg';
					}else if($img == 'gif'){
						$params['image_type'] = 'image/gif';
					}else if ($img == 'png'){
						$params['image_type'] = 'image/png';
					}
						
					$result = $this->postToGplus($message, $params);
		
					if($result){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('sociable')->__('Posted to Google+ page!'));
						$fullMessage = $message.'-----'.$msg['name'].'-----'.$msg['description'];
						$re = $this->saveLog($productId,$type,'gplus',$fullMessage);
						
						return true;
					}
		
		
				}catch (Exception $e){
					Mage::log($e->getMessage());
				}
			
		    }else {
		    	Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Maximum number of daily posts for Google+ reached!'));
		    }
		}else {
			
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Could not post to Google+!'));
		}
		return false;
	}
	public function handlePinterest($product, $picture, $type, $url){
		
		$productId = $product->getId();
		$pinterestEnable = Mage::getStoreConfigFlag('besociable/pinterest/enabled');
		$newEnabled = Mage::getStoreConfigFlag('besociable/pinterest/newproduct_enabled');
		$editedEnabled = Mage::getStoreConfigFlag('besociable/pinterest/editedproduct_enabled');
		$soldEnabled  = Mage::getStoreConfigFlag('besociable/pinterest/soldproduct_enabled');
		
		$maxPost = (int)Mage::getStoreConfig('besociable/pinterest/maxposts');
		if($maxPost == 0) {
			$maxPost = 9999999;
		}
		
		$notExceed = true;
		if($type != 'cron'){
			$currentPosts = $this->getDailyCount('pinterest');
			if($currentPosts >= $maxPost){
				$notExceed = false;
			}
		}
		
		
		$message = '';
		$shouldPost = false;
		if($type == 'new'){//New product
			if($newEnabled){
				$message = Mage::getStoreConfig('besociable/pinterest/newproductext');
				$shouldPost = true;
			}
		}else if($type == 'edit'){//edited product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/pinterest/editedproductext');
				$shouldPost = true;
			}
				
		}else if($type == 'sold'){//sold product
			if($editedEnabled){
				$message = Mage::getStoreConfig('besociable/pinterest/soldproductext');
				$shouldPost = true;
			}
		}else if($type == 'cron'){//random product
			
			$message = Mage::getStoreConfig('besociable/pinterest/randomtext');
			$shouldPost = true;
			
		}
	
		if($shouldPost){
			if($notExceed){
				$defaultMessage = (string)$product->getPinterestText();
				
				if($defaultMessage == ''){
					$defaultMessage = Mage::getStoreConfig('besociable/pinterest/defaulttext');
				}
				
				$msg = $this->getMessage($product, $defaultMessage);
				$shortener = Mage::getStoreConfig('besociable/pinterest/shortener');
				$shortenerLogin = Mage::getStoreConfig('besociable/pinterest/shortener_login');
				$shortenerPass = Mage::getStoreConfig('besociable/pinterest/shortener_api_key');
				
				$shortener = (string)$shortener;
				
				//Edit Mar 09 2013 -- Pinterest doesn't allow URL shorteners
				$link = $url;//Mage::helper('sociable')->shortenLink($url, $shortener, $shortenerLogin, $shortenerPass);
				
				try {
						
					$pinMessage = $message;
					$pinMessage .= ': '.$msg['name'];
					$pinMessage .= '>>>>>>'.$msg['description'];
				
					$result = $this->postToPinterest($pinMessage, $picture, $link);
				
					if($result){
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper('sociable')->__('Pinned to Pinterest!'));
						$re = $this->saveLog($productId,$type,'pinterest',$pinMessage);
							
						return true;
					}
				
				
				}catch (Exception $e){
					Mage::log($e->getMessage());
				}
			}else {
				Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Maximum number of daily pins for Pinterest reached!'));
			}
			
		}else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper('sociable')->__('Could not pin to Pinterest!'));
		}
		
		return false;
	}
	
	public function getImage($product){
		$image = '';
		if($product->getSociableImage() != 'no_selection' && $product->getSociableImage() != null && file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product'.$product->getSociableImage())){
			$image = $product->getSociableImage();
		}else if($product->getImage() != 'no_selection' && $product->getImage() != null && file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product'.$product->getImage())){
			$image = $product->getImage();
		}else if($product->getSmallImage() != 'no_selection' && $product->getSmallImage() != null && file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product'.$product->getSmallImage())){
			$image = $product->getSmallImage();
		}else if($product->getThumbnail() != 'no_selection' && $product->getThumbnail() != null && file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product'.$product->getThumbnail())){
			$image = $product->getThumbnail();
		}else {
			$image = '/magento.jpg';
		}
		
		return $image;
		
	}
	public function processProduct($productId, $type){
		
		$product = Mage::getModel('catalog/product')->load($productId);
		
		$url = $product->getUrlInStore();//$this->getFrontendUrl('').$product->getUrlPath();
		
		$result = array();
        if (!$product->getImage() || $product->getImage() == 'no_selection') {
            return $result;
        }
        $picturePath = '';
		$picture = $this->getImage($product);
		
		if($picture != ''){
		    /*
			$picturePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product'.$picture;
			$picture = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$picture;
			*/
		    $picture = Mage::helper('catalog/image')->init($product, 'image')->resize(300)->__toString();
		}
		$facebookEnable = Mage::getStoreConfigFlag('besociable/facebook/enabled');
		if($facebookEnable){
			$re = $this->handleFacebook($product, $picture, $type, $url);
			
			if($re){
				$result['facebook'] = 'ok';
			}
		}
		
		$twitterEnable = Mage::getStoreConfigFlag('besociable/twitter/enabled');
		if($twitterEnable){
			$re = $this->handleTwitter($product, $picture, $picturePath, $type, $url);
			if($re){
				$result['twitter'] = 'ok';
			}
		}
		
		$gplusEnable = Mage::getStoreConfigFlag('besociable/gplus/enabled');
		if($gplusEnable){
			$re = $this->handleGplus($product, $picture, $type, $url);
			if($re){
				$result['gplus'] = 'ok';
			}
		}
		
		$pinterestEnable = Mage::getStoreConfigFlag('besociable/pinterest/enabled');
		if($pinterestEnable){
			$re = $this->handlePinterest($product, $picture, $type, $url);
			if($re){
				$result['pinterest'] = 'ok';
			}
		}
		
		return $result;
	}
	
	public function getMessage($product, $type){
		
		$msg = array();
		$metaTitle = $product->getMetaTitle();
		if((string)$metaTitle == ''){
			$metaTitle = $product->getName();
		}
		$shortDescription = $product->getShortDescription();
		$description = $product->getDescription();
		$sku = $product->getSku();
		
		$msg['name'] = $metaTitle;
		$msg['caption'] = $sku;
		$msg['description'] = '';
		
		if($type == 'title_short'){
			$msg['description'] = $shortDescription;
		}else if($type == 'short'){
			$msg['name'] = $shortDescription;
		}else if($type == 'title_full'){
			$msg['description'] = $description;
		}else if($type == 'title_full'){
			$msg['name'] = $description;
		}
		
		return $msg;
	}
	
	public function postToFacebook($link, $picture, $name, $caption, $description, $message){
		
		$appId = Mage::getStoreConfig('besociable/facebook/appid');
		$appSecret = Mage::getStoreConfig('besociable/facebook/appsecret');
		$pageId = Mage::getStoreConfig('besociable/facebook/pageid');
		
		
		$accessToken = Mage::getStoreConfig('besociable/facebook/access_token');
		
		if($appId != '' && $appSecret !='' && $pageId != ''){
			$facebook = new Facebook(array(
					'appId'  => $appId,
					'secret' => $appSecret,
			));
			
			$facebook->setAccessToken($accessToken);
				
			$user = $facebook->getUser();
			$error = false;
			if ($user) {
				try {
					//$logoutUrl = $facebook->getLogoutUrl();
					// Proceed knowing you have a logged in user who's authenticated.
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
								'link' => $link,
								'picture'=>$picture,
								'name' => $name,
								'caption' => $caption,
								'description' => $description,
								'message' => $message
						);
						$args['ref'] = 'fbwpp';
						
							
						$publish_result = $facebook->api('/' . $fan_page_info[0][2] . '/feed', 'POST', $args);
							
						Mage::log('facebook response begin');
						Mage::log($publish_result);
						Mage::log('facebook response end');
						
						if(isset($publish_result) && isset($publish_result['id'])){
							return true;
						}
					}
						
					return false;
			
			
					} catch (FacebookApiException $e) {
						return false;
						Mage::log($e->__toString());
					}
						
						
			}
			return false;
		}
		
		return false;
	}
	public function postToTwitter($status, $image = ''){
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
				
			
			$code = 0;
			if($image != ''){
				$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update_with_media'), array('status' => $status,'media[]' =>"@$image"),true,true);
			}else {
				$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array('status' => $status));
			}
			
			Mage::log('twitter response: '.$code);
				
			if ($code == 200) {
				return true;
			}
			return false;
		}

		return false;
		
	}
	public function postToPinterest($message, $image, $link){
		$email = Mage::getStoreConfig('besociable/pinterest/email');
		$pass = Mage::getStoreConfig('besociable/pinterest/password');
		$boardId = trim(Mage::getStoreConfig('besociable/pinterest/boardid'));
		
		$login = doConnectToPinterest($email, $pass);
		
		
		if (!$login)
		{
			$result = doPostToPinterest($message, $image, $link, $boardId);
		
			
			if($result == 'OK'){
				return true;
			}else {
				Mage::log('pinterest response begin');
				Mage::log('result: '.$result);
				Mage::log('message: '.$message);
				Mage::log('image: '.$image);
				Mage::log('link: '.$link);
				Mage::log('boardId: '.$boardId);
				Mage::log('pinterest response end');
			}
			return false;
		}
		
		return false;
		
	}
	public function postToGplus($message, $params){
		$email = Mage::getStoreConfig('besociable/gplus/email');
		$pass = Mage::getStoreConfig('besociable/gplus/password');
		$pageID = Mage::getStoreConfig('besociable/gplus/pageid');
		
		$postToProfile = Mage::getStoreConfigFlag('besociable/gplus/profile');
		
		$login = doConnectToGooglePlus2($email, $pass);
		if (!$login)
		{
			//$lnk = doGetGoogleUrlInfo2($params);
		
			
			$lnk = array(
					'link' => $params['link'],
					'fav' => '//s2.googleusercontent.com/s2/favicons?domain='.$_SERVER['HTTP_HOST'],
					'title' => $params['name'],
					'domain' => $_SERVER['HTTP_HOST'],
					'txt' =>  $params['description'],
					'img' => $params['image'],
					'imgType' => $params['image_type']//'image/jpeg'
			);
			
		
			if($postToProfile){
				$result = doPostToGooglePlus2($message, $lnk, '');
			}else {
				$result = doPostToGooglePlus2($message, $lnk, $pageID);
			}
			
			
			
			
			
			
			if($result == 'OK'){
				return true;
			}else {
				Mage::log('gplus response begin');
				Mage::log($result);
				
				Mage::log('gplus response end');
			}
			return false;
		} 
		
		return false;
	}
	
	public function saveLog($productId, $productType, $network, $message){
		$date = getdate(Mage::getModel('core/date')->timestamp(time()));
		$currentDate = $date['year'].$date['mon'].$date['mday'];
		
		$log = Mage::getModel('sociable/log');
		$log->setProductId($productId);
		$log->setProductType($productType);
		$log->setPostDate($currentDate);
		$log->setNetwork($network);
		$log->setMessage($message);
		
		$re = $log->save();
		
		return $re;
	}
	public function getDailyCount($network){
		$date = getdate(Mage::getModel('core/date')->timestamp(time()));
		$currentDate = $date['year'].$date['mon'].$date['mday'];
		
		$log = Mage::getModel('sociable/log')->getCollection()->addFieldToFilter('post_date', $currentDate)->addFieldToFilter('network', $network)->addFieldToFilter('product_type', array('neq'=>'cron'));
		
		return $log->count();
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

	public function getRandomProducts($numProducts){
		if($numProducts > 0){
// 			$collection = Mage::getModel('catalog/product');
// 			Mage::getModel('catalog/layer')->prepareProductCollection($collection);
// 			$collection->getSelect()->order('rand()');
// 			//$collection->addStoreFilter();
// 			$collection->setPage(1, $numProducts);
			
			$products = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('status', 1)//enabled
			->addAttributeToFilter('visibility', '4');	
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
			
			$products->getSelect()->order(new Zend_Db_Expr('RAND()'));
			$products->getSelect()->limit($numProducts);
			
			return $products;
		}
		return false;
		
	}
	
	public function checkRandomProducts($currentDate, $network){
		$collection = Mage::getModel('sociable/cron')->getCollection()
			->addFieldToFilter('post_date', $currentDate)
			//->addFieldToFilter('done', 0)
			->addFieldToFilter('network', $network);
		if($collection->count()){
			return true;
		}
		
		return false;
	}
	
	//For cron job
	public function processQueue(){
		$date = getdate(Mage::getModel('core/date')->timestamp(time()));
		$currentDate = $date['year'].$date['mon'].$date['mday'];
		
		$facebookEnable = Mage::getStoreConfigFlag('besociable/facebook/enabled');
		if($facebookEnable){
			//Check if we have already generated the random products
			$check = $this->checkRandomProducts($currentDate, 'facebook');
			
			if($check){//generated
				$collection = Mage::getModel('sociable/cron')->getCollection()
								->addFieldToFilter('post_date', $currentDate)
								->addFieldToFilter('done', 0)
								->addFieldToFilter('network', 'facebook');
				
				if($collection->count()){
					//process one product each run only
					$cron = $collection->getFirstItem();
					$productId = $cron->getProductId();
					
					$re = $this->processProduct($productId, 'cron');
					
					if(isset($re['facebook']) && $re['facebook']=='ok'){
						$cron->setDone(1);
						$cron->save();
					}
				}
				
			}else {
				$randomNumber = (int)Mage::getStoreConfig('besociable/facebook/randompost');
				$randomProducts = $this->getRandomProducts($randomNumber);
				
				if($randomProducts){
					
					foreach ($randomProducts as $item){
						$cron = Mage::getModel('sociable/cron')
								->setProductId($item->getId())
								->setPostDate($currentDate)
								->setNetwork('facebook')
								->setDone(false)
								->save();
					}
				}
			}
			
		}
		
		$twitterEnable = Mage::getStoreConfigFlag('besociable/twitter/enabled');
		if($twitterEnable){
			$check = $this->checkRandomProducts($currentDate, 'twitter');
				
			if($check){//generated
				$collection = Mage::getModel('sociable/cron')->getCollection()
								->addFieldToFilter('post_date', $currentDate)
								->addFieldToFilter('done', 0)
								->addFieldToFilter('network', 'twitter');
			
				if($collection->count()){
					//process one product each run only
					$cron = $collection->getFirstItem();
						
					$productId = $cron->getProductId();
						
					$re = $this->processProduct($productId, 'cron');
						
					if(isset($re['twitter']) && $re['twitter']=='ok'){
						$cron->setDone(1);
						$cron->save();
					}
				}
			
			}else {
				$randomNumber = (int)Mage::getStoreConfig('besociable/twitter/randompost');
				$randomProducts = $this->getRandomProducts($randomNumber);
				if($randomProducts){
					foreach ($randomProducts as $item){
						$cron = Mage::getModel('sociable/cron')
						->setProductId($item->getId())
						->setPostDate($currentDate)
						->setNetwork('twitter')
						->setDone(false)
						->save();
						;
					}
				}
			}
		}
		
		$gplusEnable = Mage::getStoreConfigFlag('besociable/gplus/enabled');
		if($gplusEnable){
			$check = $this->checkRandomProducts($currentDate, 'gplus');
				
			if($check){//generated
				$collection = Mage::getModel('sociable/cron')->getCollection()
								->addFieldToFilter('post_date', $currentDate)
								->addFieldToFilter('done', 0)
								->addFieldToFilter('network', 'gplus');
			
				if($collection->count()){
					//process one product each run only
					$cron = $collection->getFirstItem();
					$productId = $cron->getProductId();
						
					$re = $this->processProduct($productId, 'cron');
						
					if(isset($re['gplus']) && $re['gplus']=='ok'){
						$cron->setDone(1);
						$cron->save();
					}
				}
			
			}else {
				$randomNumber = (int)Mage::getStoreConfig('besociable/gplus/randompost');
				$randomProducts = $this->getRandomProducts($randomNumber);
				if($randomProducts){
					foreach ($randomProducts as $item){
						$cron = Mage::getModel('sociable/cron')
						->setProductId($item->getId())
						->setPostDate($currentDate)
						->setNetwork('gplus')
						->setDone(false)
						->save();
						;
					}
				}
			}
		}
		
		$pinterestEnable = Mage::getStoreConfigFlag('besociable/pinterest/enabled');
		if($pinterestEnable){
			$check = $this->checkRandomProducts($currentDate, 'pinterest');
				
			if($check){//generated
				$collection = Mage::getModel('sociable/cron')->getCollection()
								->addFieldToFilter('post_date', $currentDate)
								->addFieldToFilter('done', 0)
								->addFieldToFilter('network', 'pinterest');
			
				if($collection->count()){
					//process one product each run only
					$cron = $collection->getFirstItem();
					$productId = $cron->getProductId();
						
					$re = $this->processProduct($productId, 'cron');
						
					if(isset($re['pinterest']) && $re['pinterest']=='ok'){
						$cron->setDone(1);
						$cron->save();
					}
				}
			
			}else {
				$randomNumber = (int)Mage::getStoreConfig('besociable/pinterest/randompost');
				$randomProducts = $this->getRandomProducts($randomNumber);
				if($randomProducts){
					foreach ($randomProducts as $item){
						$cron = Mage::getModel('sociable/cron')
						->setProductId($item->getId())
						->setPostDate($currentDate)
						->setNetwork('pinterest')
						->setDone(false)
						->save();
						;
					}
				}
			}
		}
	}
}
	 