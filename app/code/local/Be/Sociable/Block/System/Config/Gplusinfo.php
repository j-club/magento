<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */
 class Be_Sociable_Block_System_Config_Gplusinfo
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
    	$email = trim(Mage::getStoreConfig('besociable/gplus/email'));
    	$pass = Mage::getStoreConfig('besociable/gplus/password');
    	
    	
    	$msg = '<div style="padding-bottom: 1em;max-width: 800px;">';
    	
    	if($email != '' && $pass != ''){
    		//$msg .= Mage::helper('sociable')->__('<strong style="color:blue">Notice: </strong>');
    		$msg .= Mage::helper('sociable')->__('Please <a href="%s">click here</a> to make a test post.',$this->getUrl('adminhtml/sociable/testgplus'));
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
