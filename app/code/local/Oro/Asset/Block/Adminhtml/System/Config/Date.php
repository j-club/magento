<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset System Config Increment version Button Block
 */
class Oro_Asset_Block_Adminhtml_System_Config_Date extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Returns Element HTML
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = (string)$element->getValue();
        if (empty($value)) {
            return Mage::helper('oro_asset')->__('Never');
        }

        return Mage::app()->getLocale()->storeDate(null, $value, true);
    }
}
