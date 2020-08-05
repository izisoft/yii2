<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\product;
use Yii;
 
class Product extends \yii\base\Component
{
    
    private $_model;
    
    public function getModel()
    {
        if($this->_model == null){
            $this->_model = Yii::createObject('izi\product\models\Product');
        }
        
        return $this->_model;
    }
    
    
    
    public function getItem($entity_id)
    {
        return $this->getModel()->getItem($entity_id);
    }
    
    public function getItemName($entity_id)
    {
        return $this->getModel()->getItemName($entity_id);
        
    }
    
    public function getItemSku($entity_id)
    {
        return $this->getItem($entity_id)->sku;
        
    }
}