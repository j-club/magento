<?php
/**
 * Oro_PageCache_Model_Resource_Crawler - backported a fix from EE 1.13.1
 *
 * @category   Oro
 * @package    Oro_PageCache
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_PageCache_Model_Resource_Crawler extends Enterprise_PageCache_Model_Resource_Crawler
{
    /**
     * Initialize application, adapter factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
        parent::__construct();
    }

    /**
     * Get store urls
     *
     * @param int $storeId
     * @return array
     */
    public function getRequestPaths($storeId)
    {
        $batchSize = Oro_PageCache_Model_Crawler::BATCH_SIZE;
        $offset = Mage::getSingleton('core/session')->getCrawlerOffset();
        $store = $this->_app->getStore($storeId);

        $rootCategoryId = $store->getRootCategoryId();

        $selectProduct = $this->_getReadAdapter()->select()
            ->from(array('url_product_default' => $this->getTable('enterprise_catalog/product')),
                   array(''))
            ->joinInner(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                        'url_rewrite.url_rewrite_id = url_product_default.url_rewrite_id',
                        array('request_path', 'entity_type')
            )
            ->joinInner(array('cp' => $this->getTable('catalog/category_product_index')),
                        'url_product_default.product_id = cp.product_id',
                        array('category_id')
            )
            ->where('url_rewrite.entity_type = ?', Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE)
            ->where('cp.store_id = ?', (int) $storeId)
            ->where('cp.category_id != ?', (int) $rootCategoryId)
            ->limit($batchSize, $offset);

        $selectCategory = $this->_getReadAdapter()->select()
            ->from(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                   array(
                       'request_path',
                       'entity_type',
                       'category_id' => new Zend_Db_Expr('NULL'),
                   )
            )
            ->where('url_rewrite.store_id = ?', $storeId)
            ->where('url_rewrite.entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE)
            ->limit($batchSize, $offset);

        $selectPaths = $this->_getReadAdapter()->select()
            ->union(array('(' . $selectProduct . ')', '(' . $selectCategory . ')'));

        return $this->_getReadAdapter()->fetchAll($selectPaths);
    }
}