<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Adminhtml_Googletrustedstore_FeedController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Generate feed files
     */
    public function generateAction()
    {
        $session = $this->_getSession();

        try {
            Mage::getModel('googletrustedstore/feeder')->generateFeeds($this->_getStores(), true);
            $session->addSuccess(Mage::helper('googletrustedstore')->__('Feed files have been created.'));
        }
        catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('*/system_config/edit', $this->_getRedirectParams());
    }

    /**
     * Upload feed files
     */
    public function uploadAction()
    {
        $session = $this->_getSession();

        try {
            Mage::getModel('googletrustedstore/feeder')->uploadFeeds($this->_getStores(), true);
            $session->addSuccess(Mage::helper('googletrustedstore')->__('Feed files have been uploaded.'));
        }
        catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('*/system_config/edit', $this->_getRedirectParams());
    }

    /**
     * Verify settings
     * 
     * @deprecated in favor of verifyconfigAction
     */
    public function verifyAction()
    {
        $session = $this->_getSession();
        try {
            Mage::getModel('googletrustedstore/feeder')->verifySettings($this->_getStores());
            $session->addSuccess(Mage::helper('googletrustedstore')
                ->__('Settings are valid: FTP Credentials, file system permissions, cron settings.'));
        }
        catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('*/system_config/edit', $this->_getRedirectParams());
    }
    
    /**
     * This method will test the configuration provided by the Adminhtml System Configuration
     * for the GoogleTrustedStore module.
     * 
     * This method deprecates verifyAction().
     */
    
    public function verifyConfigAction()
    {

        try {
            $storeId = $this->getRequest()->getParam('store_id', Mage::app()->getDefaultStoreView()->getId());
            Mage::getModel('googletrustedstore/feeder')->verifyConfigurationSettings(
                $this->getRequest()->getPost('user'),
                $this->getRequest()->getPost('password'),
                $this->getRequest()->getPost('passive'),
                $storeId
            );

            $this->getResponse()->setBody(
                Mage::helper('googletrustedstore')->__(
                    'Settings are valid: FTP Credentials, file system permissions, cron settings.'
                )
            );
        }
        catch (Exception $e) {
            $this->getResponse()->setBody(
                Mage::helper('googletrustedstore')->__(
                    $e->getMessage()
                )
            );
        }
    }
    
    /**
     * Returns store list
     * 
     * @return array
     */
    protected function _getStores()
    {
        $stores = null;
        if ($store = $this->_getStore()) {
        	$stores = array($store);
        } elseif ($website = $this->_getWebsite()) {
        	$stores = $website->getStores();
        }
        return $stores;
    }
    
    /**
     * Returns website or null
     * 
     * @return mixed
     */
    protected function _getWebsite()
    {
        $website = null;
        $websiteId = $this->getRequest()->getParam('website_id');
        if ($websiteId) {
            $website = Mage::app()->getWebsite($websiteId);
        }
        return $website;
    }
    
    /**
     * Returns store or null
     * 
     * @return mixed
     */
    protected function _getStore()
    {
        $store = null;
        $storeId = $this->getRequest()->getParam('store_id');
        if ($storeId) {
            $store = Mage::app()->getStore($storeId);
        }
        return $store;
    }
    
    /**
     * Returns redirect params
     * 
     * @return array
     */
    protected function _getRedirectParams()
    {
        $params = array('section' => 'google');
        if ($website = $this->_getWebsite()) {
            $params['website'] = $website->getCode();
        }
        if ($store = $this->_getStore()) {
            $params['store'] = $store->getCode();
        }
        return $params;
    }
    
    /**
     * Returns session
     * 
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}
