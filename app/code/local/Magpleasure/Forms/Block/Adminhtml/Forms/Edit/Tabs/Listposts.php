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
 * List of Posts
 */
class Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Listposts
                extends Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Abstract
{



    /**
     * Render block HTML
     * @return String
     */
    protected function _toHtml()
    {
        $grid = $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_listposts_grid');
        if ($grid){
            return $grid->toHtml();
        }
        return '';
    }
}