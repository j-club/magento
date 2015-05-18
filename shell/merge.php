<?php
/**
 * @category   Oro
 * @package    Oro_Assert
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

require_once 'abstract.php';

/**
 * Merge CSS and JS files
 *
 */
class Mage_Shell_Merge extends Mage_Shell_Abstract
{
    /**
     * Merge Config XML element
     *
     * @var Mage_Core_Model_Config_Base
     */
    protected $_config;

    /**
     * Default input arguments
     *
     * @var array
     */
    protected $_args            = array(
        'compress'      => 'true',
        'type'          => 'all',
        'js-optimize'   => 'false',
        'js-munge'      => 'true',
    );

    /**
     * YUI Compressor file name
     *
     * @uri http://yui.github.io/yuicompressor/
     * @var string
     */
    protected $_yuiCompressor   = 'yuicompressor-2.4.7.jar';

    /**
     * Full path to Source file
     *
     * @var string
     */
    protected $_sourceFile;

    /**
     * Full path to Output file
     *
     * @var string
     */
    protected $_outputFile;

    /**
     * Full path to Output compressed file
     *
     * @var string
     */
    protected $_outMinFile;

    /**
     * Run script
     *
     */
    public function run()
    {
        $type = $this->getArg('type');
        if (!in_array($type, array('js', 'css', 'all'))) {
            echo 'ERROR: Unknown type' . PHP_EOL;
            echo $this->usageHelp();
        }

        if ($type == 'all' || $type == 'css') {
            $this->_mergeCss();
        }
        if ($type == 'all' || $type == 'js') {
            $this->_mergeJs();
        }
    }

    /**
     * Loads merge files
     *
     * @return Mage_Core_Model_Config_Base
     */
    protected function _getMergeConfig()
    {
        if ($this->_config === null) {
            $this->_config = Mage::getConfig()->loadModulesConfiguration('merge.xml');
        }

        return $this->_config;
    }

    /**
     * Processes CSS file rules and merge it
     */
    protected function _mergeCss()
    {
        echo 'START PROCESS CSS FILES' . PHP_EOL;

        foreach ($this->_getMergeConfig()->getNode('css')->children() as $css) {
            $this->_processCssFile($css);
            $this->_compressCssFile();
        }

        echo 'END PROCESS CSS FILES' . PHP_EOL;
    }

    /**
     * Processes JS file rules and merge it
     */
    protected function _mergeJs()
    {
        echo 'START PROCESS JS FILES' . PHP_EOL;

        foreach ($this->_getMergeConfig()->getNode('js')->children() as $js) {
            $this->_processJsFile($js);
            $this->_compressJsFile();
        }

        echo 'END PROCESS JS FILES' . PHP_EOL;
    }

    /**
     * Merges CSS file content and write to file
     *
     * @param Mage_Core_Model_Config_Element $xml
     */
    protected function _processCssFile(Mage_Core_Model_Config_Element $xml)
    {
        $type       = $xml->type ? (string)$xml->type : 'skin';
        $area       = $xml->area ? (string)$xml->area : 'frontend';
        $package    = $xml->package ? (string)$xml->package : 'base';
        $theme      = $xml->theme ? (string)$xml->theme : 'default';
        $path       = $xml->path ? (string)$xml->path : 'css';

        if (!$this->_canProcess($type, $area, $package, $theme)) {
            return;
        }

        $filename   = sprintf('%s.css', $xml->filename);
        $minFile    = sprintf('%s.min.css', $xml->filename);

        if ($type == 'skin') {
            $path = str_replace('/', DS, $path);
            $this->_outputFile = Mage::getBaseDir('skin') . DS . $area
                . DS . $package . DS . $theme . DS . $path. DS . $filename;
            $this->_outMinFile = Mage::getBaseDir('skin') . DS . $area
                . DS . $package . DS . $theme . DS . $path. DS . $minFile;
        } else {
            return;
        }

        echo 'MERGE FILE ';
        echo str_replace(Mage::getBaseDir() . DS, '', $this->_outputFile);
        echo PHP_EOL;

        $content    = $this->_getMergedContent($xml, 'css', $type, $area, $package, $theme, $path);

        file_put_contents($this->_outputFile, $content);

        echo 'MERGED SUCCESS' . PHP_EOL;
    }

    /**
     * Checks is can process instruction
     *
     * @param string $type
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return bool
     */
    protected function _canProcess($type, $area, $package, $theme)
    {
        if ($type == 'static') {
            return true;
        }

        if ($this->getArg('area') && $this->getArg('area') != $area) {
            return false;
        }

        if ($this->getArg('package') && $this->getArg('package') != $package) {
            return false;
        }

        if ($this->getArg('theme') && $this->getArg('theme') != $theme) {
            return false;
        }

        return true;
    }

