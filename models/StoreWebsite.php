<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "store_website".
 *
 * @property int $website_id Website ID
 * @property string|null $code Code
 * @property string|null $name Website Name
 * @property int $sort_order Sort Order
 * @property int $default_group_id Default Group ID
 * @property int|null $is_default Defines Is Website Default
 * @property int $sid
 * @property int $type_id
 *
 * @property CatalogPostWebsite[] $catalogPostWebsites
 * @property Articles[] $posts
 * @property CatalogProductWebsite[] $catalogProductWebsites
 * @property CatalogProductEntity[] $products
 * @property SatelliteWeb $satelliteWeb
 * @property SmenuWebsite[] $smenuWebsites
 * @property Smenu[] $menus
 * @property Store[] $stores
 * @property StoreGroup[] $storeGroups
 * @property Shops $s
 */
class StoreWebsite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'store_website';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_order', 'default_group_id', 'is_default', 'sid', 'type_id'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 64],
            [['code', 'sid'], 'unique', 'targetAttribute' => ['code', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'website_id' => 'Website ID',
            'code' => 'Code',
            'name' => 'Name',
            'sort_order' => 'Sort Order',
            'default_group_id' => 'Default Group ID',
            'is_default' => 'Is Default',
            'sid' => 'Sid',
            'type_id' => 'Type ID',
        ];
    }

    /**
     * Gets query for [[CatalogPostWebsites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogPostWebsites()
    {
        return $this->hasMany(CatalogPostWebsite::className(), ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Articles::className(), ['id' => 'post_id'])->viaTable('catalog_post_website', ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[CatalogProductWebsites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogProductWebsites()
    {
        return $this->hasMany(CatalogProductWebsite::className(), ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(CatalogProductEntity::className(), ['entity_id' => 'product_id'])->viaTable('catalog_product_website', ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[SatelliteWeb]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSatelliteWeb()
    {
        return $this->hasOne(SatelliteWeb::className(), ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[SmenuWebsites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSmenuWebsites()
    {
        return $this->hasMany(SmenuWebsite::className(), ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[Menus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Smenu::className(), ['id' => 'menu_id'])->viaTable('smenu_website', ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[Stores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStores()
    {
        return $this->hasMany(Store::className(), ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[StoreGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoreGroups()
    {
        return $this->hasMany(StoreGroup::className(), ['website_id' => 'website_id']);
    }

    /**
     * Gets query for [[S]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
