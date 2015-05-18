<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_LayeredNavigation
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_LayeredNavigation_Block_Layer_MultiFilter_Brand
    extends Itoris_LayeredNavigation_Block_Layer_MultiFilter_Attribute {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('itoris/layerednavigation/layer/filter.phtml');
        if ($this->getDataHelper()->isEnabledThirdEngineSearch()) {
            $this->_filterModelName = 'itoris_layerednavigation/enterprise_layer_multiFilter_attribute';
        } else {
            $this->_filterModelName = 'oro_layerednavigation/layer_multiFilter_brand';
        }
    }

    protected function _prepareFilter() {
        return $this;
    }
}
