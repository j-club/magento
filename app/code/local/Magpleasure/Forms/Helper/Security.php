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

class Magpleasure_Forms_Helper_Security extends Mage_Core_Helper_Abstract
{
    const SECURITY_DATA_KEY = 'mp_forms_security_keyring';

    /**
     * Helper
     *
     * @return Magpleasure_Forms_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('forms');
    }

    protected function _getCombinedId($formId, $objectName)
    {
        return $formId."_".$objectName;
    }

    protected function _generateKey()
    {
        $salt = "626262";
        return md5(time().$salt);
    }

    public function getSecureKey($formId, $objectName, $clear = false)
    {
        $session = $this->_helper()->getCustomerSession();
        $data = $session->getData(self::SECURITY_DATA_KEY) ? $session->getData(self::SECURITY_DATA_KEY) : array();
        $id = $this->_getCombinedId($formId, $objectName);
        if (isset($data[$id])){
            $key = $data[$id];
            if ($clear){
                unset($data[$id]);
                $session->setData(self::SECURITY_DATA_KEY, $data);
            }
        } else {
            if (!$clear){
                $key = $this->_generateKey();
                $data[$id] = $key;
                $session->setData(self::SECURITY_DATA_KEY, $data);
            } else {
                $key = false;
            }
        }

        return $key;
    }

    public function checkSecureKey($formId, $objectName, $key)
    {
        return $key == $this->getSecureKey($formId, $objectName, true);
    }
}