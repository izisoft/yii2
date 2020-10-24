<?php
namespace izi\web;

use izi\models\Store as ModelsStore;
use izi\models\StoreGroup;
use izi\models\StoreWebsite;
use Yii;
 
class Store extends \yii\base\Component
{

//     public function init()
//     {
//         //
//         $tables = [
//             'eav_entity_type',
//             'eav_attribute',
            
            
//             'store_website',
//             'store_group',
//             'store',
//             'eav_attribute_option',
//             'eav_attribute_label',
//             'eav_attribute_option_swatch',
//             'eav_attribute_option_value',
//             'catalog_eav_attribute',
//             'catalog_product_entity',
//             'catalog_product_entity_datetime',
//             'catalog_product_entity_decimal',
//             'catalog_product_entity_gallery',
//             'catalog_product_entity_int',
//             'catalog_product_entity_text',
//             'catalog_product_entity_tier_price',
//             'catalog_product_entity_varchar',
//             'catalog_product_entity_media_gallery_value_video',
//             'catalog_product_entity_media_gallery_value_to_entity',
//             'catalog_product_entity_media_gallery_value',
//             'catalog_product_website',
//             'catalog_product_relation'
//         ];
        
//         foreach($tables as $table){
//             $tableSchema = Yii::$app->db->schema->getTableSchema($table);
//             if($tableSchema !== null){
//                 $class = "\izi\product\migrations\\$table"; (new $class)->down();                (new $class)->up();
//             }
            
//             if($tableSchema === null){
//                 $class = "\izi\product\migrations\\$table";
//                 (new $class)->up();
//             }
//         }
        
// //         exit;
//     }
    

    public function getDefaultStore()
    {
        $query = (new \yii\db\Query())
        ->from(['a' => StoreWebsite::tableName()])
        ->innerJoin(['b' => StoreGroup::tableName()], 'a.website_id=b.website_id')
        ->innerJoin(['c' => ModelsStore::tableName()], 'b.group_id=c.group_id and a.website_id=c.website_id')
        ->where(['a.is_default' => 1, 'a.sid' => __SID__])
        ->select([
            'a.website_id',
            'b.group_id',
            'c.store_id',
            'website_code' => 'a.code',
            'website_name' => 'a.name',
            'group_code' => 'b.code',
            'group_name' => 'b.name',
            'store_code' => 'c.code',
            'store_name' => 'c.name',
            
        ])
        ;
        return $query->one();
    }
    
    private $_defaultWebsiteId;
    
    public function getDefaultWebsiteId()
    {
        if($this->_defaultWebsiteId === null){
            $this->_defaultWebsiteId = ($this->getDefaultStore())['website_id'];
        }
        
        return $this->_defaultWebsiteId;
    }
    
    
    public function getStoreWebsites(){
        $query = StoreWebsite::find()->where(['sid' => __SID__]);        
        return $query->orderBy(['is_default' => SORT_DESC])->all();
    }
    
    public function getStoreWebsiteByCode($code){
        $query = StoreWebsite::find()->where(['code' => $code, 'sid' => __SID__]);
        return $query->one();
    }
    
   
    
    
    public function createWebsite($code, $name, $type_id = 0){
        $website = $this->getStoreWebsiteByCode($code);
        
        if(!empty($website)){
            return false;
        }
        
        $website = new StoreWebsite();
        
        $website->sid = __SID__;
        $website->code = $code;
        $website->name = $name;
        $website->type_id = $type_id;
        
        if($website->save()){
            return $website;
        }
        
        return false;
        
    }
    
    
    public function generateWebsiteCode($param)
    {
        $prefix = isset($param['prefix']) ? $param['prefix'] : '';
        $afterfix = isset($param['afterfix']) ? $param['afterfix'] : '';
        
        $inc = isset($param['inc']) ? $param['inc'] : false;
        $inc_length = isset($param['inc_length']) ? $param['inc_length'] : 1;
        
        $code = $prefix;
        
        if($inc > 0){
            $inc = danhso($inc, $inc_length);
            $code .= $inc;
        }
        
        $code .= $afterfix;
        
        $website = $this->getStoreWebsiteByCode($code);
        
        while(!empty($website)){
            $code = $prefix;
            
           
            $inc = danhso(++$inc, $inc_length);
            $code .= $inc;
           
            
            $code .= $afterfix;
            
            $website = $this->getStoreWebsiteByCode($code);
        }
        
        return $code;
    }
    
    
}    