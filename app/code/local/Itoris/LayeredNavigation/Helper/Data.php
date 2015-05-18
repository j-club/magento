<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_LAYEREDNAVIGATION
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
class Itoris_LayeredNavigation_Helper_Data extends Mage_Core_Helper_Data {

	public function isAdminRegistered() {
		try {
			return Itoris_Installer_Client::isAdminRegistered($this->getAlias());
		} catch (Exception $e) {
			$this->getAdminhtmlSession()->addError($e->getMessage());
			return false;
		}
	}

	public function isRegisteredAutonomous($website = null) {
		return Itoris_Installer_Client::isRegisteredAutonomous($this->getAlias(), $website);
	}

	public function registerCurrentStoreHost($sn) {
		return Itoris_Installer_Client::registerCurrentStoreHost($this->getAlias(), $sn);
	}

	public function isRegistered($website) {
		return Itoris_Installer_Client::isRegistered($this->getAlias(), $website);
	}

	public function tryRegister() {
		$this->request = Mage::app()->getRequest();
		if($this->request->isPost()){
			$sn = $this->request->getPost('sn', null);
			if($sn == null) {
				return false;
			}

			$sn = trim($sn);
			try{
				$response = Itoris_Installer_Client::registerCurrentStoreHost($this->getAlias(), $sn);
				if($response == 0){
					$this->getAdminhtmlSession()->addSuccess($this->__('The component has been registered!'));
					Mage::app()->cleanCache();
				}else{
					$this->getAdminhtmlSession()->addError($this->__('Invalid serial number!'));
				}
			}catch(Exception $e){
				$this->getAdminhtmlSession()->addError($e->getMessage());
			}
		}
	}

	public function getAlias() {
		return $this->alias;
	}

	public function getScope(Mage_Core_Controller_Request_Http $request) {
		if ($request->getParam('store')) {
			return 'store';
		} else if ($request->getParam('website')) {
			return 'website';
		} else {
			return 'default';
		}
	}

	public function getScopeId(Mage_Core_Controller_Request_Http $request) {
		if ($request->getParam('store')) {
			return Mage::app()->getStore($request->getParam('store'))->getId();
		} else if ($request->getParam('website')) {
			return Mage::app()->getWebsite($request->getParam('website'))->getId();
		} else {
			return 0;
		}
	}

	public function getWebsiteIdFromRequest() {
		$websiteCode = Mage::app()->getRequest()->getParam('website');
		$website = Mage::app()->getWebsite($websiteCode);
		if ($website) {
			return $website->getId();
		} else {
			return 0;
		}
	}

	public function getStoreIdFromRequest() {
		$storeCode = Mage::app()->getRequest()->getParam('store');
		$store = Mage::app()->getStore($storeCode);
		if ($store) {
			return $store->getId();
		} else {
			return 0;
		}
	}

	/**
	 * @return Mage_Adminhtml_Model_Session
	 */
	public function getAdminhtmlSession() {
		return Mage::getSingleton('adminhtml/session');
	}

	/**
	 * Update layered navigation state items.
	 * Some applied filter items can't be show as checkbox
	 * so they will be passed to the browser as hidden inputs.
	 *
	 * @param Mage_Catalog_Model_Layer_State $state
	 * @param $items
	 */
	public function initFilterItems(Mage_Catalog_Model_Layer_State $state, $items) {
		$filters = $state->getFilters();
		/** @var $item Mage_Catalog_Model_Layer_Filter_Item */
		foreach($items as $item) {
			/** @var $itemInState Mage_Catalog_Model_Layer_Filter_Item */
			foreach($filters as $itemInState) {
				if ($item->getFilter() == $itemInState->getFilter()
						&& $item->getValue() == $itemInState->getValue()) {
					$item->setInState(true);
					$itemInState->setOutputInCheckbox($this->getNotUseFilter() ? false : true);
				}
			}
		}
	}

	public function isEnabledThirdEngineSearch() {
		$config = Mage::getConfig()->getModuleConfig('Enterprise_Search');
		if ($config->active == 'true') {
			if (Mage::app()->getRequest()->getParam('q') && Mage::helper('enterprise_search')) {
				return Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation(false);
			}
		}
		return false;
	}

	public function setNotUseFilter($flag = true) {
		$this->notUseFilter = $flag;
		return $this;
	}

	public function getNotUseFilter() {
		return $this->notUseFilter;
	}

	/**
	 * @var Mage_Core_Controller_Request_Http
	 */
	protected $request;
	protected $notUseFilter = false;

	protected $alias = 'layered_navigation';

}
?>