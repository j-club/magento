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

class Magpleasure_Forms_Adminhtml_RubbishController extends Mage_Adminhtml_Controller_Action
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

    /**
     * Initial;ize forms
     * @return Magpleasure_Forms_Adminhtml_FormsController 
     */
    protected function _initForms()
    {
		$this->loadLayout()
			->_setActiveMenu('cms/forms')
			->_addBreadcrumb(Mage::helper('forms')->__('CMS'), Mage::helper('forms')->__('Forms'));
        
        return $this;
    }

    /**
     * Purge form
     * @param int|string $id
     * @return boolean
     */
    protected function _purge($id)
    {
        $form = Mage::getModel('forms/form')->load($id);
        if ($form->getId()){
            try{
                $form->purge();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Restore form
     * @param int|string $id
     * @return boolean
     */
    protected function _restore($id)
    {
        $form = Mage::getModel('forms/form')->load($id);
        if ($form->getId()){
            try{
                $form->restore();
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add Success message for future show
     * @param string $message Message to show
     * @param mixed $value Value to show in string
     */
    protected function _addSuccess($message, $value = null)
    {
        $message = $value ? $this->_helper()->__($message, $value) : $this->_helper()->__($message);
        Mage::getSingleton('core/session')->addSuccess($message);
    }

    /**
     * Add Error message for future show
     * @param string $message Message to show
     * @param mixed $value Value to show in string
     */
    protected function _addError($message, $value = null)
    {
        $message = $value ? $this->_helper()->__($message, $value) : $this->_helper()->__($message);
        Mage::getSingleton('core/session')->addError($message);
    }
    
    public function indexAction()
    {
        $this->_initForms();
        $this->renderLayout();    
        return $this;
    }

    public function purgeAction()
    {
        if ($id = $this->getRequest()->getParam('id'))
        {
            if ($this->_purge($id)){
                $this->_addSuccess("Form successfully purged");
            } else {
                $this->_addError("There is error when purging of the form");
            }
        }
        $this->_redirect("*/*/index");
    }

    public function restoreAction()
    {
        if ($id = $this->getRequest()->getParam('id'))
        {
            if ($this->_restore($id)){
                $this->_addSuccess("Form successfully restored");
            } else {
                $this->_addError("There is error when restoring of the form");
            }
        }
        $this->_redirect("*/*/index");
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/forms/bin');
    }
}
