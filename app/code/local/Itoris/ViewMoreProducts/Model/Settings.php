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

  

class Itoris_ViewMoreProducts_Model_Settings extends Varien_Object {

    /** @var  Mage_Core_Model_Resource */
    private $_resource = null;
    /** @var Varien_Db_Adapter_Pdo_Mysql */
    private $_connection = null;
    private $_table = 'itoris_viewmoreproducts_settings';
    private $_textOptions = array('view_more_text', 'load_more_text');

    private $_settings = array(
        'by_default' => array(
            'enabled' => 1,
            'view_more_text' => 'View More Products',
            'load_more_text' => 'Loading more products...',
        ),
    );

    protected function _construct() {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_connection = $this->_resource->getConnection('core_write');
        $this->_table = $this->_resource->getTableName($this->_table);
    }

    public function save($settings, $websiteId = 0, $storeId = 0) {
        $websiteId = intval($websiteId);
        $storeId = intval($storeId);

        $this->_deleteSettings($websiteId, $storeId);

        $newSettings = array();
        if (is_array($settings)) {
            foreach($settings as $key => $value){
                $settingSql = '(' . $this->_connection->quote($key) . ', ' . $websiteId . ',' . $storeId . ',';
                if ($this->_isTextOption($key)) {
                    $settingSql .= 'null,' . $this->_connection->quote($value);
                } else {
                    $settingSql .= $this->_connection->quote($value) . ', null';
                }
                $settingSql .= ')';
                $newSettings[] = $settingSql;
            }
        }
        if (!empty($newSettings)) {
            $newSettings = implode(',', $newSettings);
            $this->_connection->query("insert into {$this->_table} (`code`, `website_id`, `store_id`, `value`, `value_text`) values {$newSettings}");
        }

        return $this;
    }

    public function load($websiteId, $storeId) {
        $websiteId = (int)$websiteId;
        $storeId = (int)$storeId;
        $settings = $this->_connection->fetchAll("
			select e.* from {$this->_table} as e
			where (e.website_id = {$websiteId} and e.store_id = {$storeId})
					or (e.website_id = {$websiteId} and e.store_id = 0)
					or (e.website_id = 0 and e.store_id = 0)
		");

        foreach ($settings as $setting) {
            $settingValue = $this->_isTextOption($setting['code']) ? $setting['value_text'] : $setting['value'];
            if ($setting['website_id'] && $setting['store_id']) {
                $this->_settings['store'][$setting['code']] = $settingValue;
            } elseif ($setting['website_id']) {
                $this->_settings['website'][$setting['code']] = $settingValue;
            } else {
                $this->_settings['default'][$setting['code']] = $settingValue;
            }
        }

        return $this;
    }

    public function getSettingsValue($key) {
        return $this->__call('get' . $key, array());
    }

    public function __call($method, $args) {
        if (substr($method, 0, 3) == 'get') {
            $key = $this->_underscore(substr($method,3));
            if (isset($this->_settings['store'][$key])) {
                return $this->_settings['store'][$key];
            } elseif (isset($this->_settings['website'][$key])) {
                return $this->_settings['website'][$key];
            } elseif (isset($this->_settings['default'][$key])) {
                return $this->_settings['default'][$key];
            } elseif (isset($this->_settings['by_default'][$key])) {
                return $this->_settings['by_default'][$key];
            }
            return $this->getData($key, isset($args[0]) ? $args[0] : null);
        } else {
            parent::__call($method,$args);
        }
    }

    public function isParentValue($key) {
        if (isset($this->_settings['store'][$key])) {
            return false;
        }

        return true;
    }

    private function _deleteSettings($websiteId, $storeId) {
        $this->_connection->query("DELETE FROM $this->_table WHERE `website_id`={$websiteId} and `store_id`={$storeId}");
        return $this;
    }

    public function _isValid($settings) {
        $errors = array();

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    private function _isTextOption($key) {
        return in_array($key, $this->_textOptions);
    }
}
?>