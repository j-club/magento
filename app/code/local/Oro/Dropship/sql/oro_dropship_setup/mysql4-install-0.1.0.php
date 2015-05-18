<?php
/**
 * Modify Unirgy Dropship Vendor for new admin fields
 *
 * @category   Oro
 * @package    Oro_Dropship
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($installer->getTable('udropship/vendor'), 'filing_number', 'varchar(100)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'received_by', 'varchar(255)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'received_date', 'varchar(100)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'approved_by', 'varchar(255)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'approved_date', 'varchar(100)');
$conn->addColumn($installer->getTable('udropship/vendor'), 'remarks', 'varchar(255)');

$installer->endSetup();