<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "templates".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $is_hidden
 * @property int $parent_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property TempToShop[] $tempToShops
 * @property Shops[] $s
 */
class Template extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_hidden', 'parent_id', 'created_at', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'is_hidden' => 'Is Hidden',
            'parent_id' => 'Parent ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTempToShops()
    {
        return $this->hasMany(TempToShop::className(), ['temp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasMany(Shop::className(), ['id' => 'sid'])->viaTable('temp_to_shop', ['temp_id' => 'id']);
    }
}
