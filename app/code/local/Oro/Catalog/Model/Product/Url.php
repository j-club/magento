<?php

class Oro_Catalog_Model_Product_Url extends Enterprise_Catalog_Model_Product_Url
{
    static protected $_categories = array();
    /**
     * Retrieve product URL based on requestPath param
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $requestPath
     * @param array $routeParams
     *
     * @return string
     */
    protected function _getProductUrl($product, $requestPath, $routeParams)
    {
        $categoryId = $this->_getCategoryIdForUrl($product, $routeParams);

        if (!empty($requestPath)) {
            if ($categoryId) {
                $category = $this->_getCategoryModel($categoryId);
                if ($category->getId()) {
                    $categoryRewrite = $this->_factory->getModel('enterprise_catalog/category')
                        ->loadByCategory($category);
                    if ($categoryRewrite->getId()) {
                        $requestPath = $categoryRewrite->getRequestPath() . '/' . $requestPath;
                    }
                }
            }
            $product->setRequestPath($requestPath);

            $storeId = $this->getUrlInstance()->getStore()->getId();
            $requestPath = $this->_factory->getHelper('enterprise_catalog')
                ->getProductRequestPath($requestPath, $storeId);

            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
        }

        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        if ($categoryId) {
            $routeParams['category'] = $categoryId;
        }
        return $this->getUrlInstance()->getUrl('catalog/product/view', $routeParams);
    }

    protected function _getCategoryModel($categoryId)
    {
        if (!isset(self::$_categories[$categoryId])) {
            self::$_categories[$categoryId] = $this->_factory
                ->getModel('catalog/category', array('disable_flat' => true))
                ->load($categoryId);
        }

        return self::$_categories[$categoryId];
    }
}