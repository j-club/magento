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

class Magpleasure_Forms_Block_View_Content extends Magpleasure_Forms_Block_Abstract
{
    const TEMPLATE_PATH_FRONTEND = "forms/view/content.phtml";
    const TEMPLATE_PATH_EMAIL = "forms/email/content.phtml";

    protected $_post;

    protected $_pager;

    protected function _construct()
    {
        parent::_construct();

        if ($this->isEmail()){
            $this->setTemplate(self::TEMPLATE_PATH_EMAIL);
        } else {
            $this->setTemplate(self::TEMPLATE_PATH_FRONTEND);
        }

        $pid = $this->getRequest()->getParam('pid');
        if ($pid){
            /** @var $post Magpleasure_Forms_Model_List */
            $post = Mage::getModel('forms/list')->load($pid);
            $this->setPost($post);
        }
    }

    /**
     * Set Post
     *
     * @param Magpleasure_Forms_Model_List $value
     * @return Magpleasure_Forms_Block_View_Content
     */
    public function setPost(Magpleasure_Forms_Model_List $value)
    {
        $this->_post = $value;
        return $this;
    }

    /**
     * Post
     *
     * @return Magpleasure_Forms_Model_List
     */
    public function getPost()
    {
        return $this->_post;
    }

    public function getFields()
    {
        return $this->getForm()->getFields();
    }

    public function getFormName()
    {
        return $this->escapeHtml($this->getForm()->getName());
    }

    public function getPostData()
    {
        $postData = array();

        foreach ($this->getFields() as $field){
            if ($field->getDisplayInPost() || $this->isEmail()){
                $postData[] = array(
                    'value' => $this->getPost()->getValue($field->getId()),
                    'label' => $field->getQuestion(),
                );
            }
        }

        return $postData;
    }

    public function getHtmlId()
    {
        return "form-post-view-table-".$this->getPost()->getId();
    }

    public function isEmail()
    {
        return Mage::registry('is_email');
    }
}