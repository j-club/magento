<?php

class Magestore_Luckydraw_Adminhtml_CodeController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction(){
		$this->loadLayout()
			->_setActiveMenu('luckydraw/code')
			->_addBreadcrumb($this->__('Luckydraw'),$this->__('Code Manager'));
		return $this;
	}

	public function indexAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$this->_initAction()
			->_title($this->__('Lucky Draw'))
			->_title($this->__('Codes'))
			->renderLayout();
	}
	
	public function massStatusAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$codeIds = $this->getRequest()->getParam('luckydrawcode');
		if(!is_array($codeIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
		} else {
			try {
				foreach ($codeIds as $codeId) {
					$code = Mage::getSingleton('luckydraw/code')
						->load($codeId)
						->setStatus($this->getRequest()->getParam('status'))
						->setIsMassupdate(true)
						->save();
				}
				$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully updated', count($codeIds))
				);
			} catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
  
	public function exportCsvAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$fileName   = 'drawcode.csv';
		$content	= $this->getLayout()->createBlock('luckydraw/adminhtml_code_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName,$content);
	}

	public function exportXmlAction(){
		if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) { return; }
		$fileName   = 'drawcode.xml';
		$content	= $this->getLayout()->createBlock('luckydraw/adminhtml_code_grid')->getXml();
		$this->_prepareDownloadResponse($fileName,$content);
	}
}