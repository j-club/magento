<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_GenerateFeeds
    extends Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_Button
{
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $url = $this->_getOnclickUrl($element,'generate');
        $confirmationMessage = Mage::helper('googletrustedstore')->__('Are you sure you want to generate the feeds?');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'generate_now',
                'label'     => Mage::helper('googletrustedstore')->__('Generate feeds now'),
                'onclick'   => "if(confirm('".$confirmationMessage."')){setLocation('".$url."');}",
            ));
        return $button->toHtml();
    }
}
