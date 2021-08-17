<?php
namespace Zero1\GDPR\Block\Adminhtml\Edit;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Zero1\GDPR\Helper\Data as Helper;

class Anonymise extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper
    ) {
        parent::__construct($context, $registry);
        $this->helper = $helper;
    }

    /**
     * Returns anonymise button data
     *
     * @return array
     */
    public function getButtonData() {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId && $this->helper->getCanAnonymise()) {
            $data = [
                'label' => __('Anonymise'),
                'class' => 'anonymise anonymise-customer',
                'on_click' => sprintf("if(confirm('%s')){location.href = '%s';}",
                    __('This will anonymise this customer permanently. It will delete the customer account and anonymise Sales and Quote data.'),
                    $this->getUrl('gdpr/anonymise/index', ['customer_id' => $customerId])
                ),
                'sort_order' => 50,
            ];
        }
        return $data;
    }
}
