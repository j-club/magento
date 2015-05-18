<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return Oro_GoogleTrustedStore_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('googletrustedstore/config');
    }

    /**
     * Sends subscribe requst email to Google group
     *
     * @param string $email Email for subscription
     */
    public function subscribeForUpdate($email)
    {
        $message = new Zend_Mail;
        $message->setFrom($email)
            ->addTo($this->_getConfig()->getSubscriptionEmail())
            ->setBodyText('')
            ->send();
    }

    /**
     * @param $store
     * @param $items
     * @param $tplSuccess
     * @param $tplError
     * @return $this
     */
    public function sendFeedNotification($store, $items, $tplSuccess, $tplError)
    {
        $body = '';
        $hasError = false;
        $allowedKeys = array('entity_name', 'store_name', 'error_message');
        foreach($items as $item) {
            if ($item['successfully']) {
                $itemMsg = $this->__($tplSuccess);
            } else {
                $itemMsg = $this->__($tplError);
                $hasError = true;
            }
            foreach($allowedKeys as $key) {
                $value = isset($item[$key]) ? $item[$key] : '';
                $itemMsg = str_replace("{{$key}}", $value, $itemMsg);
            }
            $body .= $itemMsg . PHP_EOL;
        }

        $email = $this->_getConfig()->getNotificationRecipientEmail($store);
        $subject = $this->_getConfig()->getNotificationSubject();
        $subject.= $hasError ? $this->__('Failure') : $this->__('Success');

        $mail = new Zend_Mail();
        $mail->setFrom($this->_getConfig()->getDefaultSenderEmail(), $this->_getConfig()->getDefaultSenderName());
        $mail->addTo($email);
        $mail->setSubject($subject);
        $mail->setBodyHtml(nl2br($body));

        try {
            $mail->send();
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
}
