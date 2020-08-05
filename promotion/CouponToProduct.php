<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "coupon_to_product".
 *
 * @property int $coupon_id
 * @property int $item_id
 *
 * @property Articles $item
 * @property Coupons $coupon
 */
class CouponToProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupon_to_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id', 'item_id'], 'required'],
            [['coupon_id', 'item_id'], 'integer'],
            [['coupon_id', 'item_id'], 'unique', 'targetAttribute' => ['coupon_id', 'item_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Articles::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['coupon_id'], 'exist', 'skipOnError' => true, 'targetClass' => Coupons::className(), 'targetAttribute' => ['coupon_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'coupon_id' => 'Coupon ID',
            'item_id' => 'Item ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Articles::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoupon()
    {
        return $this->hasOne(Coupons::className(), ['id' => 'coupon_id']);
    }
}
