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

require_once Mage::getModuleDir('controllers', 'Mage_Catalog').'/CategoryController.php';

/**
 * Category controller used to return to the browser html of layered navigation and
 * html of the content. Also return price range config if enabled.
 */
class Itoris_LayeredNavigation_CategoryController extends Mage_Catalog_CategoryController {

	public function viewAction() {
		$this->setFlag('', 'no-renderLayout', true);
		parent::viewAction();

		$response = array(
			'content_html' 				=> $this->getLayout()->getBlock('content')->toHtml(),
			'layered_navigation_html' 	=> $this->getLayout()->getBlock('catalog.itoris_leftnav')
					->setTemplate('itoris/layerednavigation/layer/view/content.phtml')->toHtml()
		);

		if ($priceRangeBlock = $this->getLayout()->getBlock('layer_filter_price_range')) {
			if ($priceRangeBlock->canBeShown()) {
				/** @var $priceRangeBlock Itoris_LayeredNavigation_Block_Layer_Filter_Price_Range */
				$response['price_range_config'] = $priceRangeBlock->getConfig();
			}
		}

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}

	/**
	 * Use this controller only to override the view action behaviour
	 * and only when this is post request with particular flag in it.
	 *
	 * @param $action
	 * @return bool
	 */
	public function hasAction($action) {
		if ($action != 'view') {
			return false;
		} else {
			return $this->getRequest()->isPost()
					&& $this->getRequest()->getPost('itoris_layerednavigation') == 'true';
		}
	}
}
?>