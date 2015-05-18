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

class Magpleasure_Forms_Model_System_Config_Source_List_Status extends Varien_Object
{
    /**
     * Form Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    /**
     * Options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            Magpleasure_Forms_Model_List::TYPE_PENDING => $this->_helper()->__('Pending'),
            Magpleasure_Forms_Model_List::TYPE_REJECTED => $this->_helper()->__('Rejected'),
            Magpleasure_Forms_Model_List::TYPE_APPROVED => $this->_helper()->__('Approved'),
        );
    }

    /**
     * Options
     *
     * @return array
     */
    public function getOptionsArray()
    {
        $options = array();
        foreach($this->toOptionArray() as $value=>$label){
            $options[] = array('value'=>$value, 'label'=>$label);
        }
        return $options;
    }
}
