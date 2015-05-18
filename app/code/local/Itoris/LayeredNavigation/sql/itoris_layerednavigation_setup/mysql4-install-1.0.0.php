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

$this->startSetup();

$settingsTable = 'itoris_layerednavigation_settings';

$this->run("

CREATE TABLE IF NOT EXISTS {$this->getTable($settingsTable)} (
    `scope` ENUM('default', 'website', 'store') NOT NULL ,
    `scope_id` INT UNSIGNED NOT NULL ,
    `key` VARCHAR( 255 ) NOT NULL ,
    `int_value` INT UNSIGNED NULL ,
    `text_value` TEXT NULL,
    `type` ENUM('text', 'int') NULL,
  PRIMARY KEY(`scope`, `scope_id`, `key`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

INSERT INTO {$this->getTable($settingsTable)} (`scope`, `scope_id`, `key`, `int_value`, `type`) VALUES
	('default', 0, 'enabled', 1, 'int'),
	('default', 0, 'multicategory_enabled', 1, 'int'),
	('default', 0, 'graphical_price_enabled', 1, 'int');
");

$this->endSetup();
?>