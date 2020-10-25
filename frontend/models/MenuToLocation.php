<?php

namespace izi\frontend\models;

use Yii;

/**
 * This is the model class for table "menu_to_location".
 *
 * @property int $menu_id
 * @property string $location_id
 * @property int $temp_id
 *
 * @property Menu $menu
 */
class MenuToLocation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu_to_location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['menu_id', 'location_id', 'temp_id'], 'required'],
            [['menu_id', 'temp_id'], 'integer'],
            [['location_id'], 'string', 'max' => 64],
            [['menu_id', 'location_id', 'temp_id'], 'unique', 'targetAttribute' => ['menu_id', 'location_id', 'temp_id']],
            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::className(), 'targetAttribute' => ['menu_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'menu_id' => 'Menu ID',
            'location_id' => 'Location ID',
            'temp_id' => 'Temp ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::className(), ['id' => 'menu_id']);
    }
}
