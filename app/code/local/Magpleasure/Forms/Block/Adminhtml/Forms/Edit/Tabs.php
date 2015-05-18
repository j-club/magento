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

class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('forms_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_helper()->__('Properties'));
    }

    protected function _isActiveTab($id)
    {
        if ($tab = $this->getRequest()->getParam('tab')){
            return (str_replace("forms_tabs_", "", $tab) == $id);
        }
        return null;
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_general', array(
           'label'     => $this->_helper()->__('General'),
           'title'     => $this->_helper()->__('General'),
           'content'   => $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_general')->toHtml(),
           'active'    => $this->_isActiveTab('form_general'),
        ));

        $this->addTab('form_behaviour', array(
           'label'     => $this->_helper()->__('Behaviour'),
           'title'     => $this->_helper()->__('Behaviour'),
           'content'   => $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_behaviour')->toHtml(),
           'active'    => $this->_isActiveTab('form_behaviour'),
        ));

        if (Mage::registry('forms_data') && Mage::registry('forms_data')->getFormId() ) {

            $this->addTab('constructror', array(
               'label'     => $this->_helper()->__('Constructor'),
               'title'     => $this->_helper()->__('Constructor'),
               'content'   => $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_constructor')->toHtml(),
               'active'    => $this->_isActiveTab('constructror'),
            ));

            $this->addTab('list_posts', array(
               'label'     => $this->_helper()->__('List Posts'),
               'title'     => $this->_helper()->__('List Posts'),
               'content'   => $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_listposts')->toHtml(),
               'active'    => $this->_isActiveTab('list_posts'),
            ));
        }
        
        return parent::_beforeToHtml();
    }
}