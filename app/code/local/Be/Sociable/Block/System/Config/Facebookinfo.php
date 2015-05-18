<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */
require_once Mage::getBaseDir('base').DS.'lib'.DS.'bsultils'.DS.'facebook.php';
 
class Be_Sociable_Block_System_Config_Facebookinfo
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Template path
     *
     * @var string
     */
    //protected $_template = 'paypal/system/config/payflowlink/info.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
    	$msg = '<div style="padding-bottom: 1em;max-width: 800px;">';
    	$appId = Mage::getStoreConfig('besociable/facebook/appid');
    	$appSecret = Mage::getStoreConfig('besociable/facebook/appsecret');
    	$currentAccessToken = Mage::getStoreConfig('besociable/facebook/access_token');
    	if($appId != '' && $appSecret != ''){
    		$facebook = new Facebook(array(
    				'appId'  => $appId,
    				'secret' => $appSecret,
    		));
    		
    		
     		$merge = $appId.'|'.$appSecret;
     		$accessToken = $facebook->getAccessToken();
     		if($accessToken != $merge && $accessToken != $currentAccessToken){
     			Mage::getConfig()->saveConfig('besociable/facebook/access_token', $accessToken);
     			$facebook->setAccessToken($accessToken);
     		}else {
     			$facebook->setAccessToken($currentAccessToken);
     		}
    		
    		
    		
    		$user = $facebook->getUser();
    		if ($user) {
    			$loginUrl = $facebook->getLoginUrl(array('scope'=>array('manage_pages','publish_stream')));
    			
    			$msg .= Mage::helper('sociable')->__('<span style="color: green;">You have successfully authorized with facebook.</span>');
    			$msg .= Mage::helper('sociable')->__('<br>You can always <a href="%s">click here</a> to re-authorize your access to facebook again.',$loginUrl);
    			$msg .= Mage::helper('sociable')->__('<br>If you want to get a list of your pages, you can simply <a href="%s">click here</a>',$this->getUrl('adminhtml/sociable/getPages'));
    			$msg .= Mage::helper('sociable')->__('<br>Please <a href="%s">click here</a> to make a test post.',$this->getUrl('adminhtml/sociable/testfacebook'));
    			
    			/*
    			if(!$user){
    				$loginUrl = $facebook->getLoginUrl(array('scope'=>array('manage_pages','publish_stream')));//, 'publish_actions', 'publish_stream'
    				$msg .= Mage::helper('sociable')->__('<strong style="color:red">Important: </strong>');
    				$msg .= Mage::helper('sociable')->__('You will need to <a href="%s">click here</a> to authorize your access to facebook for the first time.',$loginUrl);
    			}else {
    				
    			}
    			*/
    			
    			
    			
    		}else {
    			$loginUrl = $facebook->getLoginUrl(array('scope'=>array('manage_pages','publish_stream')));//, 'publish_actions', 'publish_stream'
    			$msg .= Mage::helper('sociable')->__('<strong style="color:red">Important: </strong>');
    			$msg .= Mage::helper('sociable')->__('You will need to <a href="%s">click here</a> to authorize your access to facebook for the first time.',$loginUrl);
    		}
    		
    	}
    	
    	$msg .= '</div>';
        return $msg;
    }

    /**
     * Get frontend url
     *
     * @param string $routePath
     * @return strting
     */
    public function getFrontendUrl($routePath)
    {
        if ($this->getRequest()->getParam('website')) {
            $website = Mage::getModel('core/website')->load($this->getRequest()->getParam('website'));
            $secure = Mage::getStoreConfigFlag(
                Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT,
                $website->getDefaultStore()
            );
            $path = $secure ?
                Mage_Core_Model_Store::XML_PATH_SECURE_BASE_LINK_URL :
                Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_LINK_URL;
            $websiteUrl = Mage::getStoreConfig($path, $website->getDefaultStore());
        } else {
            $secure = Mage::getStoreConfigFlag(
                Mage_Core_Model_Url::XML_PATH_SECURE_IN_FRONT
            );
            $websiteUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure);
        }

        return $websiteUrl . $routePath;
    }
}
