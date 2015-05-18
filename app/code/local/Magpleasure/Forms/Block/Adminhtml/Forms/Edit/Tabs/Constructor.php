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

/**
 * Form Constructor
 */
class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Constructor
                extends Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Abstract
{

    const TEMPLATE_PATH = 'forms/tabs/constructor.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        Mage::register('mp_forms_constructor', $this, true);

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => $this->_helper()->__('Add New Field'),
                    'class' => 'add',
                    'id'    => 'add_new_field',
                ))
        );

        $this->setChild('fields_box',
            $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_constructor_field')
        );

        parent::_prepareLayout();
    }


    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getFieldsBoxHtml()
    {
        return $this->getChildHtml('fields_box');
    }

    public function getPreviewButtonHtml()
    {
        return '';
    }

}