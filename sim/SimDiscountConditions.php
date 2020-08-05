<?php

namespace izi\sim;

use Yii;

/**
 * This is the model class for table "discount_conditions".
 *
 * @property string $min_price
 * @property string $max_price
 * @property string $profit_value
 * @property int $min_value
 * @property int $max_value
 * @property string $condition1
 * @property string $condition2
 * @property string $condition3
 * @property int $sid
 * @property string $group_name
 * @property int $partner_id
 */
class SimDiscountConditions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'discount_conditions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['min_price', 'max_price', 'condition1', 'condition2', 'condition3', 'sid', 'group_name'], 'required'],
            [['min_price', 'max_price', 'min_value', 'max_value', 'sid', 'partner_id'], 'integer'],
            [['profit_value'], 'number'],
            [['condition1', 'condition2', 'condition3', 'group_name'], 'string', 'max' => 255],
            [['min_price', 'max_price', 'condition1', 'condition2', 'condition3', 'sid'], 'unique', 'targetAttribute' => ['min_price', 'max_price', 'condition1', 'condition2', 'condition3', 'sid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'min_price' => 'Min Price',
            'max_price' => 'Max Price',
            'profit_value' => 'Profit Value',
            'min_value' => 'Min Value',
            'max_value' => 'Max Value',
            'condition1' => 'Condition1',
            'condition2' => 'Condition2',
            'condition3' => 'Condition3',
            'sid' => 'Sid',
            'group_name' => 'Group Name',
            'partner_id' => 'Partner ID',
        ];
    }
}
