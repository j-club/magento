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
 * Base controller class that checks registration status of the currently selected website.
 */
class Itoris_LayeredNavigation_Controller_Admin extends Mage_Adminhtml_Controller_Action {

	public function preDispatch() {
		parent::preDispatch();
		$this->getDataHelper()->tryRegister();
		if (!$this->getDataHelper()->isAdminRegistered()) {
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);

			$register = $this->getLayout()->createBlock('itoris_layerednavigation/admin_register');
			Mage::getSingleton('adminhtml/session')->addError($register->toHtml());

			$this->loadLayout();
			$this->renderLayout();
		}

		$websiteCode = $this->getRequest()->getParam('website');
		if (!empty($websiteCode)) {
			$website = Mage::app()->getWebsite($websiteCode);
			if (!$this->getDataHelper()->isRegistered($website)) {
				$error = '<b style="color:red">'
						 . $this->getDataHelper()->__('The extension is not registered for the website selected. '
								.'Please register it with an additional S/N.')
						 . '</b>';
				$this->_getSession()->addError($error);
			}
		}

		return $this;
	}

    /**
     * @return Itoris_LayeredNavigation_Helper_Data
     */
	protected function getDataHelper() {
		return Mage::helper('itoris_layerednavigation');
	}
}
?>