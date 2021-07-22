<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "slugs".
 *
 * @property int $id
 * @property int $sid
 * @property string $url
 * @property string $lang
 * @property int $type_id
 * @property string $router
 * @property string $item_id
 * @property string $item_type
 * @property string $json_data
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Shops $s
 */
class Slug extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'slugs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'type_id', 'item_id', 'created_at', 'updated_at'], 'integer'],
            [['json_data'], 'string'],
            [['url'], 'string', 'max' => 255],
            [['lang'], 'string', 'max' => 6],
            [['router'], 'string', 'max' => 128],
            [['item_type'], 'string', 'max' => 32],
            [['url', 'sid'], 'unique', 'targetAttribute' => ['url', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shop::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => 'Sid',
            'url' => 'Url',
            'lang' => 'Lang',
            'type_id' => 'Type ID',
            'router' => 'Router',
            'item_id' => 'Item ID',
            'item_type' => 'Item Type',
            'json_data' => 'Json Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }
}
