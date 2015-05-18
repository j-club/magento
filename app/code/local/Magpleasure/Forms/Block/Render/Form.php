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
 * Form Renderer
 */
class Magpleasure_Forms_Block_Render_Form extends Mage_Core_Block_Template
{
    /** @var array Elements */
    protected $_elements = array();

    protected $_attrs = array();

    const TEMPLATE_PATH = "forms/render/form.phtml";

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    /**
     * Retrieves form attributes
     *
     * @return string
     */
    public function getFormAttributes()
    {
        $str = array();
        foreach ($this->_attrs as $key=>$value){
            $str[] = "{$key}=\"{$value}\"";
        }
        return count($str) ? implode(" ", $str) : '';
    }


    /**
     * Add element
     *
     * @param Magpleasure_Forms_Model_Structure $field
     * @return Magpleasure_Forms_Block_Render_Form
     */
    public function addElement($field)
    {
        /** @var Magpleasure_Forms_Block_Render_Element $element  */
        $element = $this->getLayout()->createBlock('forms/render_element');
        $element->setFieldType($field->getType());
        $element->setField($field);
        $this->_elements[] = $element;
        return $this;
    }

    /**
     * Render elements
     *
     * @return string
     */
    public function renderElements()
    {
        $html = "";

        foreach ($this->_elements as $element){
            /** @var Magpleasure_Forms_Block_Render_Element $element */
            $html .= $element->toHtml()."\n";
        }

        return $html;
    }

    /**
     * 
     *
     * @param array $attrs
     * @return Magpleasure_Forms_Block_Render_Form
     */
    public function addAttributes($attrs)
    {
        foreach ($attrs as $key=>$value){
            $this->_attrs[$key] = $value;
        }
        return $this;
    }

    public function getSecurityValue()
    {

    }
}