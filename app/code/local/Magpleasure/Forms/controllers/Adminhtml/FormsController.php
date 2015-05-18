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

class Magpleasure_Forms_Adminhtml_FormsController extends Magpleasure_Common_Controller_Adminhtml_Action
{
    protected $_defaultValues = array(
        'add_data_to_email' => 1,
        'list_rows_responsive' => 1,
    );

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
			->_addBreadcrumb(Mage::helper('forms')->__('CMS'), Mage::helper('forms')->__('Form'));
        
        return $this;
    }

    /**
     * Delete form
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id)
    {
        $video = Mage::getModel('forms/form')->load($id);
        if ($video->getId()){
            try{
                $video->delete();
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

    public function newAction()
    {
        $this->_forward('edit');
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('forms/form')->load($id);

		if ($model->getId() || $id == 0) {

            if (!$model->getId()){
                $model->addData($this->_defaultValues);
            }


			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			Mage::register('forms_data', $model);

            $this->_initForms();

            # Load Wysiwyg on demand and Prepare layout
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }

			$this->_addBreadcrumb($this->_helper()->__('Edit Form'), $this->_helper()->__('Edit Form'));
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('forms/adminhtml_forms_edit'))
				 ->_addLeft($this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->_helper()->__('Form does not exist'));
			$this->_redirect('*/*/');
		}
	}

    /**
     * Decode checkbox value
     * Retrieves "1" or "0"
     *
     * @param mixed $value
     * @return int
     */
    protected function _processCheckboxData($value = null)
    {
        //TODO Process this issue

        return $value ? 1 : 0;
    }


    /**
     * Process Form Fields
     *
     * @param array $fields
     * @param null $formId
     */
    protected function _processFields($fields = array(), $formId = null)
    {
        if (is_array($fields)){
            foreach ($fields as $field){

                if ($field['is_delete']){
                    if ($strId = $field['field_id']){
                        $structure = Mage::getModel('forms/structure')->load($strId);
                        $structure->delete();
                    }
                    continue;
                }

                /** @var Magpleasure_Forms_Model_Structure $structure  */
                $structure = Mage::getModel('forms/structure')->load(@$field['field_id'], 'structure_id');
                $structure->setFormId($formId);
                unset($field['field_id']);
                unset($field['id']);


                $structure->addData($field);
                $structure->setDisplayOnFront( (@$field['display_on_front']) ? 1 : 0 );
                $structure->setDisplayInPost( (@$field['display_in_post']) ? 1 : 0 );
                $structure->setEnableOther( (@$field['enable_other']) ? 1 : 0 );

                if ($structure->isFileType()){
                    $values = $field['file'];
                    $structure->setValues(Zend_Json::encode($values));
                }
                if ($structure->isSelectType()){
                    $values = $field['values'];
                    $toSave = array();
                    foreach ($values as $value){
                        if (!$value['is_delete']){
                            $toSave[] = $value;
                        }
                    }
                    $structure->setValues(Zend_Json::encode($toSave));
                }
                $structure->save();
            }
        }
    }

    /**
     * Check that id is uniq for form
     *
     * @param array $post
     * @return Magpleasure_Forms_Adminhtml_FormsController
     */
    protected function _checkUniqId(array $post)
    {
        if ($id = $post['id']){
            $checkMe = Mage::getModel('forms/form')->load($id, 'id');
            if ($checkMe->getFormId()){
                return $id."_copy";
            }
        }
        return $id;
    }

    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id');
        $back = $this->getRequest()->getParam('back');

        $post = $this->getRequest()->getPost();

        /** @var Magpleasure_Forms_Model_Form $form  */
        $form = Mage::getModel('forms/form');
        if ($id){
            $form->load($id);
        } else {
            # Check uniq form
            $post['id'] = $this->_checkUniqId($post);
        }

        $form->addData($post);
        $form->save();

        if (!$form->isLocked()) {
            if (isset($post['fields']) && is_array($post['fields'])) {
                $this->_processFields($post['fields'], $form->getFormId());
            }
        }

        $this->_addSuccess("Form successfully saved");

        $params = array('id'=>$form->getId());
        if ($tab = $this->getRequest()->getParam('tab')){
            $params['tab'] = $tab;
        }

        if ($back){
            $this->_redirect("*/*/edit", $params);
        } else {
            $this->_redirect("*/*/index");
        }
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id'))
        {
            if ($this->_delete($id)){
                $this->_addSuccess("Form successfully deleted");
            } else {
                $this->_addError("There is error when deleting of the form");
            }
        }
        $this->_redirect("*/*/index");
    }

    public function listgridAction()
    {
        $grid = $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_listposts_grid');
        if ($grid){
            Mage::register('forms_data', new Varien_Object(array(
                                              'form_id' => $this->getRequest()->getParam('form_id')
                                                          )), true);
            $this->getResponse()->setBody($grid->toHtml());
        }
    }

    protected function _changePostStatus($postId, $status)
    {
        if ($postId){
            try {
                $list = Mage::getModel('forms/list')->load($postId);
                $list->setStatus($status);
                $list->save();
                return true;
            } catch (Exception $e){
                $this->_helper()->getCommon()->getException()->logException($e);
                return false;
            }
        }
    }


    public function approvepostAction()
    {
        if ($id = $this->getRequest()->getParam('pid')){
            if ($this->_changePostStatus($id, Magpleasure_Forms_Model_List::TYPE_APPROVED)){
                $this->_addSuccess("Post successfully approved");
            } else {
                $this->_addError("Error then approving the post");
            }

        }
        $this->_redirectToPosts();
    }

    protected function _redirectToPosts()
    {
        $params = $this->getRequest()->getParams();
        $clear = array('list_status', 'form_key', 'key');

        /** @var $list Magpleasure_Forms_Model_List */
        $list = Mage::getModel('forms/list');

        if ($pid = $this->getRequest()->getParam('pid')){
            $list->load($pid);
        }

        $id = $list->getId() ? $list->getForm()->getId() : $this->getRequest()->getParam('id');

        $params['id'] = $id;
        $params['tab'] = 'forms_tabs_list_posts';

        foreach ($clear as $clearKey){
            if (isset($params[$clearKey])){
                unset($params[$clearKey]);
            }
        }


        /** @var $adminUrl Mage_Adminhtml_Model_Url */
        $adminUrl = Mage::getSingleton('adminhtml/url');
        $this->_redirectUrl($adminUrl->getUrl("forms_admin/adminhtml_forms/edit", $params));
    }

    public function rejectpostAction()
    {
        if ($id = $this->getRequest()->getParam('pid')){
            if ($this->_changePostStatus($id, Magpleasure_Forms_Model_List::TYPE_REJECTED)){
                $this->_addSuccess("Post successfully rejected");
            } else {
                $this->_addError("Error then rejecting the post");
            }
        }
        $this->_redirectToPosts();
    }

    public function deletepostAction()
    {
        if ($id = $this->getRequest()->getParam('pid')){
            if ($this->_deletePost($id)){
                $this->_addSuccess("Post successfully deleted");
            } else {
                $this->_addError("Error then deleting the post");
            }
        }
        $this->_redirectToPosts();
    }

    protected function _deletePost($postId)
    {
        if ($postId){
            try {
                $list = Mage::getModel('forms/list')->load($postId);
                $list->delete();
                return true;
            } catch (Exception $e){
                $this->_helper()->getCommon()->getException()->logException($e);
                return false;
            }
        }
        return $this;
    }

    public function massPostStatusAction()
    {
        $listIds = $this->getRequest()->getParam('listIds');
        $status = $this->getRequest()->getParam('list_status');

        if (!is_array($listIds)){
            $this->_addError("Please select item(s)");
        } else {
            $success = 0;
            $error = 0;

            foreach ($listIds as $listId){
                if ($this->_changePostStatus($listId, $status)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success > 0){
                $this->_addSuccess("Status successfully changed for %s posts", $success);
            }
            if ($error > 0){
                $this->_addError("Can not change status for %s posts", $error);
            }
        }
        $this->_redirectToPosts();
    }

    public function massPostDeleteAction()
    {
        $listIds = $this->getRequest()->getParam('listIds');
        if (!is_array($listIds)){
            $this->_addError("Please select item(s)");
        } else {
            $success = 0;
            $error = 0;

            foreach ($listIds as $listId){
                if ($this->_deletePost($listId)){
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success > 0){
                $this->_addSuccess("%s posts successfully deleted", $success);
            }
            if ($error > 0){
                $this->_addError("Can not delete %s posts", $error);
            }
        }
        $this->_redirectToPosts();
    }


    /**
     * Retrieves data to export
     *
     * @return array
     */
    public function _prepareDataToExport()
    {
        $data = array();

        return $data;
    }


    /**
     * Prepare grid to export
     *
     * @param $grid
     * @return Magpleasure_Forms_Adminhtml_FormsController
     */
    protected function _prepareGrid($grid)
    {
        if ($id = $this->getRequest()->getParam('id')){
            $form = Mage::getModel('forms/form')->load($id);
            $grid->setForm($form);
        }
        return $this;
    }

    public function exportCsvAction()
    {
        $fileName = "posts.csv";
        /** @var Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Listposts_Grid $grid  */
        $grid = $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_listposts_grid');
        $this->_prepareGrid($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsv($fileName));
    }

    public function exportExcelAction()
    {
        $fileName = "posts.xml";
        /** @var Magpleasure_Forms_Block_Adminhtml_Forms_Edit_Tabs_Listposts_Grid $grid  */
        $grid = $this->getLayout()->createBlock('forms/adminhtml_forms_edit_tabs_listposts_grid');
        $this->_prepareGrid($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcel($fileName));
    }


    /**
     * Declare headers and content file in responce for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     *                              that case
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _prepareDownloadResponse(
        $fileName,
        $content,
        $contentType = 'application/octet-stream',
        $contentLength = null)
    {
        $session = Mage::getSingleton('admin/session');
        if ($session->isFirstPageAfterLogin()) {
            $this->_redirect($session->getUser()->getStartupPageUrl());
            return $this;
        }

        $isFile = false;
        $file   = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                $isFile         = true;
                $file           = $content['value'];
                $contentLength  = filesize($file);
            }
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->setHeader('Last-Modified', date('r'));

        if (!is_null($content)) {
            if ($isFile) {
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $ioAdapter = new Varien_Io_File();
                $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
                $ioAdapter->streamOpen($file, 'r');
                while ($buffer = $ioAdapter->streamRead()) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
                if (!empty($content['rm'])) {
                    $ioAdapter->rm($file);
                }
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/forms/forms');
    }
}
