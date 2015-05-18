<?php
/**
 * Controller
 *
 * @category   Oro
 * @package    Oro_Membership
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
require_once  'Magestore/Membership/controllers/Adminhtml/PackageController.php';

class Oro_Membership_Adminhtml_PackageController extends Magestore_Membership_Adminhtml_PackageController
{
    public function saveAction()
    {
        $packageId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['package_member'])){
                $members = array();
                parse_str($data['package_member'], $members);
                $members = array_keys($members);
            }else{
                $members = null;
            }

            if(isset($data['package_group'])){
                $groupIds = array();
                parse_str($data['package_group'], $groupIds);
                $groupIds = array_keys($groupIds);
            }else{
                $groupIds = array(0);
            }

            //get productIds
            $productIds = array();
            if(isset($data['product_select_all'])) {
                if($data['product_select_all']=='1') {
                    $productCollection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addFieldToFilter('status', 1)
                        ->addAttributeToSelect('*');
                    $productIds = $productCollection->getAllIds();
                } elseif($data['product_select_all']=='0') {
                    $productIds = array();
                } else {
                    if(isset($data['package_product'])){
                        parse_str($data['package_product'], $productIds);
                        $productIds = array_keys($productIds);
                    } else {
                        $productIds = array(0);
                    }
                }
            } else {
                if(isset($data['package_product'])){
                    parse_str($data['package_product'], $productIds);
                    $productIds = array_keys($productIds);
                } else {
                    $productIds = array(0);

                }
            }
            //print_r($data);die();

            $packageModel = Mage::getModel('membership/package');
            $packageModel->setData($data)
                ->setStoreId($storeId)
                ->setId($packageId);
            if($packageId){
                $productId = Mage::getModel('membership/package')->load($packageId)->getProductId();
            }
            if($data['package_name_default']){
                if($data['package_name_default']==1){
                    $data['package_name'] = Mage::getModel('membership/package')->load($packageId)->getPackageName();
                }else{
                    $data['package_name'] = $data['package_name_default'];
                }
            }
            if($data['description_default']){
                if($data['description_default']==1){
                    $data['description'] = Mage::getModel('membership/package')->load($packageId)->getDescription();
                }else{
                    $data['description'] = $data['description_default'];
                }
            }

            try {
                //create membership product
                if (!$packageId) {
                    $productId = Mage::helper('membership')->createMembershipProduct($data['package_name'], $data['description'], $data['package_price'], $data['package_status'], $productId, $storeId);
                }

                if ($productId)
                    $packageModel->setProductId($productId);
                else {
                    $this->_redirect('*/*/');
                    return;
                }
                $packageModel->save();
                //print_r($packageModel);die();
                if($members && isset($members)){
                    Mage::helper('membership')->setMemberByAdmin($members, $packageModel);
                }
                Mage::helper('membership')->assignGroupIds($packageModel, $groupIds);
                Mage::helper('membership')->assignProductIdsToPackage($packageModel, $productIds);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('membership')->__('Package was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $packageModel->getId(),'store'=>$storeId));
                    return;
                }
                $this->_redirect('*/*/', array('store'=>$storeId));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('membership')->__('Unable to find package to save'));
        $this->_redirect('*/*/');
    }
}
