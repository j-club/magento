<?php

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('luckydraw_code')};
DROP TABLE IF EXISTS {$this->getTable('luckydraw_program')};

CREATE TABLE {$this->getTable('luckydraw_program')} (
  `program_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `short_description` text NULL,
  `award_image` varchar(255) NULL,
  `description` mediumtext NULL,
  `url_key` varchar(255) NULL,
  `status` smallint(5) default '0',
  `created_time` datetime NULL,
  `start_time` datetime NULL,
  `end_time` datetime NULL,
  `code_length` smallint(5) default '5',
  `min_user` smallint(5) default '0',
  `auto_prize` tinyint(1) default '0',
  `prize_code` varchar(255) default '',
  `prize_days` int(10) default '0',
  `credit_rate` decimal(12,4) NOT NULL default '0',
  `term_condition` mediumtext NULL,
  `store_ids` text NULL,
  PRIMARY KEY (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('luckydraw_code')} (
  `code_id` int(10) unsigned NOT NULL auto_increment,
  `program_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `refer_user` int(10) unsigned NOT NULL,
  `refer_email` varchar(255) NULL,
  `name` varchar(255) NULL,
  `email` varchar(255) NULL,
  `address_id` int(10) unsigned NULL,
  `order_id` int(10) unsigned NOT NULL,
  `order_increment_id` varchar(127) NULL,
  `draw_code` varchar(255) NULL,
  `created_time` datetime NULL,
  `expired_time` datetime NULL,
  `status` smallint(5) default '0',
  `is_prize` tinyint(1) default '0',
  `credit_rate` decimal(12,4) NOT NULL default '0',
  PRIMARY KEY (`code_id`),
  INDEX (`program_id`),
  FOREIGN KEY (`program_id`)
  REFERENCES {$this->getTable('luckydraw_program')} (`program_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

try {
	$installer->run("
	ALTER TABLE {$this->getTable('sales/order')}
		ADD COLUMN `luckydraw_discount` decimal(12,4) default NULL,
		ADD COLUMN `base_luckydraw_discount` decimal(12,4) default NULL;
	");
} catch (Exception $e){}

try {
    $setup = new Mage_Customer_Model_Entity_Setup();
    $setup->addAttribute('customer', 'national_id', array(
        'group' => 'General',
        'type'  => 'varchar',
        'input' => 'text',
        'label' => 'National Identification',
        'visible'   => 1,
        'required'  => 0,
        'visible_on_front'  => 1,
        'sort_order'    => 120
    ));
    
    $setup->getConnection()->insertMultiple($setup->getTable('customer/form_attribute'), array(
        array(
            'form_code' => 'adminhtml_customer',
            'attribute_id'  => $setup->getAttributeId('customer','national_id')
        )
    ));
} catch (Exception $e){}

$installer->endSetup();
