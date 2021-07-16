<?php
namespace Zero1\AbnValidation\Observer;

use Zero1\AbnValidation\Model\AbnValidator;
use Zero1\AbnValidation\Helper\Address as AbnHelper;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\GroupManagementInterface;

abstract class AbstractAbnValidationObserver
{
    /**
     * @var AbnHelper
     */
    protected $abnHelper;

    /**
     * @var AbnValidator
     */
    protected $abnValidator;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        AbnValidator $abnValidator,
        AbnHelper $abnHelper,
        GroupManagementInterface $groupManagement,
        LoggerInterface $loggerInterface
    ){
        $this->abnValidator = $abnValidator;
        $this->abnHelper = $abnHelper;
        $this->groupManagement = $groupManagement;
        $this->logger = $loggerInterface;
    }

    protected function isEnabled(
        $customer, 
        $customerAddress,
        $storeId
    ){
        $debugData = [
            'customer' => $customer->getId(),
            'customer_address' => $customerAddress->getId(),
            'store' => $storeId,
        ];
        $this->logger->debug(__FILE__.':'.__LINE__, $debugData);

        if(!$this->abnHelper->isABNValidationEnabled()
            || $customerAddress->getShouldIgnoreValidation()
        ){
            return false;
        }

        return true;
    }

    protected function validate($customer, $customerAddress, $storeId)
    {
        $validationResult = [
            'validation' => null,
            'new_group' => null,
        ];
        $abn = $customerAddress->getData('abn_id');
        $currentGroupId = $customer->getGroupId();
        $debugData = [
            'abn' => $abn,
            'current_group' => $currentGroupId,
            'store' => $storeId,
        ];
        $this->logger->debug(__FILE__.':'.__LINE__, $debugData);

        $abnValid = false;
        // maybe in future will need to check if this is an australian address
        if(!$abn){
            $newGroupId = $this->groupManagement->getDefaultGroup($storeId)->getId();
        }else{
            $abnValid = $this->abnValidator->isValid($abn);
            $validationResult['validation'] = $abnValid;
            if($abnValid){
                $newGroupId = $this->abnHelper->getABNValidCustomerGroupId($storeId);
            }else{
                $newGroupId = $this->abnHelper->getABNErrorCustomerGroupId($storeId);
            }
        }

        if($currentGroupId != $newGroupId){
            $validationResult['new_group'] = $newGroupId;
            $debugData['new_group'] = $newGroupId;
        }
        $this->logger->debug(__FILE__.':'.__LINE__, [
            'debug' => $debugData,
            'validation_result' => $validationResult,
        ]);

        return $validationResult;
    }
}