<?php

namespace izi\product\models;

use Yii;

/**
 * This is the model class for table "mg_catalog_product_entity_decimal".
 *
 * @property int $value_id Value ID
 * @property int $attribute_id Attribute ID
 * @property int $store_id Store ID
 * @property int $entity_id
 * @property string $value Value
 *
 * @property MgStore $store
 * @property MgEavAttribute $attribute0
 * @property MgCatalogProductEntity $entity
 */
class CatalogProductEntityDecimal extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mg_catalog_product_entity_decimal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attribute_id', 'store_id', 'entity_id'], 'integer'],
            [['value'], 'number'],
            [['entity_id', 'attribute_id', 'store_id'], 'unique', 'targetAttribute' => ['entity_id', 'attribute_id', 'store_id']],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => MgStore::className(), 'targetAttribute' => ['store_id' => 'store_id']],
            [['attribute_id'], 'exist', 'skipOnError' => true, 'targetClass' => MgEavAttribute::className(), 'targetAttribute' => ['attribute_id' => 'attribute_id']],
            [['entity_id'], 'exist', 'skipOnError' => true, 'targetClass' => MgCatalogProductEntity::className(), 'targetAttribute' => ['entity_id' => 'entity_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'value_id' => 'Value ID',
            'attribute_id' => 'Attribute ID',
            'store_id' => 'Store ID',
            'entity_id' => 'Entity ID',
            'value' => 'Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(Store::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttribute0()
    {
        return $this->hasOne(EavAttribute::className(), ['attribute_id' => 'attribute_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntity()
    {
        return $this->hasOne(CatalogProductEntity::className(), ['entity_id' => 'entity_id']);
    }
}
