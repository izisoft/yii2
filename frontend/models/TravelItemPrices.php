<?php

namespace izi\frontend\models;

use Yii;

/**
 * This is the model class for table "travel_item_prices".
 *
 * @property int $item_id
 * @property int $quotation_id
 * @property int $package_id
 * @property int $nationality_id
 * @property int $group_id
 * @property int $age_group_id
 * @property string $price
 * @property int $price_type_id 0: nomal 1: single supplement
 * @property int $type_id 1: ghep 2: private
 * @property int $departure_type_id
 * @property string $departure_date
 * @property int $day_id
 */
class TravelItemPrices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'travel_item_prices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'quotation_id', 'package_id', 'nationality_id', 'group_id', 'age_group_id', 'price_type_id', 'type_id', 'departure_type_id', 'day_id'], 'required'],
           // [['item_id', 'quotation_id', 'package_id', 'nationality_id', 'group_id', 'age_group_id', 'price_type_id', 'type_id', 'departure_type_id', 'day_id'], 'integer'],
           // [['price'], 'number'],
            
           // [['item_id', 'quotation_id', 'package_id', 'nationality_id', 'group_id', 'age_group_id', 'price_type_id', 'type_id', 'departure_type_id' , 'day_id'], 'unique', 'targetAttribute' => ['item_id', 'quotation_id', 'package_id', 'nationality_id', 'group_id', 'age_group_id', 'price_type_id', 'type_id', 'departure_type_id',  'day_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'item_id' => 'Item ID',
            'quotation_id' => 'Quotation ID',
            'package_id' => 'Package ID',
            'nationality_id' => 'Nationality ID',
            'group_id' => 'Group ID',
            'age_group_id' => 'Age Group ID',
            'price' => 'Price',
            'price_type_id' => 'Price Type ID',
            'type_id' => 'Type ID',
            'departure_type_id' => 'Departure Type ID',
            'departure_date' => 'Departure Date',
            'day_id' => 'Day ID',
        ];
    }
}
