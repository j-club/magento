<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento enterprise edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento enterprise edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancednewsletter
 * @version    2.3.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancednewsletter_Model_Template extends Mage_Newsletter_Model_Template {
    const TEST_EMAIL = 'advancednewsletter/general/test_email';

    public function _construct() {
        $this->_init('advancednewsletter/template');
    }

    /**
     * Sending activation mail
     * @param AW_Advancednewsletter_Model_Subscriber $subscriber
     * @return AW_Advancednewsletter_Model_Template
     */
    public function sendActivationMail($subscriber) {
        $subscriber->setConfirmationLink(Mage::getUrl('advancednewsletter/index/activate', array('confirm_code' => $subscriber->getConfirmCode(), 'email' => $subscriber->getEmail())));
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        Mage::getModel('core/email_template')->sendTransactional(
                Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRM_EMAIL_TEMPLATE), Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRM_EMAIL_IDENTITY), $subscriber->getEmail(), $subscriber->getLastName() . " " . $subscriber->getFirstName(), array('subscriber' => $subscriber)
        );
        $translate->setTranslateInline(true);
        return $this;
    }

    /**
     * Sending first subscription mail
     * @param AW_Advancednewsletter_Model_Subscriber $subscriber
     * @return AW_Advancednewsletter_Model_Template
     */
    public function sendFirstSubscribeMail($subscriber) {
        $subscriber->setUnsubscribeAllLink($subscriber->getUnsubscribeAllLink());

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        Mage::getModel('core/email_template')->sendTransactional(
                Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_SUCCESS_EMAIL_TEMPLATE), Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_SUCCESS_EMAIL_IDENTITY), $subscriber->getEmail(), $subscriber->getLastName() . " " . $subscriber->getFirstName(), array('subscriber' => $subscriber)
        );
        $translate->setTranslateInline(true);
        return $this;
    }

    /**
     * Sending unsubscribe from all mail
     * @param AW_Advancednewsletter_Model_Subscriber $subscriber
     * @return AW_Advancednewsletter_Model_Template 
     */
    public function sendUnsubscribeMail($subscriber) {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        Mage::getModel('core/email_template')->sendTransactional(
                Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE), Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY), $subscriber->getEmail(), $subscriber->getLastName() . " " . $subscriber->getFirstName(), array('subscriber' => $subscriber)
        );
        $translate->setTranslateInline(true);
        return $this;
    }

    /*
     * Retrieve included template
     *
     * @param string $templateCode
     * @param array $variables
     * @return string
     */

    public function getInclude($templateCode, array $variables) {
        return Mage::getModel('advancednewsletter/template')
                        ->loadByCode($templateCode)
                        ->getProcessedTemplate($variables);
    }

    /**
     * Load template by code
     *
     * @param string $templateCode
     * @return Mage_Newsletter_Model_Template
     */
    public function loadByCode($templateCode) {
        $this->_getResource()->loadByCode($this, $templateCode);
        return $this;
    }

    /**
     * Send mail to subscriber
     *
     * @param   AW_Advancednewsletter_Model_Subscriber|string   $subscriber   subscriber Model or E-mail
     * @param   array                                           $variables    template variables
     * @param   string|null                                     $name         receiver name (if subscriber model not specified)
     * @param   AW_Advancednewsletter_Model_Queue|null          $queue        queue model
     * @return boolean
     * */
    public function send($subscriber, array $variables = array(), $name=null, Mage_Newsletter_Model_Queue $queue=null) {
        if (!$this->isValidForSend()) {
            return false;
        }

        $email = '';
        if ($subscriber instanceof AW_Advancednewsletter_Model_Subscriber) {
            $email = $subscriber->getEmail();
            $name = $subscriber->getFirstName() . ' ' . $subscriber->getLastName();
            $variables['awunsubscribefromsegment'] = $this->getUnsubscriptionSegmentLink($subscriber);
            $variables['awunsubscribe'] = '<a href="' . $subscriber->getUnsubscriptionLink() . '">Unsubscribe</a>';
            $variables['awloginpage'] = '<a href="' . Mage::app()->getStore($subscriber->getStoreId())->getBaseUrl() . 'customer/account/login/">Manage subscriptions</a>';
        } else {
            $email = (string) $subscriber;           
            $subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($email);
            $variables['awunsubscribe'] = $subscriber->getId() ?
                    '<a href="' . $subscriber->getUnsubscriptionLink() . '">Unsubscribe</a>' :
                    '<p style="text-decoration:underline;">' . Mage::helper('advancednewsletter')
                            ->__('You can unsubscribe from our newsletter in your account.')
                    . '</p>';
            $variables['awloginpage'] = '<a href="' . Mage::getBaseUrl() . 'customer/account/login/">Manage subscriptions</a>';
            $variables['awunsubscribefromsegment'] = $this->getUnsubscriptionSegmentLink($subscriber);
        }

        if (Mage::getStoreConfigFlag(Mage_Newsletter_Model_Subscriber::XML_PATH_SENDING_SET_RETURN_PATH)) {
            $this->getMail()->setReturnPath($this->getTemplateSenderEmail());
        }

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();
        $mail->addTo($email, $name);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        try {
            $mail->setSubject($this->getProcessedTemplateSubject($variables));
            $mail->setFrom($this->getTemplateSenderEmail(), $this->getTemplateSenderName());
        } catch (Exception $ex) {
            
        }

        $smtp = Mage::getModel('advancednewsletter/smtp')->load($this->getSmtpId());
        $transport = null;
        if ($smtp->getId()) {
            if ($smtp->getUsessl())
                $config = array('ssl' => 'tls', 'port' => $smtp->getPort(), 'auth' => 'login', 'username' => $smtp->getUserName(), 'password' => $smtp->getPassword());
            else
                $config = array('port' => $smtp->getPort(), 'auth' => 'login', 'username' => $smtp->getUserName(), 'password' => $smtp->getPassword());
            $transport = new Zend_Mail_Transport_Smtp($smtp->getServerName(), $config);
        }

        try {
            if ($transport)
                $mail->send($transport);
            else
                $mail->send();
            $this->_mail = null;
            if (!is_null($queue)) {
                $subscriber->received($queue);
            }
        } catch (Exception $e) {
            Mage::helper('awcore/logger')->log($this, $subscriber->getEmail().' - '.$e->getMessage());
            return false;
        }

        return true;
    }

    public function getTemplateText() {
        if (!$this->getData('template_text') && !$this->getId()) {
            $this->setData('template_text', Mage::helper('newsletter')->__('Follow this link to unsubscribe {{var awunsubscribe}}')
            );
        }

        return $this->getData('template_text');
    }
    
    public function getUnsubscriptionSegmentLink($subscriber)
    {
        return Mage::getUrl("advancednewsletter/index/unsubscribe", array(
                    'email' => $subscriber->getEmail(),
                    'segments_codes' => implode(',', $this->getSegmentsCodes()),
                    'key' => Mage::helper('advancednewsletter')->encrypt($subscriber->getId()),
                    '_store' => $subscriber->getStoreId()
                ));
    }

}
