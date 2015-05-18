<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE-CE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE-CE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Magpleasure does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Magpleasure does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Magpleasure
 * @package    Magpleasure_Forms
 * @version    1.0.5
 * @copyright  Copyright (c) 2011-2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE-CE.txt
 */

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mp_form_list_value')};
DROP TABLE IF EXISTS {$this->getTable('mp_form_list')};
DROP TABLE IF EXISTS {$this->getTable('mp_form_store')};
DROP TABLE IF EXISTS {$this->getTable('mp_form_structure')};
DROP TABLE IF EXISTS {$this->getTable('mp_form')};

CREATE TABLE IF NOT EXISTS  {$this->getTable('mp_form')} (
   `form_id` int UNSIGNED NOT NULL AUTO_INCREMENT , 
   `name` varchar(255) NOT NULL , 
   `id` varchar(100) NOT NULL , 
   `status` int(3) UNSIGNED NOT NULL , 
   `before_form` text , 
   `after_form` text , 
   `submit_button_text` varchar(255) NOT NULL , 
   `guest_can_post` int(3) UNSIGNED NOT NULL DEFAULT '1' , 
   `approve_require` int(3) UNSIGNED NOT NULL DEFAULT '0' ,
   `is_locked` int(3) UNSIGNED NOT NULL DEFAULT '0' ,
   `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS  {$this->getTable('mp_form_structure')} (
   `structure_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
   `form_id` int UNSIGNED NOT NULL , 
   `question` tinytext NOT NULL , 
   `hint` tinytext , 
   `type` varchar(100) NOT NULL ,
   `is_require` int(3) UNSIGNED NOT NULL DEFAULT '0' , 
   `sort_order` int(10) NOT NULL DEFAULT '0' ,
   `display_on_front` int(3) UNSIGNED NOT NULL , 
   `values` text NOT NULL , 
   `enable_other` int(3) UNSIGNED NOT NULL DEFAULT '0' , 
   PRIMARY KEY (`structure_id`),
   KEY `FK_FORMS_FORM_ID` (`form_id`),
   CONSTRAINT `FK_FORMS_FORM_ID` FOREIGN KEY (`form_id`) REFERENCES `{$this->getTable('mp_form')}` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_form_store')}` (
   `form_id` int(11) UNSIGNED NOT NULL,
   `store_id` smallint(5) unsigned NOT NULL,
   PRIMARY KEY (`form_id`, `store_id`),
   KEY `FK_FORMS_STORE_INT_FORM_ID` (`form_id`),
   CONSTRAINT `FK_FORMS_STORE_INT_FORM_ID` FOREIGN KEY (`form_id`) REFERENCES `{$this->getTable('mp_form')}` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   KEY `FK_FORMS_STORE_ID` (`store_id`),
   CONSTRAINT `FK_FORMS_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_form_list')}`(
   `list_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
   `form_id` int UNSIGNED NOT NULL ,
   `customer_id` int(10) UNSIGNED ,
   `status` varchar(20) NOT NULL,
   `created_at` datetime NOT NULL ,
   `store_id` smallint(5) UNSIGNED NOT NULL ,
   PRIMARY KEY (`list_id`),
   KEY `FK_FORMS_LIST_FORM_ID` (`form_id`),
   CONSTRAINT `FK_FORMS_LIST_FORM_ID` FOREIGN KEY (`form_id`) REFERENCES `{$this->getTable('mp_form')}` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_form_list_value')}`(
   `value_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `list_id` int(20) UNSIGNED NOT NULL ,
   `structure_id` int(10) UNSIGNED NOT NULL ,
   `value` text ,
   PRIMARY KEY (`value_id`),
   KEY `FK_FORMS_VALUE_LIST_ID` (`list_id`),
   KEY `FK_FORMS_VALUE_STRUCTURE_ID` (`structure_id`),
   CONSTRAINT `FK_FORMS_VALUE_LIST_ID` FOREIGN KEY (`list_id`) REFERENCES `{$this->getTable('mp_form_list')}` (`list_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `FK_FORMS_VALUE_STRUCTURE_ID` FOREIGN KEY (`structure_id`) REFERENCES `{$this->getTable('mp_form_structure')}` (`structure_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 