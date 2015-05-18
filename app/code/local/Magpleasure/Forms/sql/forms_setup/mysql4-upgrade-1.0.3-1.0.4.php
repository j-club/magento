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

ALTER TABLE `{$this->getTable('mp_form')}`
  ADD COLUMN `redirect_type` VARCHAR(100) DEFAULT 'self' NULL AFTER `is_locked`,
  ADD COLUMN `redirect_entity_id` BIGINT NULL AFTER `redirect_type`,
  ADD COLUMN `add_data_to_email` SMALLINT(1) DEFAULT 0 NOT NULL AFTER `redirect_entity_id`,
  ADD COLUMN `list_rows_responsive` SMALLINT(1) DEFAULT 0 NOT NULL AFTER `add_data_to_email`
  ;

ALTER TABLE `{$this->getTable('mp_form_structure')}`
  ADD COLUMN `display_in_post` INT(3) UNSIGNED DEFAULT 0  NOT NULL AFTER `display_on_front`;

");

$installer->endSetup();