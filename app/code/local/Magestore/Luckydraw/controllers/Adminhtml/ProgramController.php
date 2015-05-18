<?php

class Magestore_Luckydraw_Adminhtml_ProgramController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction(){
		$this->loadLayout()
			->_setActiveMenu('luckydraw/program')
			->_addBreadcrumb($this->__('Luckydraw'),$this->__('Program Manager'));
		return $this;
	}

	public function indexAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$this->_initAction()
			->_title($this->__('Lucky Draw'))
			->_title($this->__('Manage Program'))
			->renderLayout();
	}

	public function editAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$id	 = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('luckydraw/program')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data))
				$model->setData($data);
			$this->_title($this->__('Lucky Draw'))->_title($this->__('Manage Program'));
			if ($model->getId()) $this->_title($model->getName());
			else $this->_title($this->__('New Program'));

			Mage::register('program_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('luckydraw/program');

			$this->_addBreadcrumb($this->__('Program Manager'),$this->__('Program Manager'));
			$this->_addBreadcrumb($this->__('Program News'),$this->__('Program News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('luckydraw/adminhtml_program_edit'))
				->_addLeft($this->getLayout()->createBlock('luckydraw/adminhtml_program_edit_tabs'));

			$this->renderLayout();
			if (Mage::getSingleton('adminhtml/session')->getProgramData())
				Mage::getSingleton('adminhtml/session')->setProgramData(null);
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Program does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if ($data = $this->getRequest()->getPost()) {
			if (isset($data['award_image']['delete']) && $data['award_image']['delete'] == 1){
				$data['award_image'] = '';
			} elseif (isset($data['award_image']) && is_array($data['award_image'])){
				$data['award_image'] = $data['award_image']['value'];
				if ($pos = strrpos($data['award_image'],'/')) $data['award_image'] = substr($data['award_image'],$pos+1);
			}
			if (isset($_FILES['award_image']['name']) && $_FILES['award_image']['name'] != ''){
				try {
					$uploader = new Varien_File_Uploader('award_image');
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(false);
					
					$path = Mage::getBaseDir('media').DS.'luckydraw'.DS.'program'.DS;
					$result = $uploader->save($path,$_FILES['award_image']['name']);
					$data['award_image'] = $result['file'];
				} catch (Exception $e){
					$data['award_image'] = $_FILES['award_image']['name'];
				}
			}
			
			$model = Mage::getModel('luckydraw/program');
			$data = $this->_filterDateTime($data,array('start_time','end_time'));
			$model->setData($data)
				->setData('time_included_gmt',true)
				->setId($this->getRequest()->getParam('id'));
			try {
				$model->refreshDatetimeObject();
			} catch (Exception $e){}
			
			try {
				if ($model->getCreatedTime() == NULL) $model->setCreatedTime(now());
				$model->save();
				
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setProgramData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find program to save'));
		$this->_redirect('*/*/');
	}
 
	public function deleteAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if ($this->getRequest()->getParam('id') > 0){
			try {
				$model = Mage::getModel('luckydraw/program');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction() {
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$luckydrawIds = $this->getRequest()->getParam('luckydraw');
		if(!is_array($luckydrawIds)){
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select program(s)'));
		}else{
			try {
				foreach ($luckydrawIds as $luckydrawId) {
					$luckydraw = Mage::getModel('luckydraw/program')->load($luckydrawId);
					$luckydraw->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($luckydrawIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
  
	public function exportCsvAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$fileName   = 'luckydraw.csv';
		$content	= $this->getLayout()->createBlock('luckydraw/adminhtml_program_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName,$content);
	}

	public function exportXmlAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$fileName   = 'luckydraw.xml';
		$content	= $this->getLayout()->createBlock('luckydraw/adminhtml_program_grid')->getXml();
		$this->_prepareDownloadResponse($fileName,$content);
	}
	
	/** Code of Program tab */
	public function codeAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$gridBlock = $this->getLayout()->createBlock('luckydraw/adminhtml_program_edit_tab_code','luckydraw.program.code');
		$this->getResponse()->setBody($gridBlock->toHtml());
	}
	public function codeGridAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$this->codeAction();
	}
	
	/** Prize of Program tab */
	public function prizeAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$gridBlock = $this->getLayout()->createBlock('luckydraw/adminhtml_program_edit_tab_prize','luckydraw.program.prize');
		$this->getResponse()->setBody($gridBlock->toHtml());
	}
	public function prizeGridAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$this->prizeAction();
	}
	
	/** Actions */
	protected function _initProgram(){
		$programId = $this->getRequest()->getParam('id');
		$program = Mage::getModel('luckydraw/program')->load($programId);
		if ($program->getId())
			return $program;
		Mage::getSingleton('adminhtml/session')->addError($this->__('Program does not exist'));
		$this->_redirect('*/*/edit', array('id' => $programId));
		return null;
	}
	
	public function pauseAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if (!$program = $this->_initProgram()) return;
		if ($program->getStatus() < Magestore_Luckydraw_Model_Program::STATUS_COMPLETE){
			$program->setStatus(Magestore_Luckydraw_Model_Program::STATUS_PAUSED);
			try {
				$program->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully paused'));
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot pause this program'));
		}
		return $this->_redirect('*/*/edit',array('id' => $program->getId()));
	}
	
	public function resumeAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if (!$program = $this->_initProgram()) return;
		if ($program->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_PAUSED){
			$program->setStatus(Magestore_Luckydraw_Model_Program::STATUS_PENDING);
			try {
				$program->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully resumed'));
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot resume this program'));
		}
		return $this->_redirect('*/*/edit',array('id' => $program->getId()));
	}
	
	public function closeAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if (!$program = $this->_initProgram()) return;
		if ($program->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_COMPLETE){
			$program->setStatus(Magestore_Luckydraw_Model_Program::STATUS_CLOSED);
			try {
				$program->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully closed'));
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot close this program'));
		}
		return $this->_redirect('*/*/edit',array('id' => $program->getId()));
	}
	
	public function dialAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if (!$program = $this->_initProgram()) return;
		if ($program->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_DIALING){
			$program->setStatus(Magestore_Luckydraw_Model_Program::STATUS_COMPLETE);
			try {
				$program->setData('prize_code',$this->getRequest()->getParam('prize_code'));
                $program->save();
				$program->dialLuckDraw();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully completed'));
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot complete this program'));
		}
		return $this->_redirect('*/*/edit',array('id' => $program->getId()));
	}
	
	public function redialAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		if (!$program = $this->_initProgram()) return;
		if ($program->getStatus() == Magestore_Luckydraw_Model_Program::STATUS_COMPLETE){
			try {
				$program->dialLuckDraw();
				$program->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Program was successfully redialed'));
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Cannot redial for this program'));
		}
		return $this->_redirect('*/*/edit',array('id' => $program->getId()));
	}
}