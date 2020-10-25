<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "temp_to_shop".
 *
 * @property int $temp_id
 * @property int $sid
 * @property int $state
 * @property string $lang
 *
 * @property Shops $s
 * @property Templates $temp
 */
class TempToShop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_to_shop';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['temp_id', 'sid'], 'required'],
            [['temp_id', 'sid', 'state'], 'integer'],
            [['lang'], 'string', 'max' => 16],
            [['temp_id', 'sid'], 'unique', 'targetAttribute' => ['temp_id', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
            [['temp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Templates::className(), 'targetAttribute' => ['temp_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'temp_id' => 'Temp ID',
            'sid' => 'Sid',
            'state' => 'State',
            'lang' => 'Lang',
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
    public function getTemp()
    {
        return $this->hasOne(Templates::className(), ['id' => 'temp_id']);
    }
}
