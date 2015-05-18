<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipSellYours_Adminhtml_VendorController extends Mage_Adminhtml_Controller_Action
{
    public function massIsFeaturedAction()
    {
        $modelIds = (array)$this->getRequest()->getParam('vendor');
        $is_featured = (string)$this->getRequest()->getParam('is_featured');

        try {
            foreach ($modelIds as $modelId) {
                Mage::getModel('udropship/vendor')->load($modelId)->setData('is_featured', $is_featured)->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated', count($modelIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('There was an error while updating vendor(s) is featured'));
        }

        $this->_redirect('udropshipadmin/adminhtml_vendor/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor');
    }
}
