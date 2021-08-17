<?php
namespace Zero1\GDPR\Controller\Adminhtml\Anonymise;

use Zero1\GDPR\Helper\Data as Helper;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var Helper $helper
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Helper $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Helper $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if (!$this->helper->getCanAnonymise()) {
            $this->helper->getMessageManager()->addError(__('The Anonymise Customer Feature Is Disabled'));
            return $this->_redirect('customer/index/index');
        }

        $customer = $this->customerFactory->create()->load($this->getRequest()->getParam('customer_id'));
        if (!$customer->getId()) {
            $this->helper->getMessageManager()->addError(__('Customer Not Found'));
            return $this->_redirect('customer/index/index');
        }

        $this->helper->anonymiseCustomer($customer);
        $this->helper->getMessageManager()->addSuccess(__('Customer Anonymised'));
        return $this->_redirect('customer/index/index');
    }
}