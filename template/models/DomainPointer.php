<?php

namespace izi\template\models;

use Yii;

/**
 * This is the model class for table "domain_pointer".
 *
 * @property int $id
 * @property int $sid
 * @property string $domain
 * @property string $module
 * @property string $layout
 * @property int $state
 * @property string $time
 * @property int $is_default
 * @property int $is_admin
 * @property int $is_invisible
 * @property string $bizrule
 * @property int $temp_id
 *
 * @property Shops $s
 */
class DomainPointer extends \izi\db\ActiveRecord
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
            [['sid', 'state', 'is_default', 'is_admin', 'is_invisible', 'temp_id'], 'integer'],            
            [['time'], 'safe'],
            [['bizrule'], 'string'],
            [['domain', 'module'], 'string', 'max' => 64],
            [['layout'], 'string', 'max' => 16],
            [['domain'], 'unique'],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
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
            'bizrule' => 'Bizrule',
            'temp_id' => 'Temp ID',
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
     * {@inheritdoc}
     * @return DomainPointerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DomainPointerQuery(get_called_class());
    }
}
