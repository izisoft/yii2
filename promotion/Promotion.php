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
class Promotion extends \yii\base\Component
{
    
    
    private $_coupon;
    
    public function getCoupon(){
        if($this->_coupon === null){
            $this->_coupon = Yii::createObject([
                'class'=>'izi\promotion\Coupons',
                
            ]);
        }
        return $this->_coupon;
    }
    
}
