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

class Magpleasure_Forms_Model_Value extends Mage_Core_Model_Abstract
{
    protected $_field;

    /**
     * Forms
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('forms/value');
    }

    /**
     * Structure of value
     *
     * @return Magpleasure_Forms_Model_Structure
     */
    public function getField()
    {
        if (!$this->_field){
            /** @var $field Magpleasure_Forms_Model_Structure */
            $field = Mage::getModel('forms/structure')->load($this->getStructureId());
            $this->_field = $field;
        }
        return $this->_field;
    }

    /**
     * Form of value
     *
     * @return Magpleasure_Forms_Model_Form
     */
    public function getForm()
    {
        return $this->getField()->getForm();
    }
}