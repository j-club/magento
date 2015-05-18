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

/**
 * Form Helper
 */
class Magpleasure_Forms_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_FORM_PARAM_KEY = 'form_id';

    /*
     * Recursively searches and replaces all occurrences of search in subject values replaced with the given replace value
     * @param string $search The value being searched for
     * @param string $replace The replacement value
     * @param array $subject Subject for being searched and replaced on
     * @return array Array with processed values
     */
    public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
            return $subject;

        foreach($subject as $key => $value)
            if(is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif(is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }


    /**
     * Default form param
     * @return string
     */
    public function getFormParamKey()
    {
        return self::DEFAULT_FORM_PARAM_KEY;
    }

    /**
     * Core session
     *
     * @return Mage_Core_Model_Session
     */
    public function getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Customer session
     *
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Magpleasure Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    public function getCommon()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Notifier
     *
     * @return Magpleasure_Forms_Helper_Notifier
     */
    public function getNotifier()
    {
        return Mage::helper('forms/notifier');
    }

    /**
     * Security
     *
     * @return Magpleasure_Forms_Helper_Security
     */
    public function getSecurity()
    {
        return Mage::helper('forms/security');
    }


}