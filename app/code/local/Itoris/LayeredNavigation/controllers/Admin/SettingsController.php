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

/**
 * Settings controller.
 */
class Itoris_LayeredNavigation_Admin_SettingsController extends Itoris_LayeredNavigation_Controller_Admin {

	/**
	 * Show the settings form.
	 */
	public function indexAction() {
		$this->loadLayout()
				->_addContent($this->getLayout()->createBlock('itoris_layerednavigation/admin_settings'))
				->_addLeft($this->getLayout()->createBlock('itoris_layerednavigation/admin_settings_switcher'))
				->_setActiveMenu('system/itoris_extensions/itoris_layerednavigation')
				->_title($this->__('IToris Extensions'))
				->_title($this->__('Layered Navigation'))
				->_title($this->__('Settings'))
				->renderLayout();
	}

	/**
	 * Save applied settings.
	 */
	public function saveAction() {
        $scope = $this->getDataHelper()->getScope($this->getRequest());
        $scopeId = $this->getDataHelper()->getScopeId($this->getRequest());

		$data = $this->getRequest()->getPost();
		$settings = $data['settings'];

        /** @var $model Itoris_LayeredNavigation_Model_Settings */
		$model = Mage::getModel('itoris_layerednavigation/settings');

		try {
			$model->save($settings, $scope, $scopeId);
			$this->_getSession()->addSuccess($this->__('Settings have been saved.'));
		} catch (Exception $e) {
            Mage::logException($e);
			$this->_getSession()->addError($this->__('Unable to save settings.'));
		}

		$this->_redirectReferer();
	}

	/**
	 * Check access of the current role to this controller.
	 *
	 * @return bool
	 */
	protected function _isAllowed() {
		/** @var $adminSession Mage_Admin_Model_Session */
		$adminSession = Mage::getSingleton('admin/session');
		return $adminSession->isAllowed('system/itoris_extensions/itoris_layerednavigation');
	}
}
?>