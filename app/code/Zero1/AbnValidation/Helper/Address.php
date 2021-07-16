<?php
namespace Zero1\AbnValidation\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Customer address helper
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Address extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_ABN_VALIDATION_ENABLED = 'customer/create_account/abn_auto_group_assign';

    const CONFIG_PATH_ABN_ON_EACH_TRANSACTION = 'customer/create_account/abn_on_each_transaction';

    const CONFIG_PATH_ABN_TAX_CALCULATION_ADDRESS_TYPE = 'customer/create_account/abn_tax_calculation_address_type';

    const CONFIG_PATH_ABN_FRONTEND_VISIBILITY = 'customer/create_account/abn_frontend_visibility';

    const CONFIG_PATH_ABN_VALID_GROUP = 'customer/create_account/abn_default_group';

    const CONFIG_PATH_ABN_ERROR_GROUP = 'customer/create_account/abn_error_group';

    /**
     * Possible customer address types
     */
    const TYPE_BILLING = 'billing';

    const TYPE_SHIPPING = 'shipping';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Address constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     */
    public function isABNValidationEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ABN_VALIDATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve 'validate on each transaction' value
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     */
    public function hasValidateOnEachTransaction($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ABN_ON_EACH_TRANSACTION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve customer address type on which tax calculation must be based
     *
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return string
     */
    public function getTaxCalculationAddressType($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_ABN_TAX_CALCULATION_ADDRESS_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if ABN address attribute has to be shown on frontend (on Customer Address management forms)
     *
     * @return boolean
     */
    public function isABNAttributeVisible()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ABN_FRONTEND_VISIBILITY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return int
     */
    public function getABNValidCustomerGroupId($store = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_ABN_VALID_GROUP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return int
     */
    public function getABNErrorCustomerGroupId($store = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_PATH_ABN_ERROR_GROUP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
