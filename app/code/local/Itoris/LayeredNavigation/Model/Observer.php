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
 * An observer class.
 */
class Itoris_LayeredNavigation_Model_Observer {

	/**
	 * Add module handle to the layout update.
	 *
	 * @param $observer
	 * @return mixed
	 * @throws Exception
	 */
	public function addLayeredNavigationHandle($observer) {
		
		if (!($observer->getAction() instanceof Mage_Catalog_CategoryController || $observer->getAction() instanceof Mage_CatalogSearch_ResultController)) {
			return;
		}

		/** @var $layout Mage_Core_Model_Layout */
		$layout = $observer->getLayout();
		$handles = $layout->getUpdate()->getHandles();

		if (!(in_array('catalog_category_layered', $handles) || in_array('catalogsearch_result_index', $handles))) {
			return;
		}

		try {

			/** @var $settings Itoris_LayeredNavigation_Model_Settings */
			$settings = Mage::getSingleton('itoris_layerednavigation/settings');

			if (!$settings->getEnabled()) {
				return;
			}

			if (!$this->getDataHelper()->isRegisteredAutonomous()) {
				return;
			}

			$layout->getUpdate()->addHandle('catalog_category_itoris_layerednavigation');

		} catch (Exception $e) {
			Mage::logException($e);
			throw $e;
		}
	}

	/**
	 * @return Itoris_LayeredNavigation_Helper_Data
	 */
	public function getDataHelper() {
		return Mage::helper('itoris_layerednavigation');
	}

}
?>