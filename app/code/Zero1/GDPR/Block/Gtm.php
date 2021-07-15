<?php
namespace Zero1\GDPR\Block;

use Zero1\GDPR\Helper\Data as Helper;

class Gtm extends \Magento\Framework\View\Element\Template
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

    /**
     * @return string
     */
    public function getCookieAcceptedName() {
        return \Magento\Cookie\Helper\Cookie::IS_USER_ALLOWED_SAVE_COOKIE;
    }

    /**
     * Get store identifier
     *
     * @return int
     */
    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }
}
