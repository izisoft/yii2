<?php

namespace izi\app\models;

use Yii;

/**
 * This is the model class for table "tour_services".
 *
 * @property int $id
 * @property string $name
 * @property string $lang_code
 * @property int $unit
 * @property string $price
 * @property int $currency
 * @property string $unit_f1
 * @property string $unit_f2
 * @property int $pax_type
 */
class TourServices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tour_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'lang_code', 'price'], 'required'],
            [['unit', 'currency', 'pax_type'], 'integer'],
            [['price', 'unit_f1', 'unit_f2'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['lang_code'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'lang_code' => 'Lang Code',
            'unit' => 'Unit',
            'price' => 'Price',
            'currency' => 'Currency',
            'unit_f1' => 'Unit F1',
            'unit_f2' => 'Unit F2',
            'pax_type' => 'Pax Type',
        ];
    }
}
