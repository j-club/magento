<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset System Config Increment version Button Block
 */
class Oro_Asset_Block_Adminhtml_System_Config_Increment extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Returns Element HTML
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $url    = $this->getUrl('adminhtml/asset/increment');
        /** @var $button Mage_Adminhtml_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button', '.oro_asset_increase');
        $button->setData(array(
            'type'      => 'button',
            'label'     => Mage::helper('oro_asset')->__('Increase version'),
            'onclick'   => 'setLocation(\'' . $url . '\')'
        ));

        return $button->toHtml();
    }
}
