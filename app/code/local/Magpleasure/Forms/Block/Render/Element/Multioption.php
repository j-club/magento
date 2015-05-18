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
 * Multioption Element
 */
abstract class Magpleasure_Forms_Block_Render_Element_Multioption extends Magpleasure_Forms_Block_Render_Element_Abstract
{
    protected $_options;

    /**
     * Options
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->_options){
            $this->_options = array();
            $values = $this->getValues();
            if (is_array($values)){
                foreach ($this->getValues() as $value){
                    $this->_options[] = new Varien_Object($value);
                }
            }
        }
        return $this->_options;
    }

    public function getOtherId()
    {
        return $this->getInputId()."_other";
    }

    public function getOtherName()
    {
        return parent::getInputName()."[other]";
    }

}