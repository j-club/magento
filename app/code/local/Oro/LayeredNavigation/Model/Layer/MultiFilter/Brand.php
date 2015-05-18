<?php 
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_LayeredNavigation
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_LayeredNavigation_Model_Layer_MultiFilter_Brand extends
    Itoris_LayeredNavigation_Model_Layer_MultiFilter_Attribute
{
    
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'brand';
    }
    /**
     * @return Oro_LayeredNavigation_Model_Resource_Layer_MultiFilter_Attribute
     */
    protected function _getResource() {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('oro_layerednavigation/layer_multiFilter_brand');
        }
        return $this->_resource;
    }

    public function getName()
    {
       return Mage::helper('itoris_layerednavigation')->__('VENDORS');
    }

    protected function _getItemsData()
    {
        //$attribute = $this->getAttributeModel();
        $this->_requestVar = 'brand';

        $key = $this->getLayer()->getStateKey().'_'.$this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            //$options = $attribute->getFrontend()->getSelectOptions();
            
            $options = $this->_getResource()->getCount($this);
            $data = array();
            foreach ($options as $option) {
                $data[] = array(
                    'label' => $option['vendor_name'],
                    'value' => $option['vendor_id'],
                    'count' => $option['count'],
                );
            }
    
            $tags = array(
                Mage_Eav_Model_Entity_Attribute::CACHE_TAG.':'.$this->_requestVar
            );

            $tags = $this->getLayer()->getStateTags($tags);
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    protected function _getBrands()
    {
        if (is_null($this->_brands)) {
            $this->_brands = $this->_getResource()->getBrands();
        }
        return $this->_brands;
    }

    protected function _getOptionText($optionId)
    {
        if ($this->_getBrands()) {
            $brands = $this->_getBrands();
            if (isset($brands[$optionId])) {
                return $brands[$optionId]['vendor_name'];
            } else return '';
        }
        return '';
    }
}
