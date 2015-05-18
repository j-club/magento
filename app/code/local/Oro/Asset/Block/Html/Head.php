<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset Html Head Block
 */
class Oro_Asset_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    /**
     * Defines files to load
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $helper = Mage::helper('oro_asset');
        if ($helper->isEnabled() && $helper->getStyle() != Oro_Asset_Helper_Data::STYLE_DEFAULT) {
            $config = Mage::getSingleton('oro_asset/merge');

            $cssFiles = $config->getCssFiles();
            $jsFiles = $config->getJsFiles();

            $fileKey = $helper->getStyle() == Oro_Asset_Helper_Data::STYLE_MERGE_MIN ? 'file_min' : 'file';

            $allItems = array();

            foreach ($cssFiles as $item) {
                $removed = 0;
                foreach ($item['remove'] as $entity) {
                    list($type, $file) = $entity;
                    $removed += $this->isItemExists($type, $file) ? 1 : 0;
                    $this->removeItem($type, $file);
                }

                if ($removed == 0 && $item['behavior'] == 'replace') {
                    continue;
                }

                $allItems['skin_css/' . $item[$fileKey]] = array(
                    'type' => 'skin_css',
                    'name' => $item[$fileKey],
                    'params' => !empty($item['params']) ? $item['params'] : 'media="all"',
                    'if' => $item['if'],
                    'cond' => null,
                );
            }

            foreach ($jsFiles as $item) {
                $removed = 0;
                foreach ($item['remove'] as $entity) {
                    list($type, $file) = $entity;
                    $removed += $this->isItemExists($type, $file) ? 1 : 0;
                    $this->removeItem($type, $file);
                }

                if ($removed == 0 && $item['behavior'] == 'replace') {
                    continue;
                }

                $allItems['skin_js/' . $item[$fileKey]] = array(
                    'type' => 'skin_js',
                    'name' => $item[$fileKey],
                    'params' => $item['params'],
                    'if' => $item['if'],
                    'cond' => null,
                );
            }

            // reset sorting
            $this->_data['items'] = $this->_data['items'] + $allItems;
        }

        return $this;
    }

    /**
     * Checks is item exists
     *
     * @param string $type
     * @param string $file
     * @return bool
     */
    public function isItemExists($type, $file)
    {
        $itemId = sprintf('%s/%s', $type, $file);

        return array_key_exists($itemId, $this->_data['items']);
    }

    /**
     * Changes output file format and add version
     *
     * @param string $format
     * @param array $staticItems
     * @param array $skinItems
     * @param null|callback $mergeCallback
     * @return string
     */
    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems,
                                                      $mergeCallback = null)
    {
        $helper = Mage::helper('oro_asset');
        if ($helper->isEnabled()) {
            // css
            if (preg_match('#<link.*href="([^"]+)"#', $format, $match)) {
                $search  = sprintf('href="%s"', $match[1]);
                $replace = sprintf('href="%s?ver=%d"', $match[1], $helper->getCssVersion());
                $format  = str_replace($search, $replace, $format);
            }
            // js
            if (preg_match('#<script.*src="([^"]+)"#', $format, $match)) {
                $search  = sprintf('src="%s"', $match[1]);
                $replace = sprintf('src="%s?ver=%d"', $match[1], $helper->getCssVersion());
                $format  = str_replace($search, $replace, $format);
            }

            foreach ($staticItems as &$rows) {
                foreach ($rows as &$name) {
                    $pos = strpos($name, '?');
                    if ($pos !== false) {
                        $name = substr($name, 0, $pos);
                    }
                }
            }
            foreach ($skinItems as &$rows) {
                foreach ($rows as &$name) {
                    $pos = strpos($name, '?');
                    if ($pos !== false) {
                        $name = substr($name, 0, $pos);
                    }
                }
            }
        }

        return parent::_prepareStaticAndSkinElements($format, $staticItems, $skinItems, $mergeCallback);
    }


}
