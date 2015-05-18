<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sociable
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Sociable_Model_Observer extends Be_Sociable_Model_Observer
{
    public function productSaveBefore(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getDataObject();
        if ($product) {
            if ($product->isObjectNew()) {
                $product->setSociableIsNew(true);
            }
        }
        return $this;
    }

    public function productSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getDataObject();
        if ($product) {
            $actionType = $product->getSociableIsNew() ? 'new' : 'edit';
            if ($product->getData('visible_in_social')) {
                $this->scheduleProduct($product, $actionType);
            }
        }
        return $this;
    }

    public function getRandomProducts($numProducts){
        if($numProducts > 0){
            $products = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', 1)//enabled
                ->addAttributeToFilter('visibility', '4')
                ->addFieldToFilter('visible_in_social', array('eq' => 1));
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);

            $products->getSelect()->order(new Zend_Db_Expr('RAND()'));
            $products->getSelect()->limit($numProducts);

            return $products;
        }
        return false;
    }

    public function checkRandomProducts($currentDate, $network){
        $collection = Mage::getModel('sociable/cron')->getCollection()
            ->addFieldToFilter('post_date', $currentDate)
            ->addFieldToFilter('done', 0)
            ->addFieldToFilter('post_type', 'random')
            ->addFieldToFilter('network', $network);
        if($collection->count()){
            return true;
        }
    
        return false;
    }
    
    public function scheduleProduct($product, $type)
    {
        $productId = $product->getId();
        if (Mage::getStoreConfigFlag('besociable/facebook/enabled')) {
            $this->scheduleCron($productId, $type, 'facebook');
        }
        if (Mage::getStoreConfigFlag('besociable/twitter/enabled')) {
            $this->scheduleCron($productId, $type, 'twitter');
        }
        if (Mage::getStoreConfigFlag('besociable/gplus/enabled')) {
            $this->scheduleCron($productId, $type, 'gplus');
        }
        if (Mage::getStoreConfigFlag('besociable/pinterest/enabled')) {
            $this->scheduleCron($productId, $type, 'pinterest');
        }
    }

    public function scheduleCron($productId, $type, $network)
    {
        $currentDate = $this->getDate();
        $cron = Mage::getModel('sociable/cron')
            ->setProductId($productId)
            ->setPostDate($currentDate)
            ->setNetwork($network)
            ->setPostType($type)
            ->setDone(false)
            ->save();
    }

    public function getDate()
    {
        $date = getdate(Mage::getModel('core/date')->timestamp(time()));
        $currentDate = $date['year'].$date['mon'].$date['mday'];
        return $currentDate;
    }

    public function processProduct($productId, $type){
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getData('visible_in_social')) {
            return false;
        }
        $url = $product->getProductUrl();//$this->getFrontendUrl('').$product->getUrlPath();

        $result = array();

        if (!$product->getImage() || ($product->getImage() == 'no_selection')) {
            return $result;
        }
        $picturePath = '';
        $picture = $this->getImage($product);

        if($picture != ''){
            $picture = Mage::helper('catalog/image')->init($product, 'image')->resize(300)->__toString();
        }
        $facebookEnable = Mage::getStoreConfigFlag('besociable/facebook/enabled');
        if($facebookEnable){
            $re = $this->handleFacebook($product, $picture, $type, $url);
            $re = true;
            if($re){
                $result['facebook'] = 'ok';
            }
        }

        $twitterEnable = Mage::getStoreConfigFlag('besociable/twitter/enabled');
        if($twitterEnable){
            $re = $this->handleTwitter($product, $picture, $picturePath, $type, $url);
            if($re){
                $result['twitter'] = 'ok';
            }
        }

        $gplusEnable = Mage::getStoreConfigFlag('besociable/gplus/enabled');
        if($gplusEnable){
            $re = $this->handleGplus($product, $picture, $type, $url);
            if($re){
                $result['gplus'] = 'ok';
            }
        }

        $pinterestEnable = Mage::getStoreConfigFlag('besociable/pinterest/enabled');
        if($pinterestEnable){
            $re = $this->handlePinterest($product, $picture, $type, $url);
            if($re){
                $result['pinterest'] = 'ok';
            }
        }

        return $result;
    }

    public function processQueue()
    {
        $currentDate = $this->getDate();
        $facebookEnable = Mage::getStoreConfigFlag('besociable/facebook/enabled');
        if($facebookEnable){
            //Check if we have already generated the random products
            $check = $this->checkRandomProducts($currentDate, 'facebook');
            if (!$check) {
                $randomNumber = (int)Mage::getStoreConfig('besociable/facebook/randompost');
                $randomProducts = $this->getRandomProducts($randomNumber);
                
                if($randomProducts){
                    foreach ($randomProducts as $item){
                        $cron = Mage::getModel('sociable/cron')
                            ->setProductId($item->getId())
                            ->setPostDate($currentDate)
                            ->setNetwork('facebook')
                            ->setDone(false)
                            ->save();
                    }
                }
            }

            //Process all scheduled products
            $collection = Mage::getModel('sociable/cron')->getCollection()
                ->addFieldToFilter('post_date', $currentDate)
                ->addFieldToFilter('done', 0)
                ->addFieldToFilter('network', 'facebook');
            $processedProductIds = array();

            if ($collection->count()) {
                //process all products
                foreach ($collection as $cron) {
                    $productId = $cron->getProductId();
                    if (in_array($productId, $processedProductIds)) {
                        continue;
                    }
                    $re = $this->processProduct($productId, $cron->getPostType());
                    if ((isset($re['facebook']) && $re['facebook']=='ok') || true) {
                        $cron->setDone(1);
                        $cron->save();
                    }
                }
            }
        }
    }
}
