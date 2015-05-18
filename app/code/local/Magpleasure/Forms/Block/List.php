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

class Magpleasure_Forms_Block_List extends Magpleasure_Forms_Block_Abstract
{
    const TEMPLATE_PATH = "forms/list.phtml";

    protected $_listCollection;

    protected $_pager;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    /**
     * List Colleciton
     * 
     * @return Magpleasure_Forms_Model_Mysql4_List_Collection
     */
    public function getListCollection()
    {
        if (!$this->_listCollection){

            $collection = $this->getForm()->getListCollection();
            $this->getPager()->setCollection($collection);
            $this->_listCollection = $collection;
        }
        return $this->_listCollection;
    }

    /**
     * Pager
     *
     * @return Mage_Page_Block_Html_Pager
     */
    public function getPager()
    {
        if (!$this->_pager){
            $this->_pager = $this->getLayout()->createBlock('page/html_pager', "mp.forms.pager");
        }
        return $this->_pager;
    }

    /**
     * Rendered Pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($pager = $this->getPager()){

            return $pager->toHtml();
        }
        return "";
    }

    public function getHtmlId()
    {
        return "mp-forms-table";
    }

    public function getExcelLink()
    {
        ///TODO Excel export
        return '';
    }

    public function getCsvLink()
    {
        ///TODO CSV export
        return '';
    }

    public function getExportEnabled()
    {
        ///TODO Create option
        return false;
    }

    public function isClickable()
    {
        return !!$this->getForm()->getData('list_rows_responsive');
    }
}