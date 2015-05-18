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
 * Block that shows price filter as a slider.
 *
 * @method Itoris_LayeredNavigation_Model_Layer_Filter_Price_Range getFilter()
 */
class Itoris_LayeredNavigation_Block_Layer_Filter_Price_Range
			extends Itoris_LayeredNavigation_Block_Layer_MultiFilter_Attribute {

	public function __construct() {
		parent::__construct();
		$this->setTemplate('itoris/layerednavigation/layer/filter/price/range.phtml');
		if ($this->getDataHelper()->isEnabledThirdEngineSearch()) {
			$this->_filterModelName = 'itoris_layerednavigation/enterprise_layer_filter_price_range';
		} else {
			$this->_filterModelName = 'itoris_layerednavigation/layer_filter_price_range';
		}
	}

	/**
	 * Returns maximal and minimal price of products in the current filtered collection.
	 *
	 * @return array
	 */
	public function getConfig() {

		$filter = $this->getFilter();
		$config = array(
			'min_price' => floor($filter->getMinPriceInt()),
			'max_price'	=> ceil($filter->getMaxPriceInt())
		);

		return $config;
	}

	/**
	 * Determine if the filter can be shown. Only if it has some items to show.
	 *
	 * @return bool
	 */
	public function canBeShown() {
		return $this->getFilter()->canBeShown() && parent::canBeShown();
	}
}
?>