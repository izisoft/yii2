<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "promotion_condition".
 *
 * @property int $condition_id
 * @property int $promotion_id
 * @property int $coupon_id
 * @property string $min_value
 * @property string $max_value
 *
 * @property PromotionConditionLabel $condition
 * @property Coupons $coupon
 * @property Promotions $promotion
 */
class PromotionCondition extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promotion_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['condition_id'], 'required'],
            [['condition_id', 'promotion_id', 'coupon_id'], 'integer'],
            [['min_value', 'max_value'], 'number'],
            [['condition_id', 'promotion_id', 'coupon_id'], 'unique', 'targetAttribute' => ['condition_id', 'promotion_id', 'coupon_id']],
            [['condition_id'], 'exist', 'skipOnError' => true, 'targetClass' => PromotionConditionLabel::className(), 'targetAttribute' => ['condition_id' => 'id']],
            [['coupon_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coupons::className(), 'targetAttribute' => ['coupon_id' => 'id']],
            [['promotion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promotions::className(), 'targetAttribute' => ['promotion_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'condition_id' => 'Condition ID',
            'promotion_id' => 'Promotion ID',
            'coupon_id' => 'Coupon ID',
            'min_value' => 'Min Value',
            'max_value' => 'Max Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCondition()
    {
        return $this->hasOne(PromotionConditionLabel::className(), ['id' => 'condition_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(Coupons::className(), ['id' => 'coupon_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotion()
    {
        return $this->hasOne(Promotions::className(), ['id' => 'promotion_id']);
    }
}
