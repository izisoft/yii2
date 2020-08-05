<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "promotion_to_product".
 *
 * @property int $promotion_id
 * @property int $item_id
 *
 * @property Articles $item
 * @property Promotions $promotion
 */
class PromotionToProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promotion_to_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promotion_id', 'item_id'], 'required'],
            [['promotion_id', 'item_id'], 'integer'],
            [['promotion_id', 'item_id'], 'unique', 'targetAttribute' => ['promotion_id', 'item_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Articles::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['promotion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promotions::className(), 'targetAttribute' => ['promotion_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'promotion_id' => 'Promotion ID',
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
    public function getPromotion()
    {
        return $this->hasOne(Promotions::className(), ['id' => 'promotion_id']);
    }
}
