<?php

namespace izi\frontend\sim;

use Yii;

/**
 * This is the model class for table "simonline_module".
 *
 * @property string $id
 * @property string $display
 * @property string $network_label
 * @property string $category_label
 * @property string $partner_label
 * @property string $price1
 * @property string $price2
 * @property int $status
 * @property int $updated_at
 * @property string $score
 * @property string $module_name
 *
 * @property Simonline $id0
 */
class SimonlineModuleModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'simonline_module';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'display', 'network_label', 'category_label', 'price1', 'price2', 'updated_at', 'module_name'], 'required'],
            [['id', 'status', 'updated_at'], 'integer'],
            [['price1', 'price2', 'score'], 'number'],
            [['display'], 'string', 'max' => 20],
            [['network_label', 'category_label', 'partner_label'], 'string', 'max' => 64],
            [['module_name'], 'string', 'max' => 32],
            [['id'], 'unique'],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Simonline::className(), 'targetAttribute' => ['id' => 'id']],
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
            'module_name' => 'Module Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Simonline::className(), ['id' => 'id']);
    }
}
