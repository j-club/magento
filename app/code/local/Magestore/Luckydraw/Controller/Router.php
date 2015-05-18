<?php
/**
 * Lucky draw Router
 */
class Magestore_Luckydraw_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
	/**
	 * Initialize Controller Router
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function initControllerRouters($observer){
		$front = $observer->getEvent()->getFront();
		$front->addRouter('luckydraw', $this);
	}

	/**
	 * Validate and Match Lucky Draw Program and modify request
	 *
	 * @param Zend_Controller_Request_Http $request
	 * @return bool
	 */
	public function match(Zend_Controller_Request_Http $request){
		if (!Mage::isInstalled()){
			Mage::app()->getFrontController()->getResponse()
				->setRedirect(Mage::getUrl('install'))
				->sendResponse();
			exit;
		}
		$urlKey = trim($request->getPathInfo(),'/');
		if (!$urlKey) return false;
		
		$program = Mage::getModel('luckydraw/program');
		$programId = $program->checkProgramUrlKey($urlKey,Mage::app()->getStore()->getId());
		if (!$programId) return false;
		
		$request->setModuleName('luckydraw')
			->setControllerName('index')
			->setActionName('index')
			->setParam('id',$programId);
		$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,$urlKey);
		return true;
	}
}
