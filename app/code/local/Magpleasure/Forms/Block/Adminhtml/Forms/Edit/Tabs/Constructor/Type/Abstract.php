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

class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Constructor_Type_Abstract extends Mage_Adminhtml_Block_Widget
{
    protected $_name = 'abstract';

    /**
     * Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('forms');
    }


    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Constructor_Field::FIELD_NAME;
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Constructor_Field::FIELD_ID;
    }

}
