<?php
namespace Zero1\GDPR\Controller\Anonymise;

use Zero1\GDPR\Helper\Data as Helper;
use Magento\Customer\Model\Session;

class Perform extends \Magento\Framework\App\Action\Action
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
     * @var Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Helper $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Helper $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->helper->getCanAnonymise()) {
            $this->helper->getMessageManager()->addError(__('The Anonymise Customer Feature Is Disabled'));
            return $this->_redirect('gdpr/anonymise/index');
        }

        $customer = $this->customerFactory->create()->load($this->customerSession->getCustomerId());
        if (!$customer->getId()) {
            $this->helper->getMessageManager()->addError(__('Customer Not Found'));
            return $this->_redirect('gdpr/anonymise/index');
        }

        // From \Magento\Customer\Controller\Account\Logout::execute() #31357
        $this->customerSession->logout()->setLastCustomerId($this->customerSession->getId());

        $this->helper->anonymiseCustomer($customer);
        $this->helper->getMessageManager()->addSuccess(__('Your account has been anonymised'));
        return $this->_redirect('customer/account/login');
    }
}
