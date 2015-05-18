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

class Magpleasure_Forms_Helper_View_Abstract extends Zend_View_Helper_FormElement
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
     * Retrieves other answer's input name
     *
     * @param $name
     * @return mixed|string
     */
    protected function _getOtherName($name)
    {
        $name = str_replace("[0]", "", $name);

        if (strpos($name, "[]") !== false){
            return str_replace("]", "other]", $name);
        } else {
            return $name."[other]";
        }
    }
}
 
