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
class CatalogProductEntity extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mg_catalog_product_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attribute_set_id', 'has_options', 'required_options'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['type_id'], 'string', 'max' => 32],
            [['sku'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => 'Entity ID',
            'attribute_set_id' => 'Attribute Set ID',
            'type_id' => 'Type ID',
            'sku' => 'Sku',
            'has_options' => 'Has Options',
            'required_options' => 'Required Options',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityDatetimes()
    {
        return $this->hasMany(CatalogProductEntityDatetime::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityDecimals()
    {
        return $this->hasMany(CatalogProductEntityDecimal::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityGalleries()
    {
        return $this->hasMany(CatalogProductEntityGallery::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityInts()
    {
        return $this->hasMany(CatalogProductEntityInt::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityMediaGalleryValues()
    {
        return $this->hasMany(CatalogProductEntityMediaGalleryValue::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityMediaGalleryValueToEntities()
    {
        return $this->hasMany(CatalogProductEntityMediaGalleryValueToEntity::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValues()
    {
        return $this->hasMany(CatalogProductEntityMediaGallery::className(), ['value_id' => 'value_id'])->viaTable('mg_catalog_product_entity_media_gallery_value_to_entity', ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityTexts()
    {
        return $this->hasMany(CatalogProductEntityText::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityTierPrices()
    {
        return $this->hasMany(CatalogProductEntityTierPrice::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityVarchars()
    {
        return $this->hasMany(CatalogProductEntityVarchar::className(), ['entity_id' => 'entity_id']);
    }

    /**
     * {@inheritdoc}
     * @return CatalogProductEntityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CatalogProductEntityQuery(get_called_class());
    }
}
