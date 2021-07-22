<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "site_configs".
 *
 * @property string $code
 * @property string $lang
 * @property int $sid
 * @property string $json_data
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Shops $s
 */
class SiteConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_configs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'lang', 'sid'], 'required'],
            [['sid', 'created_at', 'updated_at'], 'integer'],
            [['json_data'], 'string'],
            [['code'], 'string', 'max' => 64],
            [['lang'], 'string', 'max' => 8],
            [['code', 'lang', 'sid'], 'unique', 'targetAttribute' => ['code', 'lang', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shop::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',
            'lang' => 'Lang',
            'sid' => 'Sid',
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
