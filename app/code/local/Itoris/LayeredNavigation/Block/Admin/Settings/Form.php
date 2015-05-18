<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_LAYEREDNAVIGATION
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

/**
 * Settings form of the component
 */
class Itoris_LayeredNavigation_Block_Admin_Settings_Form extends Mage_Adminhtml_Block_System_Config_Form {

	/**
	 * Prepare settings form
	 *
	 * @return Mage_Adminhtml_Block_Widget_Form
	 */
	protected function _prepareForm() {

        /** @var $settings Itoris_LayeredNavigation_Model_Settings */
        $settings = Mage::getModel('itoris_layerednavigation/settings');
        $settings->load(
            $this->getDataHelper()->getWebsiteIdFromRequest(),
			$this->getDataHelper()->getStoreIdFromRequest()
		);

        $scope = $this->getDataHelper()->getScope($this->getRequest());

        Varien_Data_Form::getFieldsetElementRenderer()->setTemplate('itoris/layerednavigation/settings/element.phtml');
        $form = new Varien_Data_Form();


        $fieldSet = $form->addFieldset('base_fieldset', array('legend'=>$this->__('Settings')));


		$fieldSet->addField('enabled', 'select', array (
            'name'  => 'settings[enabled][value]',
            'label' => $this->__('Extension Enabled'),
            'data_type' => 'int',
            'values' => array(
                array('value' => 1, 'label' => $this->__('Yes')),
                array('value' => 0, 'label' => $this->__('No'))
            ),
            'use_parent_value' => $settings->isParentValue('enabled', $scope),
        ));

        $fieldSet->addField('multicategory_enabled', 'select', array (
            'name' => 'settings[multicategory_enabled][value]',
            'label' => $this->__('Multi-category mode'),
            'data_type' => 'int',
            'values' => array(
                array('value' => 1, 'label' => $this->__('Yes')),
                array('value' => 0, 'label' => $this->__('No'))
            ),
            'use_parent_value' => $settings->isParentValue('multicategory_enabled', $scope),
        ));

		$fieldSet->addField('graphical_price_enabled', 'select', array (
			'name' => 'settings[graphical_price_enabled][value]',
			'label' => $this->__('Graphical price range'),
			'data_type' => 'int',
			'values' => array(
				array('value' => 1, 'label' => $this->__('Yes')),
				array('value' => 0, 'label' => $this->__('No'))
			),
			'use_parent_value' => $settings->isParentValue('graphical_price_enabled', $scope),
		));

        $form->setValues($settings->getSettings());

        $form->setAction($this->getUrl('*/*/save', array(
            'website' => $this->getRequest()->getParam('website'),
            'store' => $this->getRequest()->getParam('store')))
        );

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Itoris_LayeredNavigation_Helper_Data
     */
    protected function getDataHelper() {
        return Mage::helper('itoris_layerednavigation');
    }
}
?>