<?php
$installer = $this;
$installer->startSetup();


$installer->run("
		CREATE TABLE IF NOT EXISTS {$installer->getTable('be_sociable_cron')} (
		  `entity_id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) NOT NULL,
		  `post_date` varchar(50) NOT NULL,
		  `network` varchar(50) NOT NULL,
		  `done` INT(2) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`entity_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		");
		

$installer->endSetup(); 