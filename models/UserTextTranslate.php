<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "user_text_translate".
 *
 * @property string $lang_code
 * @property string $lang
 * @property int $sid
 * @property string $value
 * @property int $auto_load
 * @property int $status
 *
 * @property Shops $s
 */
class UserTextTranslate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_text_translate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lang_code', 'lang', 'sid'], 'required'],
            [['sid', 'auto_load', 'status'], 'integer'],
            [['lang_code'], 'string', 'max' => 64],
            [['lang'], 'string', 'max' => 6],
            [['value'], 'string', 'max' => 255],
            [['lang_code', 'lang', 'sid'], 'unique', 'targetAttribute' => ['lang_code', 'lang', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'lang_code' => 'Lang Code',
            'lang' => 'Lang',
            'sid' => 'Sid',
            'value' => 'Value',
            'auto_load' => 'Auto Load',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
