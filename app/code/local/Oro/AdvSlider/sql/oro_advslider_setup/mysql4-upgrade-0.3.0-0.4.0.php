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
 * @category    Enterprise
 * @package     Enterprise_CatalogPermissions
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('oro_advslider/category_banner');

$installer->run("
    DROP TABLE IF EXISTS `{$tableName}`;
    CREATE TABLE `{$tableName}` (
        `category_id` INT(10) UNSIGNED NOT NULL,
        `banner_id` INT(10) UNSIGNED NOT NULL,
        `position` int(10) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`category_id`, `banner_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addConstraint('ORO_ADVSLIDER_INDEX_CATEGORY', $tableName, 'category_id',
                                           $installer->getTable('catalog/category'), 'entity_id');

$installer->getConnection()->addConstraint('ORO_ADVSLIDER_INDEX_BANNER', $tableName, 'banner_id',
                                            $installer->getTable('enterprise_banner/banner'), 'banner_id');

$installer->endSetup();

