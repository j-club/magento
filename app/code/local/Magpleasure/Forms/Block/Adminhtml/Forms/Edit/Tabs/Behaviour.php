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

class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Behaviour extends Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Abstract
{
    protected function _getAdditionalJavascript()
    {
        return "
        <script type=\"text/javascript\">
            (function( $ ) {
                var getTrByElement = function(el, num){
                    var number = (typeof(num) != 'undefined') ? num : 1;
                    var limit = 5;

                    if (el && (typeof (el.parentNode) != 'undefined')){
                        var tr = el.parentNode;
                        if (number < limit){
                            if ($(tr).prop('tagName') == 'TR'){
                                return tr;
                            } else {
                                return getTrByElement(tr, number + 1);
                            }
                        } else {
                            return false;
                        }
                    }
                };

                var updateRedirectTypeRelatedFields = function(){
                    $('#redirect_type').each(function(i){
                        var tr = getTrByElement(this);
                        $(tr).css('display', 'inline');
                    });
                    var enabledId = $('#redirect_type').val() + '_entity_id';
                    $('#forms_tabs_form_behaviour_content input.redirect_type_related').each(function(index){
                        var tr = getTrByElement(this);

                        if (this.id == enabledId){
                            $(tr).css('display', 'inline');
                        } else {
                            $(tr).css('display', 'none');
                        }
                    });
                };

                $(function() {
                    updateRedirectTypeRelatedFields();

                    $('#redirect_type').select(function(){
                        updateRedirectTypeRelatedFields();
                    });
                });

            })( jQuery );
    </script>
        ";
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


        $fieldset = $form->addFieldset('options', array('legend'=>$this->_helper()->__('Options')));

        $fieldset->addField('guest_can_post', 'select',
                        array(
                            'label'     => $this->_helper()->__('Guest Can Post'),
                            'name'      => 'guest_can_post',
                            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
                        ));

        $fieldset->addField('approve_require', 'select',
                        array(
                            'label'     => $this->_helper()->__('Approve Require'),
                            'name'      => 'approve_require',
                            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
                        ));

        $fieldset = $form->addFieldset('submit_redirect', array('legend'=>$this->_helper()->__('After Submit Redirect')));

        $fieldset->addType('dropdown', 'Magpleasure_Common_Block_System_Entity_Form_Element_Dropdown');
        $fieldset->addType('ajax_dropdown', 'Magpleasure_Common_Block_System_Entity_Form_Element_Ajax_Dropdown');


        $additionalJavaScript = $this->_getAdditionalJavascript();

        $fieldset->addField('redirect_type', 'dropdown',
            array(
                'label'     => $this->_helper()->__('Redirect To'),
                'name'      => 'redirect_type',
                'values'    => Mage::getSingleton('forms/system_config_source_form_redirect_type')->toOptionArray(),
                'after_element_html' => "{$additionalJavaScript}",
            ));

        $fieldset->addField('form_entity_id', 'ajax_dropdown',
            array(
                'label'     => $this->_helper()->__('Form'),
                'name'      => 'form_entity_id',
                'class'     => 'redirect_type_related',
                'note'      => $this->_helper()->__("Please type to select value"),
                'data_source' => array(
                    'filter_field' => 'name',
                    'sort_field' => 'name',
                    'sort_direction' => 'ASC',
                    'entity_id_pattern' => "{{form_id}}",
                    'entity_label_pattern' => "{{name}}",
                    'model' => 'forms/form',
                ),
            ));

        $fieldset->addField('cms_page_entity_id', 'ajax_dropdown',
            array(
                'label'     => $this->_helper()->__('CMS Page'),
                'name'      => 'cms_page_entity_id',
                'class'     => 'redirect_type_related',
                'note'      => $this->_helper()->__("Please type to select value"),
                'data_source' => array(
                    'filter_field' => 'title',
                    'sort_field' => 'title',
                    'sort_direction' => 'ASC',
                    'entity_id_pattern' => "{{page_id}}",
                    'entity_label_pattern' => "{{title}}",
                    'model' => 'cms/page',
                ),
            ));

        $fieldset->addField('product_entity_id', 'ajax_dropdown',
            array(
                'label'     => $this->_helper()->__('Product'),
                'name'      => 'product_entity_id',
                'note'      => $this->_helper()->__("Please type to select value"),
                'class'     => 'redirect_type_related',
                'data_source' => array(
                    'filter_field' => 'name',
                    'sort_field' => 'name',
                    'sort_direction' => 'ASC',
                    'entity_id_pattern' => "{{entity_id}}",
                    'entity_label_pattern' => $this->_helper()->__("{{name}}, SKU: {{sku}}"),
                    'model' => 'catalog/product',
                    'methods' => array(
                        array('method' => 'addAttributeToSelect', 'parameters' => array('name')),
                    ),
                ),
            ));

        $fieldset->addField('category_entity_id', 'ajax_dropdown',
            array(
                'label'     => $this->_helper()->__('Category'),
                'name'      => 'category_entity_id',
                'note'      => $this->_helper()->__("Please type to select value"),
                'class'     => 'redirect_type_related',
                'data_source' => array(
                    'filter_field' => 'name',
                    'sort_field' => 'name',
                    'sort_direction' => 'ASC',
                    'entity_id_pattern' => "{{entity_id}}",
                    'entity_label_pattern' => $this->_helper()->__("{{name}}"),
                    'model' => 'catalog/category',
                    'methods' => array(
                        array('method' => 'addAttributeToSelect', 'parameters' => array('name')),
                    ),
                ),
            ));

        if ($this->_helper()->getCommon()->getMagento()->isModuleEnabled("Magpleasure_Blog")){
            $fieldset->addField('blog_post_entity_id', 'ajax_dropdown',
                array(
                    'label'     => $this->_helper()->__('Blog Post'),
                    'name'      => 'blog_post_entity_id',
                    'note'      => $this->_helper()->__("Please type to select value"),
                    'class'     => 'redirect_type_related',
                    'data_source' => array(
                        'filter_field' => 'title',
                        'sort_field' => 'title',
                        'sort_direction' => 'ASC',
                        'entity_id_pattern' => "{{post_id}}",
                        'entity_label_pattern' => "{{title}}",
                        'model' => 'mpblog/post',
                    ),
                ));
        }

        $fieldset = $form->addFieldset('list', array('legend'=>$this->_helper()->__('Frontend List')));

        $fieldset->addField('list_rows_responsive', 'select',
            array(
                'label'     => $this->_helper()->__('Show Rows Content'),
                'name'      => 'list_rows_responsive',
                'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            ));

        $fieldset = $form->addFieldset('notification', array('legend'=>$this->_helper()->__('Email Notification')));

        $fieldset->addField('add_data_to_email', 'select',
            array(
                'label'     => $this->_helper()->__('Add Post Data to Notification Email'),
                'name'      => 'add_data_to_email',
                'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            ));


        if ( Mage::getSingleton('adminhtml/session')->getFormsData() ){
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormsData());
            Mage::getSingleton('adminhtml/session')->setFormsData(null);
        } elseif ( Mage::registry('forms_data') ) {
            $form->setValues(Mage::registry('forms_data')->getData());
        }
        return parent::_prepareForm();
    }


}
