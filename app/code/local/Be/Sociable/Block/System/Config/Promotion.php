<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */
 class Be_Sociable_Block_System_Config_Promotion
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $msg = "";
        return $msg;
        $filename = 'http://bosua.zapto.org/1620/promotion.txt';
    	$data = file_get_contents($filename);
    	
    	$msg = '<div style="border: 1px solid #CCCCCC; margin-bottom: 10px;padding: 10px;">';
    	$msg .= $data;
    	$msg .= '</div>';
        return $msg;
    }

   
}
