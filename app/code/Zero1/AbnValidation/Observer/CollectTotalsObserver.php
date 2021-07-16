<?php
namespace Zero1\AbnValidation\Observer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Zero1\AbnValidation\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Zero1\AbnValidation\Model\AbnValidator;
use Psr\Log\LoggerInterface;

/**
 * Handle customer ABN on collect_totals_before event of quote address.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CollectTotalsObserver extends AbstractAbnValidationObserver implements ObserverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Vat
     */
    protected $customerVat;

    /**
     * @var AbnValidator
     */
    protected $abnValidator;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * Initialize dependencies.
     *
     * @param Address $customerAddressHelper
     * @param Vat $customerVat
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param GroupManagementInterface $groupManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Session $customerSession
     */
    public function __construct(
        Address $customerAddressHelper,
        Vat $customerVat,
        CustomerInterfaceFactory $customerDataFactory,
        GroupManagementInterface $groupManagement,
        AddressRepositoryInterface $addressRepository,
        Session $customerSession,
        AbnValidator $abnValidator,
        LoggerInterface $loggerInterface
    ) {
        $this->customerVat = $customerVat;
        $this->customerDataFactory = $customerDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->abnValidator = $abnValidator;
        return parent::__construct(
            $abnValidator,
            $customerAddressHelper,
            $groupManagement,
            $loggerInterface
        );
    }

    /**
     * Handle customer ABN if needed on collect_totals_before event of quote address
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $observer->getShippingAssignment();
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        /** @var Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();

        $customer = $quote->getCustomer();
        $debugData = [
            'customer' => $customer->getId(),
            'customer_address' => $address->getId(),
            'store' => $customer->getStoreId(),
        ];
        $this->logger->debug(__FILE__.':'.__LINE__, $debugData);

        if(!$this->isEnabled($customer, $address, $customer->getStoreId())){
            $this->logger->debug(__FILE__.':'.__LINE__, $debugData);
            return;
        }

        $configAddressType = $this->abnHelper->getTaxCalculationAddressType($customer->getStoreId());
        // When VAT is based on billing address then Magento have to handle only billing addresses
        $additionalBillingAddressCondition = $configAddressType ==
            \Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING ? $configAddressType !=
            $address->getAddressType() : false;

        if($additionalBillingAddressCondition 
            || $customer->getDisableAutoGroupChange()
            || !$this->abnHelper->hasValidateOnEachTransaction($customer->getStoreId())
        ){
            return;
        }

        $validationResult = $this->validate($customer, $address, $customer->getStoreId());
        $newGroupId = $validationResult['new_group'];

        if ($newGroupId !== null) {
            $address->setPrevQuoteCustomerGroupId($quote->getCustomerGroupId());
            $quote->setCustomerGroupId($newGroupId);
            $this->customerSession->setCustomerGroupId($newGroupId);
            $customer->setGroupId($newGroupId);
            $customer->setEmail($customer->getEmail() ?: $quote->getCustomerEmail());
            $quote->setCustomer($customer);
        }
    }
}
