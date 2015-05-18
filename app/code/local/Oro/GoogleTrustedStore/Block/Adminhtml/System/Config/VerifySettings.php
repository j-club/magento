<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_VerifySettings
    extends Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_Button
{
    /**
     * Return element html and configuration for the verify settings button
     * Loadsgoogletrustedstore/settings_js.phtml
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $url = $this->_getOnclickUrl($element,'verifyConfig');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'gts_verify',
                'label'     => Mage::helper('googletrustedstore')->__('Verify Settings'),
                'onclick'   => "verifyGoogleTrustedStoresSettings('{$url}')",
            ));
        $script = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('googletrustedstore/settings_js.phtml')
            ->toHtml();
        return $script.$button->toHtml();
    }
}
