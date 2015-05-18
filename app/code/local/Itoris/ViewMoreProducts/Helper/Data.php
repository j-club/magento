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
 * @package    ITORIS_VIEWMOREPRODUCTS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

  

class Itoris_ViewMoreProducts_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $alias = 'view_more';

	public function isAdminRegistered() {
		try {
			return Itoris_Installer_Client::isAdminRegistered($this->getAlias());
		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
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

	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Get store id by parameter from the request
	 *
	 * @return int
	 */
	public function getStoreId() {
		if (Mage::app()->getRequest()->getParam('store')) {
			return Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getId();
		}
		return 0;
	}

	/**
	 * Get website id by parameter from the request
	 *
	 * @return int
	 */
	public function getWebsiteId() {
		if (Mage::app()->getRequest()->getParam('website')) {
			return Mage::app()->getWebsite(Mage::app()->getRequest()->getParam('website'))->getId();
		}
		return 0;
	}

	/**
	 * Get settings
	 *
	 * @return Itoris_ViewMoreProducts_Model_Settings
	 */
	public function getSettings($backend = false) {
		/** @var $settingsModel Itoris_ViewMoreProducts_Model_Settings */
		$settingsModel = Mage::getSingleton('itoris_viewmoreproducts/settings');
		$productId = 0;
		if ($backend || !Mage::app()->getWebsite()->getId()) {
			$settingsModel->load($this->getWebsiteId(), $this->getStoreId(), $productId);
		} else {
			$settingsModel->load(Mage::app()->getWebsite()->getId(), Mage::app()->getStore()->getId(), $productId);
		}

		return $settingsModel;
	}

	public function getScopeData() {
		if ($this->getStoreId()) {
			return array(
				'scope'    => 'store',
				'scope_id' => $this->getStoreId(),
			);
		} elseif ($this->getWebsiteId()) {
			return array(
				'scope'    => 'website',
				'scope_id' => $this->getWebsiteId(),
			);
		} else {
			return array(
				'scope'    => 'default',
				'scope_id' => 0
			);
		}
	}

	public function isRegisteredFrontend() {
		return !Mage::app()->getStore()->isAdmin()
			&& $this->getSettings()->getEnabled()
			&& $this->isRegisteredAutonomous();
	}

	public function isRegisteredAdmin() {
		return Mage::app()->getStore()->isAdmin()
			&& $this->getSettings()->getEnabled()
			&& $this->isAdminRegistered();
	}
}

?>