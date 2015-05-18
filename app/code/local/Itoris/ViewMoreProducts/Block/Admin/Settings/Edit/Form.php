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
 * @package    ITORIS_VIEWMOREPRODUCTS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

  

class Itoris_ViewMoreProducts_Block_Admin_Settings_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
		$form = new Varien_Data_Form();

		$fieldset = $form->addFieldset('general_fields', array(
			'legend' => $this->__('View More Products'),
		));

		$fieldset->addField('enabled', 'select', array(
			'name'   => 'settings[enabled][value]',
			'label'  => $this->__('Extension enabled'),
			'title'  => $this->__('Extension enabled'),
			'values' => array(
				array('label' => $this->__('Yes'),
					'value' => 1),
				array('label' => $this->__('No'),
					'value' => 0),
			),
		))->getRenderer()->setTemplate('itoris/viewmoreproducts/configuration/form/element.phtml');

        $fieldset->addField('load_more_text', 'text', array(
            'name'  => 'settings[load_more_text]',
            'label' => $this->__('Loading More Text'),
            'title' => $this->__('Loading More Text'),
        ));

        $fieldset->addField('view_more_text', 'text', array(
            'name'  => 'settings[view_more_text]',
            'label' => $this->__('View More Text'),
            'title' => $this->__('View More Text'),
            'required' => true,
        ));

        $fieldset->addField('load_more_method', 'select', array(
            'name'  => 'settings[load_more_method]',
            'label' => $this->__('Load more method'),
            'title' => $this->__('Load more method'),
            'values' => array(
                array('label' => $this->__('By clicking button'),
                    'value' => 0),
                array('label' => $this->__('On page scroll automatically'),
                    'value' => 1),
            ),
        ));

        $form->setValues($this->getFormHelper()->prepareElementsValues($form));
		$form->setUseContainer(true);
		$form->setId('edit_form');
		$form->setAction($this->getUrl('itoris_viewmoreproducts/admin_configuration/save', array('_current' => true)));
		$form->setMethod('post');
        $form->setEnctype('multipart/form-data');
		$this->setForm($form);
	}

	/**
	 * @return Itoris_ViewMoreProducts_Helper_Form
	 */
	public function getFormHelper() {
		return Mage::helper('itoris_viewmoreproducts/form');
	}

}
?>