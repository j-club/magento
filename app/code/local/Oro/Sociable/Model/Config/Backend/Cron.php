<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sociable
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Sociable_Model_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH  = 'crontab/jobs/oro_sociable_processqueue/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/oro_sociable_processqueue/run/model';

    const XML_PATH_TIME          = 'groups/facebook/fields/post_time/value';

    /**
     * Cron settings after save
     */
    protected function _afterSave()
    {
        $time      = $this->getData(self::XML_PATH_TIME);

        $cronExprArray = array(
            intval($time[1]),                                   # Minute
            intval($time[0]),                                   # Hour
            '*',                                                # Day of the Month
            '*',                                                # Month of the Year
            '*',                                                # Day of the Week
        );
        $cronExprString = join(' ', $cronExprArray);

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();

            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('backup')->__('Unable to save the cron expression.'));
        }
    }
}
