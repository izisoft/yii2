<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "promotion_condition_label".
 *
 * @property int $id
 * @property string $label
 * @property string $code
 *
 * @property PromotionCondition[] $promotionConditions
 */
class PromotionConditionLabel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promotion_condition_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['label', 'code'], 'required'],
            [['label'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 128],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'code' => 'Code',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotionConditions()
    {
        return $this->hasMany(PromotionCondition::className(), ['condition_code' => 'code']);
    }

    /**
     * {@inheritdoc}
     * @return PromotionConditionLabelQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PromotionConditionLabelQuery(get_called_class());
    }
}
