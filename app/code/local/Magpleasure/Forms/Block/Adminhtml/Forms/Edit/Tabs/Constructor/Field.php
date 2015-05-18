<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE-CE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE-CE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Magpleasure does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Magpleasure does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Magpleasure
 * @package    Magpleasure_Forms
 * @version    1.0.5
 * @copyright  Copyright (c) 2011-2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE-CE.txt
 */


class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Constructor_Field extends Magpleasure_Forms_Block_Adminhtml_Widget
{
    const FIELD_NAME = 'fields';

    const FIELD_ID = 'field';

    protected $_form;

    protected $_formInstance;

    protected $_values;

    protected $_itemCount = 1;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('forms/tabs/constructor/field.phtml');
    }

    public function getItemCount()
    {
        return $this->_itemCount;
    }

    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    /**
     * Get Form
     *
     * @return Magpleasure_Forms_Model_Form
     */
    public function getForm()
    {
        if (!$this->_formInstance) {
            if ($id = Mage::registry('forms_data')->getFormId()){
                $this->_formInstance = Mage::getModel('forms/form')->load($id);
            }
        }
        return $this->_formInstance;
    }

    public function setForm($form)
    {
        $this->_formInstance = $form;
        return $this;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return self::FIELD_NAME;
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return self::FIELD_ID;
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        ///TODO Do something with readonly fields
        return false;
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => $this->_helper()->__('Delete'),
                    'class' => 'delete delete-form-field '
                ))
        );

        $path = 'global/forms/fields/custom/groups';

        foreach (Mage::getConfig()->getNode($path)->children() as $group) {
            $this->setChild($group->getName() . '_field_type',
                $this->getLayout()->createBlock(
                    (string) Mage::getConfig()->getNode($path . '/' . $group->getName() . '/render')
                )
            );
        }

        return parent::_prepareLayout();
    }

    public function getFormId()
    {
        return $this->getForm()->getId(); 
    }

    public function getAddButtonId()
    {
        $buttonId = Mage::registry('mp_forms_constructor')->getChild('add_button')->getId();
        return $buttonId;
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_type',
                'class' => 'select select-form-field-type required-option-select'
            ))
            ->setName($this->getFieldName().'[{{id}}][type]')
            ->setOptions(Mage::getSingleton('forms/system_config_source_form_field_type')->toOptionArray());

        return $select->getHtml();
    }

    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_is_require',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{id}}][is_require]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    public function getTemplatesHtml()
    {
        $templates = $this->getChildHtml('text_field_type') . "\n" .
            $this->getChildHtml('file_field_type') . "\n" .
            $this->getChildHtml('select_field_type') . "\n" .
            $this->getChildHtml('date_field_type');

        return $templates;
    }
       

    public function getFields()
    {
        return $this->getForm()->getFields('DESC');
    }

    public function getJsonValue($value = array())
    {
        return Zend_Json::encode($value);
    }

    public function getMaxId()
    {
        return $this->getForm()->getMaxId();
    }
}
