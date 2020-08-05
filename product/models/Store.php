<?php

namespace izi\product\models;

use Yii;

/**
 * This is the model class for table "mg_store".
 *
 * @property int $store_id Store Id
 * @property string $code Code
 * @property int $website_id Website Id
 * @property int $group_id Group Id
 * @property string $name Store Name
 * @property int $sort_order Store Sort Order
 * @property int $is_active Store Activity
 *
 * @property CatalogProductEntityDatetime[] $mgCatalogProductEntityDatetimes
 * @property CatalogProductEntityDecimal[] $mgCatalogProductEntityDecimals
 * @property CatalogProductEntityGallery[] $mgCatalogProductEntityGalleries
 * @property CatalogProductEntityInt[] $mgCatalogProductEntityInts
 * @property CatalogProductEntityMediaGalleryValue[] $mgCatalogProductEntityMediaGalleryValues
 * @property CatalogProductEntityMediaGalleryValueVideo[] $mgCatalogProductEntityMediaGalleryValueVideos
 * @property CatalogProductEntityMediaGallery[] $values
 * @property CatalogProductEntityText[] $mgCatalogProductEntityTexts
 * @property CatalogProductEntityVarchar[] $mgCatalogProductEntityVarchars
 * @property StoreGroup $group
 * @property StoreWebsite $website
 */
class Store extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mg_store';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['website_id', 'group_id', 'sort_order', 'is_active'], 'integer'],
            [['name'], 'required'],
            [['code'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoreGroup::className(), 'targetAttribute' => ['group_id' => 'group_id']],
            [['website_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoreWebsite::className(), 'targetAttribute' => ['website_id' => 'website_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'store_id' => 'Store ID',
            'code' => 'Code',
            'website_id' => 'Website ID',
            'group_id' => 'Group ID',
            'name' => 'Name',
            'sort_order' => 'Sort Order',
            'is_active' => 'Is Active',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityDatetimes()
    {
        return $this->hasMany(CatalogProductEntityDatetime::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityDecimals()
    {
        return $this->hasMany(CatalogProductEntityDecimal::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityGalleries() 
    {
        return $this->hasMany(CatalogProductEntityGallery::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityInts()
    {
        return $this->hasMany(CatalogProductEntityInt::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery 
     */
    public function getMgCatalogProductEntityMediaGalleryValues()
    {
        return $this->hasMany(CatalogProductEntityMediaGalleryValue::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityMediaGalleryValueVideos()
    {
        return $this->hasMany(CatalogProductEntityMediaGalleryValueVideo::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValues()
    {
        return $this->hasMany(CatalogProductEntityMediaGallery::className(), ['value_id' => 'value_id'])->viaTable('mg_catalog_product_entity_media_gallery_value_video', ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityTexts()
    {
        return $this->hasMany(CatalogProductEntityText::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMgCatalogProductEntityVarchars()
    {
        return $this->hasMany(CatalogProductEntityVarchar::className(), ['store_id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(StoreGroup::className(), ['group_id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsite()
    {
        return $this->hasOne(StoreWebsite::className(), ['website_id' => 'website_id']);
    }
}
