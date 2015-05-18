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

class Magpleasure_Forms_Model_System_Config_Source_Form_Redirect_Type
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

    public function toOptionArray()
    {
        $result = array();

        $result[] = array(
            'value' => Magpleasure_Forms_Model_Form::REDIRECT_SELF,
            'label' => $this->_helper()->__("Referrer Page"),
            'url_model' => 'form/url_back',
        );

        $result[] = array(
            'value' => Magpleasure_Forms_Model_Form::REDIRECT_FORM,
            'label' => $this->_helper()->__("Form Page"),
            'url_model' => 'form/url_form',
        );

        $result[] = array(
            'value' => Magpleasure_Forms_Model_Form::REDIRECT_CMS_PAGE,
            'label' => $this->_helper()->__("CMS Page"),
            'url_model' => 'form/url_cmspage',

        );

        $result[] = array(
            'value' => Magpleasure_Forms_Model_Form::REDIRECT_CATEGORY,
            'label' => $this->_helper()->__("Category"),
            'url_model' => 'form/url_categry',
        );

        $result[] = array(
            'value' => Magpleasure_Forms_Model_Form::REDIRECT_PRODUCT,
            'label' => $this->_helper()->__("Product"),
            'url_model' => 'form/url_product',
        );

        if ($this->_helper()->getCommon()->getMagento()->isModuleEnabled("Magpleasure_Blog")){
            $result[] = array(
                'value' => Magpleasure_Forms_Model_Form::REDIRECT_BLOG_PAGE,
                'label' => $this->_helper()->__("Blog Page"),
                'url_model' => 'form/url_blogpage',
            );
        }

        return $result;
    }
}
