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

  

$this->startSetup();

$this->run("

create table {$this->getTable('itoris_viewmoreproducts_settings')} (
	`code` varchar(255) not null,
	`store_id` smallint(5) unsigned not null,
	`website_id` smallint(5) unsigned not null,
	`value` varchar(255) null,
	`value_text` text null,
	foreign key (`store_id`) references {$this->getTable('core_store')} (`store_id`) on delete cascade on update cascade,
	foreign key (`website_id`) references {$this->getTable('core_website')} (`website_id`) on delete cascade on update cascade
)engine = InnoDB default charset = utf8;

");

$this->endSetup();
?>