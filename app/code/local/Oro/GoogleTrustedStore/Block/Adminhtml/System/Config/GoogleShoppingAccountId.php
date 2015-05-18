<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

/**
 * Custom renderer for for Google Shopping Account Id
 *
 */
class Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_GoogleShoppingAccountId
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if (!Mage::getSingleton('googletrustedstore/googleShoppingAdapter')->isActive()) {
            return parent::render($element);
        } else {
            return '';
        }
    }
}
