<?php
namespace Zero1\GDPR\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GDPR_ACTIVE = 'gdpr/general/active';
    const XML_PATH_GDPR_GTM_TAG = 'gdpr/general/gtm_tag';
    const XML_PATH_GDPR_CAN_ANONYMISE = 'gdpr/general/can_anonymise';
    const XML_PATH_GDPR_CUSTOMER_MESSAGE = 'gdpr/general/customer_message';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Anonymise constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->messageManager = $messageManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Get Active config option value
     *
     * @return bool
     */
    public function getActive() {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GDPR_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Gtm Tag config option value
     *
     * @return bool
     */
    public function getGtmTag() {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GDPR_GTM_TAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Can Anonymise config option value
     *
     * @return bool
     */
    public function getCanAnonymise() {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GDPR_CAN_ANONYMISE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Customer Message config option value
     *
     * @return bool
     */
    public function getCustomerMessage() {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GDPR_CUSTOMER_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function anonymiseCustomerOrders($customer) {
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId());
        foreach ($orders as $order) {
            $this->anonymiseSaleData($order);
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function anonymiseCustomerQuotes($customer) {
        $quotes = $this->quoteCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId());
        foreach ($quotes as $quote) {
            $this->anonymiseSaleData($quote);
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function anonymiseCustomerNewsletters($customer) {
        $subscriber = $this->subscriberFactory->create()->loadByCustomerId($customer->getId());
        if ($subscriber) {
            $subscriber->delete();
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function anonymiseCustomer($customer) {
        if ($customer->getId()) {
            $isSecure = $this->registry->registry('isSecureArea');
            if (!$isSecure) {
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', true);
            }

            $this->anonymiseCustomerOrders($customer);
            $this->anonymiseCustomerQuotes($customer);
            $this->anonymiseCustomerNewsletters($customer);
            $customer->delete();

            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecure);
        }
    }

    /**
     * Remove customer details from the address
     *
     * @param \Magento\Sales\Model\Order\Address|\Magento\Quote\Model\Quote\Address $address
     */
    protected function anonymiseSaleAddress(&$address) {
        $address->setFirstname($this->getRandom());
        $address->setMiddlename($this->getRandom());
        $address->setLastname($this->getRandom());
        $address->setCompany($this->getRandom());
        $address->setEmail($this->getRandom('email'));
        $address->setRegion($this->getRandom());
        $address->setStreet($this->getRandom());
        $address->setCity($this->getRandom());
        $address->setPostcode($this->getRandom());
        $address->setTelephone($this->getRandom());
        $address->setFax($this->getRandom());
    }

    /**
     * Remove customer details from a quote or order
     *
     * @param \Magento\Sales\Model\Order|\Magento\Quote\Model\Quote $obj
     */
    protected function anonymiseSaleData(&$obj) {
        $obj->setCustomerFirstname($this->getRandom());
        $obj->setCustomerMiddlename($this->getRandom());
        $obj->setCustomerLastname($this->getRandom());
        $obj->setCustomerEmail($this->getRandom('email'));
        $billingAddress = $obj->getBillingAddress();
        $shippingAddress = $obj->getShippingAddress();
        $this->anonymiseSaleAddress($billingAddress);
        $this->anonymiseSaleAddress($shippingAddress);
        $obj->save();
    }

    /**
     * @param string $type
     * @return null|string
     */
    protected function getRandom($type = 'str') {
        $rand = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,10))),1,10);
        if ($type == 'str') {
            return $rand;
        } else if ($type == 'email') {
            return $rand.'@'.$rand.'.com';
        }
        return null;
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessageManager() {
        return $this->messageManager;
    }
}
