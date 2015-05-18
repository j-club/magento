<?php
/**
 * Grid Controller
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_AdvSlider_Adminhtml_AdvsliderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Banner binding tab grid action on category
     *
     */
    public function categoryBannersGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('catalog/category');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('catalog')->__('This category no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }
        if (!Mage::registry('current_category')) {
            Mage::register('current_category', $model);
        }
        $this->loadLayout();
        $this->getLayout()
            ->getBlock('related_category_banners_grid')
            ->setSelectedCategoryBanners($this->getRequest()->getPost('selected_category_banners'));
        $this->renderLayout();
    }
}
