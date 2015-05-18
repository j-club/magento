<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Remove scope label
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * Generates URL for onclick action
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $actionName
     * @return string
     */
    protected function _getOnclickUrl(Varien_Data_Form_Element_Abstract $element, $actionName)
    {
        $params = array();
        $configForm = $element->getForm()->getParent();
        if ($configForm->getScope() == 'websites') {
            $params['website_id'] = $configForm->getScopeId();
        } elseif ($configForm->getScope() == 'stores') {
            $params['store_id'] = $configForm->getScopeId();
        }
        return $this->getUrl('*/googletrustedstore_feed/'.$actionName, $params);
    }
}