    /**
     * Returns merge content by merge instructions
     *
     * @param Mage_Core_Model_Config_Element $xml
     * @param string $assetType
     * @param string $type
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param string $path
     * @return string
     */
    protected function _getMergedContent($xml, $assetType, $type, $area, $package, $theme, $path)
    {
        $content    = '';

        /** @var $node Mage_Core_Model_Config_Element */
        foreach ($xml->files->children() as $node) {
            $sourceType     = $node->type ? (string)$node->type : $type;
            $sourceArea     = $node->area ? (string)$node->area : $area;
            $sourcePackage  = $node->package ? (string)$node->package : $package;
            $sourceTheme    = $node->theme ? (string)$node->theme : $theme;
            $sourcePath     = $node->path ? (string)$node->path : $path;
            $sourceFilename = (string)$node->filename;

            if ($sourceType == 'skin') {
                $this->_sourceFile = Mage::getBaseDir('skin') . DS . $sourceArea
                    . DS . $sourcePackage . DS . $sourceTheme . DS . $sourcePath . DS . $sourceFilename;

                // fallback to package_default
                if (!file_exists($this->_sourceFile) && $sourceTheme != 'default') {
                    $sourceTheme = 'default';
                    $this->_sourceFile = Mage::getBaseDir('skin') . DS . $sourceArea
                        . DS . $sourcePackage . DS . $sourceTheme . DS . $sourcePath . DS . $sourceFilename;
                }

                // fallback to base_default
                if (!file_exists($this->_sourceFile) && $sourcePackage != 'base') {
                    $sourcePackage = 'base';
                    $this->_sourceFile = Mage::getBaseDir('skin') . DS . $sourceArea
                        . DS . $sourcePackage . DS . $sourceTheme . DS . $sourcePath . DS . $sourceFilename;
                }
            } else {
                $this->_sourceFile = Mage::getBaseDir() . DS . $sourcePath . DS . $sourceFilename;
            }

            $printName = str_replace(Mage::getBaseDir() . DS, '', $this->_sourceFile);
            $printName = str_replace('\\', '/', $printName);

            if (!file_exists($this->_sourceFile)) {
                echo "\t" . 'File ' . $printName . ' does not exists' . PHP_EOL;
                continue;
            }

            $fileContent    = file_get_contents($this->_sourceFile);
            if ($assetType == 'css') {
                $fileContent = $this->_processCssContent($fileContent);
            } else if ($assetType == 'js') {
                $fileContent = $this->_processJsContent($fileContent);
            }
            $nameFix        = str_repeat(' ', 80 - strlen($printName));
            $fileHeader     = <<<CONTENT
/* ======================================================================================= */
/* merged {$printName}{$nameFix} */
/* ======================================================================================= */

CONTENT;

            $content .= $fileHeader . $fileContent . PHP_EOL;

            echo "\t" . 'File ' . $printName . ' merged' . PHP_EOL;
        }

        return $content;
    }

    /**
     * Compresses CSS file and write to file
     */
    protected function _compressCssFile()
    {
        if (!in_array($this->getArg('compress'), array('true', '1'))) {
            return;
        }

        echo 'COMPRESS FILE ';
        echo str_replace(Mage::getBaseDir() . DS, '', $this->_outputFile);
        echo PHP_EOL;

        $format     = 'java -jar "%s" --type css --charset utf-8 -o "%s" "%s"';
        $jarFile    = Mage::getBaseDir('lib') . DS . $this->_yuiCompressor;
        $command    = sprintf($format, $jarFile, $this->_outMinFile, $this->_outputFile);
        exec($command, $output, $return);

        if ($return === 0) {
            echo 'COMPRESSED SUCCESS' . PHP_EOL;
        } else {
            echo 'COMPRESS FAILED' . PHP_EOL;
        }
    }

    /**
     * Returns CSS content with replaced URIs and End of Lines
     *
     * @param string $content
     * @return string
     */
    protected function _processCssContent($content)
    {
        $pattern = '/url\\(\\s*(?!data:)([^\\)\\s]+)\\s*\\)?/';
        $content = preg_replace_callback($pattern, array($this, '_cssMergerUrlCallback'), $content);

        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        return $content;
    }

    /**
     * Adds ; to end of content
     *
     * @param string $content
     * @return string
     */
    protected function _processJsContent($content)
    {
        $trimmed = rtrim($content);
        if (substr($trimmed, -1) != ';') {
            $content = substr_replace($content, ';', strlen($trimmed), 0);
        }

        return $content;
    }

