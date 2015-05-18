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

class Magpleasure_Forms_Block_Adminhtml_Rubbish extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _getFormsUrl()
    {
        return $this->getUrl('forms_admin/adminhtml_forms/index');
    }

    public function __construct()
    {
        $this->_controller = 'adminhtml_rubbish';
        $this->_blockGroup = 'forms';
        $this->_headerText = Mage::helper('forms')->__('Rubbish Bin');
        parent::__construct();
        $this->_removeButton('add');

        $this->_addButton('to_forms', array(
            'label'     => Mage::helper('forms')->__("Back to Form Manager"),
            'onclick'   => 'setLocation(\'' . $this->_getFormsUrl() .'\')',
            'class'     => 'back',
        ));
    }
}
