<?php
namespace Custom\Banner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Custom\Banner\Model\BannerFactory;


class Index extends \Magento\Framework\App\Action\Action
{	
	protected $_modelBannerFactory;
	
	public function __construct(
		Context $context,
		BannerFactory $modelBannerFactory
	) {
		parent::__construct($context);
		$this->_modelBannerFactory = $modelBannerFactory;
        }
	
	public function execute()
        {
	   //$this->_view->loadLayout();
          // $this->_view->renderLayout();
		  $str='<ul class="rel-product">';
		   $simpleId= $_POST['simpleId'];
		  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		  $product_child = $objectManager->create('Magento\Catalog\Model\Product')->load($simpleId);
          $_imageHelper = $objectManager->get('\Magento\Catalog\Helper\Image');
		  $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
		  
		
		  $formattedPrice = $priceHelper->currency(0, true, false);
		  $noproduct='No Cap Required';
		  
		   $str.='<li class="items" id="0" onclick="getCap(0)" >';
					 $str.='<div class="img" id="img-0"><img src="https://www.ibottles.co.uk/media/wysiwyg/no-cap.png" alt="No Cap"/></div>';
					  $str.= '<div id="nm-0" class="nm">'.$noproduct.'</div>';
					 $str.='<div id="price-0" class="price" style="display:none">'.$formattedPrice.'</div>';
		   $str.="</li>";
		   
		     $relatedProducts = $product_child->getRelatedProducts();
		  if (!empty($relatedProducts)) {
       
          foreach ($relatedProducts as $relatedProduct) {
			
			$_product = $objectManager->create('Magento\Catalog\Model\Product')->load($relatedProduct->getId());
			//echo $_product->getStatus();
			
			$formattedPrice = $priceHelper->currency($_product->getPrice(), true, false);
			
		    $str.='<li class="items"  onclick="getCap('.$relatedProduct->getId().')"  id="'.$relatedProduct->getId().'">';
			
            $bigImageUrl=$_imageHelper->init($_product, 'image', ['type'=>'image'])->keepAspectRatio(true)->getUrl();
            
            $str.='<input type="hidden" id="imgbig'.$relatedProduct->getId().'" value="'.$bigImageUrl.'">';
           
		    $imageUrl = $_imageHelper->init($_product, 'small_image', ['type'=>'small_image'])->keepAspectRatio(true)->resize('135','135')->getUrl();
            $str.='<div class="img" id="img-'.$relatedProduct->getId().'"><img src='. $imageUrl.'></div>';
			
			 $str.= '<div class="nm" id="nm-'.$relatedProduct->getId().'">'.$_product->getName()."</div>"; //get name
			
			 $str.='<div class="price" style="display:none" id="price-'.$relatedProduct->getId().'">'.$formattedPrice.'</div>';
			
		
	      $str.="</li></a>";
					} 
		 
				}
				
				$str.='<ul>';
			die($str);		
			//return  $str;		
				
		}
        
        
        
}