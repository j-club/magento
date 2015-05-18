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

class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_General extends Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Abstract
{
    protected function _isNew()
    {
        return !Mage::registry('forms_data')->getId();
    }

    protected function _getGenerateUrlKeyButtonHtml()
    {
        /** @var $button Mage_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');

        if ($button){
            $button->addData(array(
                'label' => $this->_helper()->__("Update"),
                'title' => $this->_helper()->__("Update"),
                'onclick' => "$('id').value = generateUrlKey($('name').value); return false;",
                'style'   => 'display: none;',
                'id'    => 'generate_form_id'

            ));
            return $button->toHtml()."
            <script type=\"text/javascript\">
            $('name').observe('blur', function(e){
                if (!$('id').value){
                    $('id').value = generateUrlKey($('name').value);
                    $('generate_form_id').style.display = 'inline';
                }
            });
            </script>
            ";
        }
        return "";
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('general', array('legend'=>$this->_helper()->__('General')));

        $fieldset->addField('name', 'text', array(
            'label'     => $this->_helper()->__('Name'),
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('id', 'text', array(
            'label'     => $this->_helper()->__('Form ID'),
            'required'  => true,
            'name'      => 'id',
            'style' => 'display: inline; width: 209px;',
            'class' => 'validate-identifier',
            'after_element_html' => $this->_isNew() ? $this->_getGenerateUrlKeyButtonHtml() : "",
        ));

        /** @var Magpleasure_Forms_Model_Form_Status $statuses  */
        $statuses = Mage::getSingleton('forms/form_status');

        $fieldset->addField('status', 'select',
            array(
                'name'      => 'status',
                'label'     => $this->_helper()->__('Status'),
                'values'    => $statuses->toOptionArray(),
            ));

        if (!Mage::app()->isSingleStoreMode()){
                        
            $fieldset->addField('stores', 'multiselect',
                        array(
                            'label'     => $this->_helper()->__('Visible In'),
                            'required'  => true,
                            'name'      => 'stores[]',
                            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                        ));

        }

        $fieldset->addField('layout', 'select',
            array(
                'label'     => $this->_helper()->__('Form Page Layout'),
                'name'      => 'layout',
                'values'    => Mage::getSingleton('page/source_layout')->toOptionArray(),
            ));


        $fieldset = $form->addFieldset('description', array('legend'=>$this->_helper()->__('Description')));

		try{
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $config->setData($this->_helper()->recursiveReplace(
                        '/forms_admin/',
                        '/'.(string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName').'/',
                        $config->getData()
                    )
                );
        } catch (Exception $ex){
            $config = null;
        }

        $fieldset->addField('submit_button_text', 'text',
                        array(
                            'label'     => $this->_helper()->__('Submit Button Text'),
                            'required'  => true,
                            'name'      => 'submit_button_text',
                        ));


        $fieldset->addField('before_form', 'editor',
                        array(
                            'name'      => 'before_form',
                            'label'     => Mage::helper('cms')->__('Before Form'),
                            'title'     => Mage::helper('cms')->__('Before Form'),
                            'required'  => false,
                            'config'    => $config,
                            'style'     => 'width:600px; height:200px;',
                        ));

        $fieldset->addField('after_form', 'editor',
                        array(
                            'label'     => $this->_helper()->__('After Form'),
                            'title'     => $this->_helper()->__('After Form'),
                            'required'  => false,
                            'name'      => 'after_form',
                            'config'    => $config,
                            'style'     => 'width:600px; height:200px;',
                        ));


        if ( Mage::getSingleton('adminhtml/session')->getFormsData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormsData());
            Mage::getSingleton('adminhtml/session')->setFormsData(null);
        } elseif ( Mage::registry('forms_data') ) {
            $form->setValues(Mage::registry('forms_data')->getData());
        }
        return parent::_prepareForm();
    }


}
