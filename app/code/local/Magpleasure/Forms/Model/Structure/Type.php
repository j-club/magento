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

class Magpleasure_Forms_Model_Structure_Type
{
    /**
     * Helper
     * 
     * @return Magpleasure_Forms_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('forms');
    }

    public function toOptionArray()
    {
        return array(
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_TEXT,
                'label' => $this->_helper()->__('Text'),
            ),
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_TEXTAREA,
                'label' => $this->_helper()->__('Text Area'),
            ),
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_DROPDOWN,
                'label' => $this->_helper()->__('Drop Down Select'),
            ),
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_MULTISELECT,
                'label' => $this->_helper()->__('Multiple Select'),
            ),
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_CHECKBOX,
                'label' => $this->_helper()->__('Check Boxes'),
            ),
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_RADIOBOX,
                'label' => $this->_helper()->__('Radio Boxes'),
            ),
            array(
                'value' => Magpleasure_Forms_Model_Structure::TYPE_FILE,
                'label' => $this->_helper()->__('File'),
            ),
        );
    }
}