<?php
namespace Zero1\Release\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Eav\Model\EntityFactory;
use \Magento\Eav\Model\Config as EavModelConfig;
use \Magento\Eav\Model\Entity\Attribute\SetFactory as EavAttributeSetFactory;
use \Swissup\AddressFieldManager\Model\ResourceModel\Address\OrderFactory;
use \Swissup\AddressFieldManager\Model\ResourceModel\Address\QuoteFactory;


class InstallData implements InstallDataInterface
{
    protected $eavSetupFactory;

    protected $eavEntityFactory;

    protected $eavModelConfig;

    protected $eavAttributeSetFactory;

    protected $orderFactory;

    protected $quoteFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        EntityFactory $eavEntityFactory,
        EavModelConfig $eavModelConfig,
        EavAttributeSetFactory $eavAttributeSetFactory,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->eavModelConfig = $eavModelConfig;
        $this->eavAttributeSetFactory = $eavAttributeSetFactory;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /*
        TODO - Review, this breaks install
        It throws this....
          SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '2-2'
   for key 'EAV_ATTRIBUTE_GROUP_ATTRIBUTE_SET_ID_ATTRIBUTE_GROUP_CODE', query
   was: INSERT INTO `eav_attribute_group` (`attribute_set_id`, `attribute_gro
  up_name`, `sort_order`, `attribute_group_code`) VALUES (?, ?, ?, ?)
In Mysql.php line 110:
  SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '2-2'
   for key 'EAV_ATTRIBUTE_GROUP_ATTRIBUTE_SET_ID_ATTRIBUTE_GROUP_CODE', query
   was: INSERT INTO `eav_attribute_group` (`attribute_set_id`, `attribute_gro
  up_name`, `sort_order`, `attribute_group_code`) VALUES (?, ?, ?, ?)
In Mysql.php line 91:
  SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '2-2'
   for key 'EAV_ATTRIBUTE_GROUP_ATTRIBUTE_SET_ID_ATTRIBUTE_GROUP_CODE'
   */
        $setup->startSetup();

        $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS;
        $entityTypeId = (int)$this->eavEntityFactory->create()->setType($entityType)->getTypeId();
        $attributeSetId = $this->eavModelConfig->getEntityType($entityType)
            ->getDefaultAttributeSetId();
        $attributeSetGroupId = $this->eavAttributeSetFactory->create()
            ->getDefaultGroupId($attributeSetId);

        $attributesToCreate = [
            'vat_number' => [
                'label' => 'VAT Number',
                'sort_order' => 20,
            ],
            'business_eori_number' => [
                'label' => 'Business EORI Number',
                'sort_order' => 20,
            ],
        ];

        $defaultAttributeConfig = [
            'entity_type_id' => $entityTypeId,
            'attribute_set_id' => $attributeSetId,
            //'group' => $attributeSetGroupId,
            'type' => 'varchar',
            'input' => 'text',
            'required' => true,
            'user_defined' => true,
            'visible' => true,
            'multiline_count' => 1,
            'system' => 0,
        ];
    }
}