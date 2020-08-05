<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "promotions".
 *
 * @property int $id
 * @property int $sid
 * @property int $state
 * @property int $is_active
 * @property int $type_id
 * @property string $title
 * @property string $bizrule
 * @property string $from_date
 * @property string $to_date
 * @property string $discount
 * @property string $discount_type
 * @property int $limited
 * @property int $used
 *
 * @property PromotionToProduct[] $promotionToProducts
 * @property Articles[] $items
 */
class Promotions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promotions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'title', 'bizrule', 'from_date', 'to_date'], 'required'],
            [['sid', 'state', 'is_active', 'type_id', 'limited', 'used'], 'integer'],
            [['bizrule'], 'string'],
            [['from_date', 'to_date'], 'safe'],
            [['discount'], 'number'],
            [['title'], 'string', 'max' => 255],
            [['discount_type'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => 'Sid',
            'state' => 'State',
            'is_active' => 'Is Active',
            'type_id' => 'Type ID',
            'title' => 'Title',
            'bizrule' => 'Bizrule',
            'from_date' => 'From Date',
            'to_date' => 'To Date',
            'discount' => 'Discount',
            'discount_type' => 'Discount Type',
            'limited' => 'Limited',
            'used' => 'Used',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotionToProducts()
    {
        return $this->hasMany(PromotionToProduct::className(), ['promotion_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Articles::className(), ['id' => 'item_id'])->viaTable('promotion_to_product', ['promotion_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return PromotionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PromotionsQuery(get_called_class());
    }
}
