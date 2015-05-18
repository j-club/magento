<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset Adminhtml Asset Controller Action
 */
class Oro_Asset_Adminhtml_AssetController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Increases assets version
     */
    public function incrementAction()
    {
        $helper = Mage::helper('oro_asset');
        try {
            $helper->increaseVersion(true);
            $this->_getSession()->addSuccess($helper->__('The JavaScript/CSS version has been updated.'));
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                $helper->__('An error occurred while updating the JavaScript/CSS version.')
            );
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'dev'));
    }

    /**
     * Checks is Admin user has permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/cache');
    }
}
