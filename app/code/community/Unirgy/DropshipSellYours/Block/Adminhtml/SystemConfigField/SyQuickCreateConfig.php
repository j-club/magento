<?php

class Unirgy_DropshipSellYours_Block_Adminhtml_SystemConfigField_SyQuickCreateConfig extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udsell/system/form_field/sy_quick_create_config.phtml');
        }
    }
    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $this->setFieldName($element->getName());
        $html = $this->_toHtml();
        return $html;
    }

    public function getFieldValue()
    {
        return $this->getElement()->getValue();
    }

    public function setFieldName($fName)
    {
        $this->resetIdSuffix();
        return $this->setData('field_name', $fName);
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : '');
    }

    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if (null === $this->_idSuffix) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }

    public function getAddButtonId()
    {
        return $this->suffixId('addBtn');
    }

}
