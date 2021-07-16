<?php
declare(strict_types=1);

namespace Zero1\AbnValidation\Observer;

use Magento\Customer\Api\GroupManagementInterface;
use Zero1\AbnValidation\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Zero1\AbnValidation\Model\AbnValidator;

/**
 * Customer Observer Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterAddressSaveObserver extends AbstractAbnValidationObserver implements ObserverInterface
{
    /**
     * VAT ID validation processed flag code
     */
    const ABN_VALIDATION_FLAG  = 'abn_validation_flag';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Vat
     */
    protected $_customerVat;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Vat $customerVat
     * @param HelperAddress $customerAddress
     * @param Registry $coreRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param AppState $appState
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Vat $customerVat,
        HelperAddress $customerAddress,
        Registry $coreRegistry,
        GroupManagementInterface $groupManagement,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Escaper $escaper,
        AppState $appState,
        CustomerSession $customerSession,
        AbnValidator $abnValidator,
        LoggerInterface $loggerInterface
    ) {
        $this->_customerVat = $customerVat;
        $this->_coreRegistry = $coreRegistry;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->appState = $appState;
        $this->customerSession = $customerSession;
        return parent::__construct(
            $abnValidator,
            $customerAddress,
            $groupManagement,
            $loggerInterface
        );
    }

    /**
     * Address after save event handler
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();
        $debugData = [
            'customer' => $customer->getId(),
            'customer_address' => $customerAddress->getId(),
            'store' => $customer->getStore()->getId(),
        ];
        $this->logger->debug(__FILE__.':'.__LINE__, $debugData);

        if(!$this->isEnabled($customer, $customerAddress, $customer->getStore()->getId())){
            $this->logger->debug(__FILE__.':'.__LINE__, $debugData);
            return;
        }

        if ($this->_coreRegistry->registry(self::ABN_VALIDATION_FLAG)
            || !$this->_canProcessAddress($customerAddress)
        ) {
            $this->logger->debug(__FILE__.':'.__LINE__, $debugData);
            return;
        }

        try {
            $this->_coreRegistry->register(self::ABN_VALIDATION_FLAG, true);

            $validationResult = $this->validate($customer, $customerAddress, $customer->getStore()->getId());
            $validation = $validationResult['validation'];
            $newGroupId = $validationResult['new_group'];

            if($newGroupId !== null){

                $customer->setGroupId($newGroupId);
                $customer->save();
                $this->customerSession->setCustomerGroupId($newGroupId);

                if($validation !== null && $this->appState->getAreaCode() == Area::AREA_FRONTEND){
                    if($validation){
                        $this->addValidMessage($customerAddress);
                    }else{
                        $this->addInvalidMessage($customerAddress);
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->debug(__FILE__.':'.__LINE__, $debugData);
            $this->_coreRegistry->register(self::ABN_VALIDATION_FLAG, false, true);
            $this->logger->error('Issue in '.__METHOD__.', error was: '.$e->getMessage(), $debugData);
        }
    }

    /**
     * Check whether specified address should be processed in after_save event handler
     *
     * @param Address $address
     * @return bool
     */
    protected function _canProcessAddress($address)
    {
        if ($address->getForceProcess()) {
            return true;
        }
        
        $configAddressType = $this->abnHelper->getTaxCalculationAddressType();
        if ($configAddressType == AbstractAddress::TYPE_SHIPPING) {
            return $this->_isDefaultShipping($address);
        }

        return $this->_isDefaultBilling($address);
    }

    /**
     * Check whether specified billing address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultBilling()
            || $address->getIsPrimaryBilling()
            || $address->getIsDefaultBilling();
    }

    /**
     * Check whether specified shipping address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultShipping()
            || $address->getIsPrimaryShipping()
            || $address->getIsDefaultShipping();
    }

    /**
     * Add success message for valid VAT ID
     *
     * @param Address $customerAddress
     * @param DataObject $validationResult
     * @return $this
     */
    protected function addValidMessage($customerAddress)
    {
        $this->messageManager->addSuccess((string)__('Your ABN was successfully validated.'));
        return $this;
    }

    /**
     * Add error message for invalid VAT ID
     *
     * @param Address $customerAddress
     * @return $this
     */
    protected function addInvalidMessage($customerAddress)
    {
        $this->messageManager->addErrorMessage((string)__('Unable to validate the entered ABN'));
        return $this;
    }
}
