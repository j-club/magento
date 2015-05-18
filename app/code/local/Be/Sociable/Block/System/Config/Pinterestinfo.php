<?php
/**
 * S2L Solutions <info@snowleopard2lion.com>
 *
 * @module: Sociable
 */
 class Be_Sociable_Block_System_Config_Pinterestinfo
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
    	$email = trim(Mage::getStoreConfig('besociable/pinterest/email'));
    	$pass = Mage::getStoreConfig('besociable/pinterest/password');
    	$boardId = trim(Mage::getStoreConfig('besociable/pinterest/boardid'));
    	
    	$msg = '<div style="padding-bottom: 1em;max-width: 800px;">';
    	
    	if($email != '' && $pass != ''){
    		$msg .= Mage::helper('sociable')->__('');
    		$msg .= Mage::helper('sociable')->__('Please <a href="%s">click here</a> to get the board info from your Pinterest account.',$this->getUrl('adminhtml/sociable/getBoards'));
    		 if($boardId != ''){
    		 	$msg .= Mage::helper('sociable')->__('<br>Please <a href="%s">click here</a> to make a test pin.',$this->getUrl('adminhtml/sociable/testpinterest'));
    		 }
    		
    	}   	
    	
    	
    	$msg .= '</div>';
        return $msg;
    }

    
}
