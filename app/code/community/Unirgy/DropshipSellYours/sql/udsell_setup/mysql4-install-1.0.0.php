<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$hlp = Mage::helper('udropship');

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($installer->getTable('customer/entity'), 'username', 'varchar(64)');
$conn->addColumn($installer->getTable('customer/entity'), 'vendor_id', 'int(10)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'username', 'varchar(64)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'customer_id', 'int(10)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'account_type', 'varchar(20)');

$conn->addColumn($installer->getTable('udropship/vendor'), 'is_featured', 'tinyint(1)');

$installer->endSetup();
