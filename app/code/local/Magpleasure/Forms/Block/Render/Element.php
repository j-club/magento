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
 * Each Element
 */
class Magpleasure_Forms_Block_Render_Element extends Mage_Core_Block_Template
{
    const TEMPLATE_PATH = 'forms/render/element.phtml';

    /** @var Magpleasure_Forms_Block_Render_Element_Abstract */
    protected $_render;

    /**
     * @var Magpleasure_Forms_Model_Structure
     */
    protected $_field;


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    public function setField($field)
    {
        $this->_field = $field;
        $this->addData($field->getData());
        if ($this->getFieldType()){
            $type = $this->getFieldType();
            $blockType = "forms/render_element_{$type}";
            $render = $this->getLayout()->createBlock($blockType);
            if ($render){
                $this->_render = $render;
                $render->addData($field->getData());
                $render->setFieldType( $type );
            }
        }
        return $this;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function renderField()
    {
        return $this->_render ? $this->_render->toHtml() : "";
    }

    public function getOtherName()
    {
        return $this->_render ? $this->_render->getOtherName() : "";
    }

    public function getOtherId()
    {
        return $this->_render ? $this->_render->getOtherId() : "";
    }

}