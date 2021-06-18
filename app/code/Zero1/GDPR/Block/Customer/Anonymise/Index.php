<?php
namespace Zero1\GDPR\Block\Customer\Anonymise;

use Zero1\GDPR\Helper\Data as Helper;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        Helper $helper
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return Helper
     */
    public function getHelper() {
        return $this->helper;
    }
}
