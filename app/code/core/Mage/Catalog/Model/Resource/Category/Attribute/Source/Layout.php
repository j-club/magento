<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Catalog category landing page attribute source
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Category_Attribute_Source_Layout
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Return cms layout update options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $layouts = array();
            foreach (Mage::getConfig()->getNode('global/cms/layouts')->children() as $layoutName=>$layoutConfig) {
                $this->_options[] = array(
                   'value'=>$layoutName,
                   'label'=>(string)$layoutConfig->label
                );
            }
            array_unshift($this->_options, array('value'=>'', 'label' => Mage::helper('catalog')->__('No layout updates')));
        }
        return $this->_options;
    }
}
