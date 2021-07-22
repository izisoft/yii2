<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "user_to_shop".
 *
 * @property int $user_id
 * @property int $sid
 * @property int $state 1: default 2: employee
 *
 * @property Users $user
 * @property Shops $s
 */
class UserToShop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_to_shop';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'sid'], 'required'],
            [['user_id', 'sid', 'state'], 'integer'],
            [['user_id', 'sid'], 'unique', 'targetAttribute' => ['user_id', 'sid']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shop::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'sid' => 'Sid',
            'state' => 'State',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }
}
