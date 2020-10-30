<?php

namespace izi\satellite;

use Yii;
use izi\models\Shops;
use izi\models\StoreWebsite;

/**
 * This is the model class for table "satellite_web".
 *
 * @property int $id
 * @property int $website_id
 * @property string $domain
 * @property int $sid
 * @property string $access_token
 *
 * @property Shops $s
 * @property StoreWebsite $website
 */
class SatelliteWebModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'satellite_web';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['website_id', 'domain', 'sid'], 'required'],
            [['website_id', 'sid'], 'integer'],
            [['domain', 'access_token'], 'string', 'max' => 255],
            [['website_id'], 'unique'],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
            [['website_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoreWebsite::className(), 'targetAttribute' => ['website_id' => 'website_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'website_id' => 'Website ID',
            'domain' => 'Domain',
            'sid' => 'Sid',
            'access_token' => 'Access Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsite()
    {
        return $this->hasOne(StoreWebsite::className(), ['website_id' => 'website_id']);
    }
}
