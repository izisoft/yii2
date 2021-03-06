<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "store_website".
 *
 * @property int $website_id Website ID
 * @property string $code Code
 * @property string $name Website Name
 * @property int $sort_order Sort Order
 * @property int $default_group_id Default Group ID
 * @property int $is_default Defines Is Website Default
 * @property int $sid
 *
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
            [['sort_order', 'default_group_id', 'is_default', 'sid'], 'integer'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStores()
    {
        return $this->hasMany(Store::className(), ['website_id' => 'website_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreGroups()
    {
        return $this->hasMany(StoreGroup::className(), ['website_id' => 'website_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
