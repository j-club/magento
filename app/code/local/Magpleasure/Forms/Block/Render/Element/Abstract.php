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
 * Abstract Element
 */
abstract class Magpleasure_Forms_Block_Render_Element_Abstract extends Mage_Core_Block_Template
{
    const TEMPLATE_FOLDER = "forms/render/element/";
    const TEMPLATE_POSTFIX = ".phtml";

    /**
     * Helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function _helper()
    {
        return Mage::helper('forms');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $this->setTemplate(self::TEMPLATE_FOLDER.$this->getFieldType().self::TEMPLATE_POSTFIX);
        return $this;
    }

    public function getInputId()
    {
        return "field".$this->getStructureId();
    }

    public function getInputName()
    {
        return "field".$this->getStructureId();
    }



}