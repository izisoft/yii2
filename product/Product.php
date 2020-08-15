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
    
    public function init()
    {
        // 
        $tables = [            
            'eav_entity_type',
            'eav_attribute',
            'store_website',
            'store_group',
            'store',
            'catalog_product_entity',
            'catalog_product_entity_datetime',
            'catalog_product_entity_decimal',
            'catalog_product_entity_gallery',
            'catalog_product_entity_int',
            'catalog_product_entity_text',
            'catalog_product_entity_tier_price',
            'catalog_product_entity_varchar',
            'catalog_product_entity_media_gallery_value_video',
            'catalog_product_entity_media_gallery_value_to_entity',
            'catalog_product_entity_media_gallery_value'
            
        ];

        foreach($tables as $table){
            $tableSchema = Yii::$app->db->schema->getTableSchema($table);
            if($tableSchema !== null){
                // $class = "\izi\product\migrations\\$table"; (new $class)->down();
            }

            if($tableSchema === null){
                $class = "\izi\product\migrations\\$table";
                (new $class)->up();
            }
        }        
    }

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