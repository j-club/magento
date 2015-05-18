<?php

class Unirgy_DropshipSellYours_Block_SellForm extends Mage_Core_Block_Template
{
    public function getSellPostUrl()
    {
        return $this->getUrl('udsell/index/sellPost', array('id'=>$this->getProduct()->getId()));
    }
    public function getProduct()
    {
        return $this->getParentBlock()->getProduct();
    }
    protected $_syForm;
    public function getSyForm()
    {
        if (null !== $this->_syForm) {
            return $this->_syForm;
        }
        $prod = $this->getProduct();
        $values = (array)@Mage::registry('sell_yours_data_'.$prod->getId());
        $mvData = (array)@$values['udmulti'];
        $fsIdx = 0;
        $this->_syForm = new Varien_Data_Form();
        $columnsConfig = Mage::getStoreConfig('udsell/form/fieldsets');
        if (!is_array($columnsConfig)) {
            $columnsConfig = Mage::helper('udropship')->unserialize($columnsConfig);
            if (is_array($columnsConfig)) {
            foreach ($columnsConfig as $fsConfig) {
            if (is_array($fsConfig)) {
                $fields = array();
                foreach (array('top_columns','bottom_columns','left_columns','right_columns') as $colKey) {
                if (isset($fsConfig[$colKey]) && is_array($fsConfig[$colKey])) {
                    $requiredFields = (array)@$fsConfig['required_fields'];
                    foreach ($fsConfig[$colKey] as $fieldCode) {
                        if (!$this->_isFieldApplicable($prod, $fieldCode, $fsConfig)) continue;
                        $field = array();
                        if (strpos($fieldCode, 'udmulti.') === 0) {
                            $field = $this->_getUdmultiField(substr($fieldCode, 8), $mvData);
                        }
                        if (!empty($field)) {
                            switch ($colKey) {
                                case 'top_columns':
                                    $field['is_top'] = true;
                                    break;
                                case 'bottom_columns':
                                    $field['is_bottom'] = true;
                                    break;
                                case 'right_columns':
                                    $field['is_right'] = true;
                                    break;
                                default:
                                    $field['is_left'] = true;
                                    break;
                            }
                            if (in_array($fieldCode, $requiredFields)) {
                                $field['required'] = true;
                            } else {
                                $field['required'] = false;
                                if (!empty($field['class'])) {
                                    $field['class'] = str_replace('required-entry', '', $field['class']);
                                }
                            }
                            $fields[] = $field;
                        }
                    }
                }}

                if (!empty($fields)) {
                    $fsIdx++;
                    $fieldset = $this->_syForm->addFieldset('group_fields'.$fsIdx,
                        array(
                            'legend'=>$fsConfig['title'],
                            'class'=>'fieldset-wide',
                    ));
                    $this->_addElementTypes($fieldset);
                    foreach ($fields as $field) {
                        if (!empty($field['input_renderer'])) {
                            $fieldset->addType($field['type'], $field['input_renderer']);
                        }
                        $formField = $fieldset->addField($field['id'], $field['type'], $field);
                        if (!empty($field['renderer'])) {
                            $formField->setRenderer($field['renderer']);
                        }
                        $formField->addClass('input-text');
                        if (!empty($field['required'])) {
                            $formField->addClass('required-entry');
                        }
                    }
                    $this->_prepareFieldsetColumns($fieldset);
                    $emptyForm = false;
                }
            }}}
            $this->_syForm->addValues($values);
        }
        return $this->_syForm;
    }
    public function getChildElementHtml($elem)
    {
        return $this->getSyForm()->getElement($elem)->toHtml();
    }
    public function getChildElement($elem)
    {
        return $this->getSyForm()->getElement($elem);
    }
    public function isHidden($elem)
    {
        return $this->getSyForm()->getElement($elem)->getIsHidden();
    }
    protected function _prepareFieldsetColumns($fieldset)
    {
        $elements = $fieldset->getElements()->getIterator();
        reset($elements);
        $bottomElements = $topElements = $lcElements = $rcElements = array();
        while($element=current($elements)) {
            if ($element->getIsBottom()) {
                $bottomElements[] = $element->getId();
            } elseif ($element->getIsTop()) {
                $topElements[] = $element->getId();
            } elseif ($element->getIsRight()) {
                $rcElements[] = $element->getId();
            } else {
                $lcElements[] = $element->getId();
            }
            next($elements);
        }
        $fieldset->setTopColumn($topElements);
        $fieldset->setBottomColumn($bottomElements);
        $fieldset->setLeftColumn($lcElements);
        $fieldset->setRightColumn($rcElements);
        reset($elements);
        return $this;
    }
    protected function _getUdmultiField($field, $mvData)
    {
        return Mage::helper('udprod/form')->getUdmultiField($field, $mvData);
    }
    protected function _isFieldApplicable($prod, $fieldCode, $fsConfig)
    {
        $result = true;
        $ult = @$fsConfig['fields_extra'][$fieldCode]['use_limit_type'];
        $lt = @$fsConfig['fields_extra'][$fieldCode]['limit_type'];
        if (!is_array($lt)) {
            $lt = explode(',', $lt);
        }
        if ($ult && !in_array($prod->getTypeId(), $lt)) {
            $result = false;
        }
        if (strpos($fieldCode, 'udmulti.') === 0
            && !Mage::helper('udropship')->isUdmultiActive()
        ) {
            $result = false;
        }
        if (strpos($fieldCode, 'stock_data.') === 0
            && Mage::helper('udropship')->isUdmultiActive()
        ) {
            $result = false;
        }
        return $result;
    }
    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
            $result = array(
                'price'    => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
                'weight'   => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_weight'),
                'gallery'  => Mage::getConfig()->getBlockClassName('udprod/vendor_product_gallery'),
                'image'    => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_image'),
                'boolean'  => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean'),
                'textarea' => Mage::getConfig()->getBlockClassName('udprod/vendor_product_wysiwyg'),
            );

            $events = array('adminhtml_catalog_product_edit_element_types', 'udsell_product_edit_element_types');
            foreach ($events as $event) {
                $response = new Varien_Object();
                $response->setTypes(array());
                Mage::dispatchEvent($event, array('response'=>$response));
                foreach ($response->getTypes() as $typeName=>$typeClass) {
                    $result[$typeName] = $typeClass;
                }
            }

            $this->_additionalElementTypes = $result;
        }
        return $this;
    }

    protected function _getAdditionalElementTypes()
    {
        $this->_initAdditionalElementTypes();
        return $this->_additionalElementTypes;
    }
    public function addAdditionalElementType($code, $class)
    {
        $this->_initAdditionalElementTypes();
        $this->_additionalElementTypes[$code] = Mage::getConfig()->getBlockClassName($class);
        return $this;
    }

    protected function _addElementTypes(Varien_Data_Form_Abstract $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }
}