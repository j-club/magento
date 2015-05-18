<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sync
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

require dirname(__FILE__) . '/abstract.php';

class Oro_Sync_Shell_Cache extends Mage_Shell_Abstract
{
    /**
     * Returns a list of cachetypes, and their current cache status.
     *
     * @param string $string
     * @return array
     */
    protected function _parseCacheTypeString($string)
    {
        $cachetypes = array();
        if ($string == 'all') {
            $collection = $this->_getCacheTypeCodes();
            foreach ($collection as $cache) {
                $cachetypes[] = $cache;
            }
        } else if (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $cachetypes[] = $code;
            }
        }

        return $cachetypes;
    }

    /**
     * Gets Magento cache types.
     *
     * @return
     */
    private function _getCacheTypes()
    {
        return Mage::getModel('core/cache')->getTypes();
    }

    /**
     * Gets an array of cache type code.
     *
     * @return array Cache type codes.
     */
    private function _getCacheTypeCodes()
    {
        return array_keys($this->_getCacheTypes());
    }

    /**
     * Refreshes caches for the provided cache types.
     *
     * @param  $types
     * @return void
     */
    public function refresh($types)
    {
        $updatedTypes = 0;
        if (!empty($types)) {
            foreach ($types as $type) {
                try {
                    if ($type == 'full_page') {
                        Enterprise_PageCache_Model_Cache::getCacheInstance()
                            ->clean(Enterprise_PageCache_Model_Processor::CACHE_TAG);
                    } else {
                        Mage::app()->getCacheInstance()->cleanType($type);
                    }
                    $updatedTypes++;
                } catch (Exception $e) {
                    echo $type . " cache unknown error:\n";
                    echo $e . "\n";
                }
            }
        }
        if ($updatedTypes > 0) {
            echo "$updatedTypes cache type(s) refreshed.\n";
        }
    }

    /**
     * Invalidates caches for the provided cache types.
     *
     * @param  $types
     * @return void
     */
    public function invalidate($types) {
        $updatedTypes = 0;
        if (!empty($types)) {
            foreach ($types as $type) {
                try {
                    Mage::app()->getCacheInstance()->invalidateType($type);
                    $updatedTypes++;
                } catch (Exception $e) {
                    echo $type . " cache unknown error:\n";
                    echo $e . "\n";
                }
            }
        }
        if ($updatedTypes > 0) {
            echo "$updatedTypes cache type(s) invalidated.\n";
        }
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        // --refresh
        if ($this->getArg('refresh')) {
            if ($this->getArg('refresh')) {
                $types = $this->_parseCacheTypeString($this->getArg('refresh'));
            } else {
                $types = $this->_parseCacheTypeString('all');
            }
            $this->refresh($types);

        // --invalidate
        } else if ($this->getArg('invalidate')) {
            if ($this->getArg('invalidate')) {
                $types = $this->_parseCacheTypeString($this->getArg('invalidate'));
            } else {
                $types = $this->_parseCacheTypeString('all');
            }
            $this->invalidate($types);

        // help
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cache.php -- [options]
  --refresh    <cachetype>    Clean cache types.
  --invalidate <cachetype>    Clean cache types.
  help                        This help.
  <cachetype>     Comma separated cache codes (layout, block_html, full_page) or value "all" for all caches

USAGE;
    }
}

$shell = new Oro_Sync_Shell_Cache();
$shell->run();

