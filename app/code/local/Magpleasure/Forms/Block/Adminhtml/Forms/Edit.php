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

class Magpleasure_Forms_Block_Adminhtml_Forms_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Form Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'forms';
        $this->_controller = 'adminhtml_forms';

        $this->_updateButton('save', 'label', $this->_helper()->__('Save'));
        $this->_updateButton('delete', 'label', $this->_helper()->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label'     => $this->_helper()->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);


        $this->_formScripts[] = "
//            function toggleEditor() {
//                if (tinyMCE.getInstanceById('before_form') == null) {
//                    tinyMCE.execCommand('mceAddControl', false, 'before_form');
//                } else {
//                    tinyMCE.execCommand('mceRemoveControl', false, 'before_form');
//                }
//                if (tinyMCE.getInstanceById('after_form') == null) {
//                    tinyMCE.execCommand('mceAddControl', false, 'after_form');
//                } else {
//                    tinyMCE.execCommand('mceRemoveControl', false, 'after_form');
//                }
//
//            }

            function saveAndContinueEdit(){


                editForm.submit($('edit_form').action+'back/edit/tab/'+forms_tabsJsTabs.activeTab.id+'/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('forms_data') && Mage::registry('forms_data')->getId() ) {
            return Mage::helper('forms')->__("Edit - %s", $this->getFormName(Mage::registry('forms_data')->getId()));
        } else {
            return Mage::helper('forms')->__("New Form");
        }
    }

    public function getFormName($formId = null)
    {
        if (!$formId){
            return;
        }
        $form = Mage::getModel('forms/form')->load($formId);
        return $form->getName();
    }
}