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
 * Settings model of the module.
 *
 * @method getEnabled()
 * @method setEnabled(boolean $value)
 * @method getMulticategoryEnabled()
 * @method setMulticategoryEnabled(boolean $value)
 * @method getGraphicalPriceEnabled()
 * @method setGraphicalPriceEnabled(boolean $value)
 *
 */
class Itoris_LayeredNavigation_Model_Settings extends Varien_Object {

	/** @var Varien_Db_Adapter_Pdo_Mysql */
	private $_resource;
	private $_table = 'itoris_layerednavigation_settings';

	private $_scope;
	private $_scopeId;
	private $_settings = array('default' => array(), 'website' => array(), 'store' => array());

	/**
	 * Loads settings for specified scope or for default.
	 *
	 * @param array $scope
	 */
	public function __construct($scope = null) {
		$this->_getConnection();
		$this->_table = Mage::getConfig()->getTablePrefix().$this->_table;

		if (is_array($scope) && isset($scope['website_id']) && isset($scope['store_id'])) {
			$this->load($scope['website_id'], $scope['store_id']);
		} else if (Mage::app()->getStore()->getCode() != Mage_Core_Model_Store::ADMIN_CODE) {
			$this->load(Mage::app()->getWebsite()->getId(), Mage::app()->getStore()->getId());
		}
	}

	/**
	 * Save settings to the db for the particular scope.
	 *
	 * @param $settings
	 * @param string $scope
	 * @param int $scopeId
	 */
	public function save($settings, $scope = 'default', $scopeId = 0) {

			$this->_scope = $scope;
			$this->_scopeId = (int)$scopeId;
		
			$this->_deleteSettings();
			$newSettings = array();
			foreach($settings as $key => $value){
				if(!(isset($settings[$key]['use_parent']))  || $scope == 'default'){
					$newSettings[$key] = array(
                        'value' => $value['value'],
                        'type' => $value['type']
                    );
				}
			}

			if (!empty($newSettings)) {
				$this->_saveSettings($newSettings);
			}
			$this->_scope = null;
			$this->_scopeId = null;
	}

	/**
	 * Load settings for the specified scope.
	 *
	 * @param $websiteId
	 * @param $storeId
	 * @return Itoris_LayeredNavigation_Model_Settings
	 */
	public function load($websiteId, $storeId) {
		$websiteId = (int)$websiteId;
		$storeId = (int)$storeId;
		$settings = $this->_resource->fetchAll("SELECT e.key, e.scope,e.int_value, e.text_value, e.type
												FROM $this->_table as e
												WHERE (e.scope = 'default' and e.scope_id = 0)
												OR (e.scope = 'website' and e.scope_id = $websiteId)
												OR (e.scope = 'store' and e.scope_id = $storeId)");
		$this->_saveSettingsIntoArray($settings);
		return $this;
	}

	private function _saveSettingsIntoArray($settings) {
		foreach($settings as $value){
			$this->_settings[$value['scope']][$value['key']] = $value[$value['type'].'_value'];
		}
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
				}
				return $this->getData($key, isset($args[0]) ? $args[0] : null);
        } else {
			parent::__call($method,$args);
		}
    }

    public function getSettings() {
        $result = $this->_settings['default'];
        foreach (array('website', 'store') as $scope) {
            foreach ($this->_settings[$scope] as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

	/**
	 * Check if value is inherited from the wider scope.
	 *
	 * @param $key
	 * @param $scope
	 * @return bool
	 */
	public function isParentValue($key, $scope) {
        return !isset($this->_settings[$scope][$key]);
	}

	private function _getConnection() {
		$this->_resource = Mage::getSingleton('core/resource')->getConnection('core_write');
		return $this->_resource;
	}

	private function _deleteSettings() {
		$this->_resource->query("DELETE FROM $this->_table WHERE `scope`=? and `scope_id`=?", array(
            $this->_scope,
            $this->_scopeId
        ));
	}

	private function _saveSettings($settings) {

		foreach($settings as $key => $value){
            $this->_resource
					->query("insert into $this->_table (`scope`, `scope_id`, `key`, `{$value['type']}_value`, `type`)
                    values (?,?,?,?,?)", array(
                $this->_scope,
                $this->_scopeId,
                $key,
                $value['value'],
                $value['type']
            ));
		}

	}

}
?>