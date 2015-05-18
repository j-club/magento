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

class Magpleasure_Forms_Model_Structure extends Mage_Core_Model_Abstract
{
    const TYPE_TEXT = 'field';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_FILE = 'file';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIOBOX = 'radiobox';

    protected $_form;

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
        $this->_init('forms/structure');
    }

    public function toJson(array $arrAttributes = array())
    {
        $arrData = $this->toArray($arrAttributes);
        $proceed = array();
        foreach ($arrData as $key => $value){
            $proceed[$key] = is_string($value) ? $this->_helper()->getCommon()->getCore()->htmlEscape($value) : $value;
        }
        $json = Zend_Json::encode($proceed);
        return $json;
    }

    /**
     * Is select type
     *
     * @return bool
     */
    public function isSelectType()
    {
        return in_array($this->getType(), array(
                                            self::TYPE_CHECKBOX,
                                            self::TYPE_RADIOBOX,
                                            self::TYPE_MULTISELECT,
                                            self::TYPE_DROPDOWN
                                          ));
    }

    /**
     * Is file type
     *
     * @return bool
     */
    public function isFileType()
    {
        return in_array($this->getType(), array(
                                            self::TYPE_FILE,
                                          ));
    }

    /**
     * Is additional setup required
     *
     * @return bool
     */
    public function hasAdditionalData()
    {
        return ($this->isFileType() || $this->isSelectType());
    }

    /**
     * Describe integer answer
     *
     * @param int $intAnswer
     * @return string
     */
    public function describeAnswer($intAnswer)
    {
        foreach ($this->getValues() as $value){
            if ($value['option_id'] == $intAnswer){
                return $value['title'];
            }
        }
        Mage::throwException("Unacceptable answer");
    }

    protected function _prepareSelect($value, $separator)
    {
        $answers = array();
        if (is_array($value)){
            foreach ($value as $key => $answer){
                if (is_numeric($key) || $key === "value"){
                    if ($answer !== 'other'){
                        if ($answer){
                            $answers[] = $this->describeAnswer($answer);
                        }
                    } else {
                        $answers[] = $value['other'];
                    }
                }
            }
        }
        return implode($separator, $answers);
    }

    /**
     * Prepare posted value to save
     *
     * @param string|array $value
     * @param string $separator
     * @return string
     */
    public function prepareValue($value, $separator = ", ")
    {
        if ($this->isSelectType()){
            return $this->_prepareSelect($value, $separator);
        }
        return $value;
    }

    /**
     * Form
     *
     * @return Magpleasure_Forms_Model_Form
     */
    public function getForm()
    {
        if (!$this->_form){
            /** @var $form Magpleasure_Forms_Model_Form */
            $form = Mage::getModel('forms/form')->load($this->getFormId());
            $this->_form = $form;
        }
        return $this->_form;
    }
}