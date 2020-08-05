<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "customers_to_groups".
 *
 * @property int $customer_id
 * @property int $group_id
 */
class CustomersToGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customers_to_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'group_id'], 'required'],
            [['customer_id', 'group_id'], 'integer'],
            [['customer_id', 'group_id'], 'unique', 'targetAttribute' => ['customer_id', 'group_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => 'Customer ID',
            'group_id' => 'Group ID',
        ];
    }
}
