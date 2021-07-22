<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "domain_pointer".
 *
 * @property int $id
 * @property int|null $sid
 * @property string $domain
 * @property string $module
 * @property string|null $layout
 * @property int|null $state
 * @property string|null $time
 * @property int|null $is_default
 * @property int $is_admin
 * @property int $is_invisible
 * @property int $temp_id
 * @property string $lang
 * @property string $bizrule
 * @property int $is_hidden
 * @property int $status
 *
 * @property Shops $s
 */
class DomainPointer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domain_pointer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'state', 'is_default', 'is_admin', 'is_invisible', 'temp_id', 'is_hidden', 'status'], 'integer'],
            [['domain'], 'required'],
            [['time'], 'safe'],
            [['bizrule'], 'string'],
            [['domain'], 'string', 'max' => 128],
            [['module'], 'string', 'max' => 64],
            [['layout', 'lang'], 'string', 'max' => 16],
            [['domain'], 'unique'],
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
            'domain' => 'Domain',
            'module' => 'Module',
            'layout' => 'Layout',
            'state' => 'State',
            'time' => 'Time',
            'is_default' => 'Is Default',
            'is_admin' => 'Is Admin',
            'is_invisible' => 'Is Invisible',
            'temp_id' => 'Temp ID',
            'lang' => 'Lang',
            'bizrule' => 'Bizrule',
            'is_hidden' => 'Is Hidden',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[S]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }
}
