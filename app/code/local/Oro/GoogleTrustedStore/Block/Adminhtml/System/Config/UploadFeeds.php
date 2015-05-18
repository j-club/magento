<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_UploadFeeds
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
        $url = $this->_getOnclickUrl($element,'upload');
        $confirmationMessage = Mage::helper('googletrustedstore')->__('Are you sure you want to upload the feeds?');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'upload_now',
                'label'     => Mage::helper('googletrustedstore')->__('Upload feeds now'),
                'onclick'   => "if(confirm('".$confirmationMessage."')){setLocation('".$url."');}",
            ));
        return $button->toHtml();
    }
}
