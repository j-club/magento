<?php
/**
 * Oro_PageCache_Model_Crawler - backported a fix from EE 1.13.1
 *
 * @category   Oro
 * @package    Oro_PageCache
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_PageCache_Model_Crawler extends Enterprise_PageCache_Model_Crawler
{
    /**
     * Batch size
     */
    const BATCH_SIZE = 500;

    /**
     * Factory
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Initialize application, adapter factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_adapterFactory = !empty($args['adapter_factory']) ? $args['adapter_factory'] :
            Mage::getSingleton('enterprise_pagecache/adapter_factory');
        parent::__construct($args);
    }

    /**
     * Prepares and executes requests by given request_paths values
     *
     * @param array $info
     * @param Varien_Http_Adapter_Curl $adapter
     */
    protected function _executeRequests(array $info, Varien_Http_Adapter_Curl $adapter)
    {
        $storeId = $info['store_id'];
        $options = array(
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => 0,
        );
        $threads = $this->_getCrawlerThreads($storeId);
        if (!$threads) {
            $threads = 1;
        }
        if (!empty($info['cookie'])) {
            $options[CURLOPT_COOKIE] = $info['cookie'];
        }
        $urls = array();

        $offset = 0;
        Mage::getSingleton('core/session')->setCrawlerOffset($offset);
        while ($rewrites = $this->_getResource()->getRequestPaths($storeId)) {
            foreach ($rewrites as $rewriteRow) {
                $url = $this->_getUrlByRewriteRow($rewriteRow, $info['base_url'], $storeId);
                $urls[] = $url;
                if (count($urls) == $threads) {
                    $adapter->multiRequest($urls, $options);
                    $urls = array();
                }
            }
            $offset += self::BATCH_SIZE;
            Mage::getSingleton('core/session')->setCrawlerOffset($offset);
        }
        if (!empty($urls)) {
            $adapter->multiRequest($urls, $options);
        }
    }

    /**
     * Get url by rewrite row
     *
     * @param array $rewriteRow
     * @param string $baseUrl
     * @param int $storeId
     * @return string
     * @throws Exception
     */
    protected function _getUrlByRewriteRow($rewriteRow, $baseUrl, $storeId)
    {
        switch ($rewriteRow['entity_type']) {
            case Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE:
                $url = $baseUrl . $this->_factory->getHelper('enterprise_catalog')->getProductRequestPath(
                        $rewriteRow['request_path'], $storeId, $rewriteRow['category_id']
                    );
                break;
            case Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE:
                $url = $baseUrl . $this->_factory->getHelper('enterprise_catalog')->getCategoryRequestPath(
                        $rewriteRow['request_path'], $storeId
                    );
                break;
            default:
                throw new Exception('Unknown entity type ' . $rewriteRow['entity_type']);
                break;
        }
        return $url;
    }
}