    /**
     * Replaces relative links for url() matches in css file
     *
     * @param array $match
     * @return string
     */
    protected function _cssMergerUrlCallback($match)
    {
        $quote = ($match[1][0] == "'" || $match[1][0] == '"') ? $match[1][0] : '';
        $uri = ($quote == '') ? $match[1] : substr($match[1], 1, strlen($match[1]) - 2);
        $uri = $this->_prepareUrl($uri);

        return "url({$quote}{$uri}{$quote})";
    }

    /**
     * Returns relative path for media resource in file
     *
     * Uses source and output file paths
     * Returns original uri if file path does not exists
     *
     * @param string $uri
     * @return string
     */
    protected function _prepareUrl($uri)
    {
        $origin = realpath(dirname($this->_sourceFile) . DS . $uri);
        $output = realpath(dirname($this->_outputFile));

        if ($origin === false) {
            return $uri;
        }

        $originPaths    = explode(DS, $origin);
        $outputPaths    = explode(DS, $output);
        $position       = 0;
        foreach ($originPaths as $pos => $path) {
            if (!isset($outputPaths[$pos])) {
                break;
            }
            if ($outputPaths[$pos] != $path) {
                break;
            }
            $position = $pos;
        }

        return sprintf('%s%s',
            str_repeat('../', count($outputPaths) - $position - 1),
            implode('/', array_slice($originPaths, $position + 1)));
    }

    /**
     * Merges JS file content and write to file
     *
     * @param Mage_Core_Model_Config_Element $xml
     */
    protected function _processJsFile(Mage_Core_Model_Config_Element $xml)
    {
        $type       = $xml->type ? (string)$xml->type : 'skin';
        $area       = $xml->area ? (string)$xml->area : 'frontend';
        $package    = $xml->package ? (string)$xml->package : 'base';
        $theme      = $xml->theme ? (string)$xml->theme : 'default';
        $path       = $xml->path ? (string)$xml->path : 'js';

        $filename   = sprintf('%s.js', $xml->filename);
        $minFile    = sprintf('%s.min.js', $xml->filename);

        if ($type == 'skin') {
            $path = str_replace('/', DS, $path);
            $this->_outputFile = Mage::getBaseDir('skin') . DS . $area
                . DS . $package . DS . $theme . DS . $path. DS . $filename;
            $this->_outMinFile = Mage::getBaseDir('skin') . DS . $area
                . DS . $package . DS . $theme . DS . $path. DS . $minFile;
        } else {
            return;
        }


        echo 'MERGE FILE ';
        echo str_replace(Mage::getBaseDir() . DS, '', $this->_outputFile);
        echo PHP_EOL;

        $content = $this->_getMergedContent($xml, 'js', $type, $area, $package, $theme, $path);

        file_put_contents($this->_outputFile, $content);

        echo 'MERGED SUCCESS' . PHP_EOL;
    }

    /**
     * Compresses JS file and write to file
     */
    protected function _compressJsFile()
    {
        if (!in_array($this->getArg('compress'), array('true', '1'))) {
            return;
        }

        echo 'COMPRESS FILE ';
        echo str_replace(Mage::getBaseDir() . DS, '', $this->_outputFile);
        echo PHP_EOL;

        // prepare compress options
        $options    = array();
        if (!in_array($this->getArg('js-optimize'), array('false', '0'))) {
            $options[] = '--disable-optimizations';
        }
        if (!in_array($this->getArg('js-munge'), array('false', '0'))) {
            $options[] = '--nomunge';
        }

        $options    = implode(' ', $options);
        $format     = 'java -jar "%s" --type js --charset utf-8 %s -o "%s" "%s"';
        $jarFile    = Mage::getBaseDir('lib') . DS . $this->_yuiCompressor;
        $command    = sprintf($format, $jarFile, $options, $this->_outMinFile, $this->_outputFile);
        exec($command, $output, $return);

        if ($return === 0) {
            echo 'COMPRESSED SUCCESS' . PHP_EOL;
        } else {
            echo 'COMPRESS FAILED' . PHP_EOL;
        }
    }

    /**
     * Returns Usage Help Message
     *
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f merge.php -- [options]

  --type (css|js|all)           Defines merge file type(s), default is all
  --compress (true|false)       Compresses merged files, default is true
  Filter options:
  --area (frontend|adminhtml)   Process only specified area instructions (optional)
  --package NAME                Process only specified package instructions (optional)
  --theme NAME                  Process only specified theme instructions (optional)
  Compress options:
  --js-optimize (true|false)    Enable JavaScript micro optimizations, default is false
  --js-munge (true|false)       Obfuscate JavaScript local symbols, default is true
  -h help                       This help

USAGE;
    }
}

$shell = new Mage_Shell_Merge();
$shell->run();
