<?php

namespace izi\product\models;

use Yii;

/**
 * This is the model class for table "mg_catalog_product_entity".
 *
 * @property int $entity_id Entity Id
 * @property int $attribute_set_id Attribute Set ID
 * @property string $type_id Type ID
 * @property string $sku SKU
 * @property int $has_options Has Options
 * @property int $required_options Required Options
 * @property string $created_at Creation Time
 * @property string $updated_at Update Time
 *
 * @property CatalogProductEntityDatetime[] $mgCatalogProductEntityDatetimes
 * @property CatalogProductEntityDecimal[] $mgCatalogProductEntityDecimals
 * @property CatalogProductEntityGallery[] $mgCatalogProductEntityGalleries
 * @property CatalogProductEntityInt[] $mgCatalogProductEntityInts
 * @property CatalogProductEntityMediaGalleryValue[] $mgCatalogProductEntityMediaGalleryValues
 * @property CatalogProductEntityMediaGalleryValueToEntity[] $mgCatalogProductEntityMediaGalleryValueToEntities
 * @property CatalogProductEntityMediaGallery[] $values
 * @property CatalogProductEntityText[] $mgCatalogProductEntityTexts
 * @property CatalogProductEntityTierPrice[] $mgCatalogProductEntityTierPrices
 * @property CatalogProductEntityVarchar[] $mgCatalogProductEntityVarchars
 */
class Product extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return CatalogProductEntity::tableName();
    }
    
    
    public $items;
    
    public function getItem($entity_id)
    {
        if(isset($this->items[$entity_id])){
            return $this->items[$entity_id];
        }
        
        $query = CatalogProductEntity::find();
        
        $query->from(['a'   =>  CatalogProductEntity::tableName()]);
        
        $query->select([
            'a.*'
        ]);
        
        $query->where(['a.entity_id'=>$entity_id]);
        
        $this->items[$entity_id] = $query->one();
        
        return $this->items[$entity_id];
    }
    
    public function getItemAttribute($entity_id, $attribute_code)
    {
        // Get attribute_id from mg_eav_attribute
        $attribute = EavAttribute::find()
        ->from(['a' =>  EavAttribute::tableName()])
        ->where([
            'a.attribute_code'=>$attribute_code,
            'a.entity_type_id'  =>  (new \yii\db\Query())->from(EavEntityType::tableName())->where(['entity_type_code'=>'catalog_product'])->select('entity_type_id')
        ])
        
        ->one();         
        
        if(!empty($attribute)){
        // mg_catalog_product_entity_varchar
        $entity_type = $attribute->backend_type;
        
        switch ($entity_type) {
            case 'static':                
                return $this->getItem($entity_id)->{$attribute_code};
            break;
            
            default:
                ;
            break;
        }
        
        $entity_table = "izi\product\models\CatalogProductEntity" . ucfirst( $entity_type);
 
        $v = $entity_table::find()->where([
            'attribute_id' => $attribute->attribute_id,
            'entity_id'    =>  $entity_id
        ])->one();
        
        return isset($v->value) ? $v->value : null;
        
        }
    }
    
    
    public function getItemName($entity_id)
    {
        return $this->getItemAttribute($entity_id, 'name');
    }
    
    public function getItemSku($entity_id)
    {
        return $this->getItemAttribute($entity_id, 'sku');
    }
    
    
}