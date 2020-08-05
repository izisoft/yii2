<?php

namespace izi\backend\models;

use Yii;

/**
 * This is the model class for table "mg_eav_attribute".
 *
 * @property int $attribute_id Attribute Id
 * @property int $entity_type_id Entity Type Id
 * @property string $attribute_code Attribute Code
 * @property string $attribute_model Attribute Model
 * @property string $backend_model Backend Model
 * @property string $backend_type Backend Type
 * @property string $backend_table Backend Table
 * @property string $frontend_model Frontend Model
 * @property string $frontend_input Frontend Input
 * @property string $frontend_label Frontend Label
 * @property string $frontend_class Frontend Class
 * @property string $source_model Source Model
 * @property int $is_required Defines Is Required
 * @property int $is_user_defined Defines Is User Defined
 * @property string $default_value Default Value
 * @property int $is_unique Defines Is Unique
 * @property string $note Note
 *
 * @property MgCatalogProductEntityDatetime[] $mgCatalogProductEntityDatetimes
 * @property MgCatalogProductEntityDecimal[] $mgCatalogProductEntityDecimals
 * @property MgCatalogProductEntityGallery[] $mgCatalogProductEntityGalleries
 * @property MgCatalogProductEntityInt[] $mgCatalogProductEntityInts
 * @property MgCatalogProductEntityMediaGallery[] $mgCatalogProductEntityMediaGalleries
 * @property MgCatalogProductEntityText[] $mgCatalogProductEntityTexts
 * @property MgCatalogProductEntityVarchar[] $mgCatalogProductEntityVarchars
 * @property MgEavEntityType $entityType
 */
class MgEavAttribute extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mg_eav_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['entity_type_id', 'is_required', 'is_user_defined', 'is_unique'], 'integer'],
            [['attribute_code'], 'required'],
            [['default_value'], 'string'],
            [['attribute_code', 'attribute_model', 'backend_model', 'backend_table', 'frontend_model', 'frontend_label', 'frontend_class', 'source_model', 'note'], 'string', 'max' => 255],
            [['backend_type'], 'string', 'max' => 8],
            [['frontend_input'], 'string', 'max' => 50],
            [['entity_type_id', 'attribute_code'], 'unique', 'targetAttribute' => ['entity_type_id', 'attribute_code']],
            [['entity_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => MgEavEntityType::className(), 'targetAttribute' => ['entity_type_id' => 'entity_type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'attribute_id' => 'Attribute ID',
            'entity_type_id' => 'Entity Type ID',
            'attribute_code' => 'Attribute Code',
            'attribute_model' => 'Attribute Model',
            'backend_model' => 'Backend Model',
            'backend_type' => 'Backend Type',
            'backend_table' => 'Backend Table',
            'frontend_model' => 'Frontend Model',
            'frontend_input' => 'Frontend Input',
            'frontend_label' => 'Frontend Label',
            'frontend_class' => 'Frontend Class',
            'source_model' => 'Source Model',
            'is_required' => 'Is Required',
            'is_user_defined' => 'Is User Defined',
            'default_value' => 'Default Value',
            'is_unique' => 'Is Unique',
            'note' => 'Note',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityDatetimes()
    {
        return $this->hasMany(MgCatalogProductEntityDatetime::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityDecimals()
    {
        return $this->hasMany(MgCatalogProductEntityDecimal::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityGalleries()
    {
        return $this->hasMany(MgCatalogProductEntityGallery::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityInts()
    {
        return $this->hasMany(MgCatalogProductEntityInt::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityMediaGalleries()
    {
        return $this->hasMany(MgCatalogProductEntityMediaGallery::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityTexts()
    {
        return $this->hasMany(MgCatalogProductEntityText::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityVarchars()
    {
        return $this->hasMany(MgCatalogProductEntityVarchar::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntityType()
    {
        return $this->hasOne(MgEavEntityType::className(), ['entity_type_id' => 'entity_type_id']);
    }
    
    
    
    public function getAll($params = [])
    {
        $entity_type_id = isset($params['entity_type_id']) ? $params['entity_type_id'] : 4;
        
        $query = static::find()->where(['entity_type_id'=>$entity_type_id]);
        
        $not_in = isset($params['not_in']) ? $params['not_in'] : [];
        
        if(!empty($not_in)){
            $query->andWhere(['not in', 'attribute_id', $not_in]);
        }
        
        
        $in = isset($params['in']) ? $params['in'] : [];
        
        if(!empty($in)){
            $query->andWhere(['in', 'attribute_id', $in]);
        }
        
        return $query->orderBy(['frontend_label'=>SORT_ASC])->asArray()->all();
    }
    
    public function getItem($attribute_id)
    {
        
        $query = static::find()->where(['attribute_id'=>$attribute_id]);
        
         
        
        return $query->asArray()->one();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
