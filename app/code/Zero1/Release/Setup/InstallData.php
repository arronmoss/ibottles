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
            'business_eori_number' => [
                'label' => 'Business EORI Number',
                'sort_order' => 20,
            ],
            'vat_number' => [
                'label' => 'VAT Number',
                'sort_order' => 20,
            ],
        ];

        $defaultAttributeConfig = [
            'entity_type_id' => $entityTypeId,
            'attribute_set_id' => $attributeSetId,
            //'group' => $attributeSetGroupId,
            'type' => 'varchar',
            'input' => 'text',
            'required' => false,
            'user_defined' => true,
            'visible' => true,
            'multiline_count' => 1,
            'system' => 0,
        ];

        $forms = [
            'customer_register_address',
            'customer_address_edit',
            'adminhtml_customer_address',
        ];

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $formTypeTable = $setup->getTable('eav_form_type');
        $select = $setup->getConnection()->select();

        // you can't add to these forms like the others
        // for some stupid reason
        $select->from($formTypeTable, ['type_id', 'code'])
            ->where('code in (?)', [
                'checkout_onepage_register',
                'checkout_onepage_register_guest',
                'checkout_onepage_billing_address',
                'checkout_onepage_shipping_address',
                'checkout_multishipping_register',
            ]);
        $checkoutForms = $select->getConnection()->fetchPairs($select);

        $quote = $this->quoteFactory->create();
        $order = $this->orderFactory->create();

        foreach($attributesToCreate as $attributeCode => $attributeConfig){

            $config = $defaultAttributeConfig;
            foreach($attributeConfig as $k => $v){
                $config[$k] = $v;
            }

            if(isset($attributeConfig['sort_order'])){
                $config['position'] = $attributeConfig['sort_order'];
            }

            $eavSetup->addAttribute(
                $entityType,
                $attributeCode,
                $config
            );

            // have to do this with an additional save for some reason
            $attribute = $this->eavModelConfig->getAttribute($entityType, $attributeCode);
            $attribute->setData('used_in_forms', $forms);
            $attribute->save();
            // make them come through from checkout
            $order->saveNewAttribute($attribute);
            $quote->saveNewAttribute($attribute);

            // foreach($checkoutForms as $checkoutFormId => $checkoutFormCode){
            //     $setup->getConnection()->insert(
            //         $setup->getTable('eav_form_element'),
            //         [
            //             'type_id' => $checkoutFormId,
            //             'fieldset_id' => null,
            //             'attribute_id' => $attribute->getId(),
            //             'sort_order' => $attributeConfig['sort_order'],
            //         ]
            //     );
            // }
            
        }

        // move the country field
        $attribute = $this->eavModelConfig->getAttribute($entityType, 'country_id');
        $attribute->setData('sort_order', 1);
        $attribute->setData('position', 1);
        $attribute->setIsRequired(false);
        $attribute->setData('used_in_forms', $forms);
        $attribute->save();
        $setup->endSetup();
    }
}
