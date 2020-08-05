<?php

namespace izi\import\sim;

use Yii;

/**
 * This is the model class for table "simonline".
 *
 * @property string $id
 * @property string $display
 * @property string $network_label
 * @property string $category_label
 * @property string $price1
 * @property string $price2
 * @property int $status
 */
class SimModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'simonline';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'display', 'network_label', 'category_label', 'price1', 'price2', 'updated_at'], 'required'],
            [['id', 'status', 'updated_at'], 'integer'],
            [['price1', 'price2', 'score'], 'number'],
            [['display'], 'string', 'max' => 20],
            [['network_label', 'category_label', 'partner_label'], 'string', 'max' => 64],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'display' => 'Display',
            'network_label' => 'Network Label',
            'category_label' => 'Category Label',
            'partner_label' => 'Partner Label',
            'price1' => 'Price1',
            'price2' => 'Price2',
            'status' => 'Status',
            'updated_at' => 'Updated At',
            'score' => 'Score',
        ];
    }
}
