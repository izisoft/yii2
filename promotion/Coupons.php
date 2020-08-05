<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "coupons".
 *
 * @property int $id
 * @property string $code Mã KM
 * @property int $sid
 * @property string $name Tên KM
 * @property int $discount_type Loại giảm giá
 * @property string $discount_value Giá trị giảm
 * @property string $max_discount_value
 * @property string $from_date
 * @property string $to_date
 * @property int $type_id Loại mã
 * @property int $quantity
 * @property int $used
 * @property int $limit_per_user
 * @property int $visibility 1 = Public
 * @property int $status
 * @property int $state
 * @property int $store_status
 * @property int $product_status
 *
 * @property CouponToProduct[] $couponToProducts
 * @property Articles[] $items
 * @property Shops $s
 * @property PromotionCondition[] $promotionConditions
 */
class Coupons extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'coupons';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'sid', 'name'], 'required'],
            [['sid', 'discount_type', 'type_id', 'quantity', 'used', 'limit_per_user', 'visibility', 'status', 'state', 'store_status', 'product_status'], 'integer'],
            [['discount_value', 'max_discount_value'], 'number'],
            [['from_date', 'to_date'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['code', 'sid'], 'unique', 'targetAttribute' => ['code', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'sid' => 'Sid',
            'name' => 'Name',
            'discount_type' => 'Discount Type',
            'discount_value' => 'Discount Value',
            'max_discount_value' => 'Max Discount Value',
            'from_date' => 'From Date',
            'to_date' => 'To Date',
            'type_id' => 'Type ID',
            'quantity' => 'Quantity',
            'used' => 'Used',
            'limit_per_user' => 'Limit Per User',
            'visibility' => 'Visibility',
            'status' => 'Status',
            'state' => 'State',
            'store_status' => 'Store Status',
            'product_status' => 'Product Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponToProducts()
    {
        return $this->hasMany(CouponToProduct::className(), ['coupon_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Articles::className(), ['id' => 'item_id'])->viaTable('coupon_to_product', ['coupon_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotionConditions()
    {
        return $this->hasMany(PromotionCondition::className(), ['coupon_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return CouponsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CouponsQuery(get_called_class());
    }
    
    
    public function getList($params = [])
    {
        $p = 1;
        
        $limit = 30;
        
        $offset = ($p - 1) * $limit;
        
        $query = static::find()->where(['sid'=>__SID__]);
        
        $count = $query->count(1);
        
        $total_pages = ceil($count/$limit);
        
        $l = $query->limit($limit)
        ->offset($offset)
        ->orderBy(['status'=>SORT_DESC, 'to_date'=>SORT_DESC])
        ->all();
        
        return [
            'list_items'    =>  $l,
            'p' =>  $p,
            'limit'    =>  $limit,
            'total_pages' =>  $total_pages,
            'total_records' =>  $count
            
        ];
    }
    
    /**
     * Khởi tạo điều kiện km
     */
    
    public function initLabel()
    {
        $arrays = [
            ['label'    =>  'Tổng số sản phẩm / đơn hàng', 'code' => 'total_product_per_invoice'],
            ['label'    =>  'Tổng số tiền / đơn hàng'    , 'code' => 'total_price_per_invoice'],
            ['label'    =>  'Giá trị sản phẩm',            'code' => 'product_price'],
            //             ['label'    =>  'Tổng số sản phẩm / đơn hàng', 'code' => 'total_product_per_invoice'],
            
        ];
        
        foreach($arrays as $r){
            $item = PromotionConditionLabel::findOne(['code'=>$r['code']]);
            
            if(empty($item)){
                $item = new PromotionConditionLabel();
                $item->label = $r['label'];
                $item->code = $r['code'];
                
                $item->save();
            }
            
            //             view($item);
        }
    }
    
    /**
     * Tạo mã
     */
    
    public function validateCode($code, $id = 0)
    {
        $item = Coupons::findOne(['code'=>$code, 'sid'=>__SID__]);
        if(!empty($item) && $item->id != $id){
            return false;
        }
        return true;
    }
    
    
    public function resetCouponState($coupon)
    {
        $time1 = strtotime($coupon->from_date);
        $time2 = strtotime($coupon->to_date . ' 23:59:59');
        
        if($time1 < __TIME__ && $time2 > __TIME__){
            $status = 1;
        }elseif($time2 < __TIME__){
            $status = 0;
        }else{
            $status = 2;
        }
        
        $item = Coupons::findOne(['id'=>$coupon->id, 'sid'=>__SID__]);
       
        
        if(!empty($item) && $coupon->state > 0){
            
            $item->state = $status;
            $item->status = $status;
            
            $item->save();
        }
    }
    
     
    
    public function getCouponConditions($coupon_id)
    {
        return PromotionCondition::find()->from(['a'=>PromotionCondition::tableName()])
        ->innerJoin(['b'=>PromotionConditionLabel::tableName()],'a.condition_id=b.id')
        ->where(['a.coupon_id'=>$coupon_id])
        ->select([ 'b.label', 'b.code', 'a.*'])
        ->asArray()
        ->all();
    }
    
    public function getCoupon($coupon, $assoc = false)
    {
        $c = Coupons::find()->where(['code'=>$coupon, 'sid'=>__SID__]);
         
        if($assoc) {
            $c->asArray();
        }
        
        
        return $c->one();
    }
    
    public function setCoupon($data)
    {
        $id = 0;
        if(isset($data['id']) && $data['id'] > 0){
            $item = Coupons::findOne(['id'=>$data['id'], 'sid'=>__SID__]);
            if(empty($item)){
                //                $item = new Coupons();
            }
            $id = $data['id'];
        }else{
            $item = new Coupons();
        }
        // validate code
        $data['code'] = unMark($data['code'], '', false);
        
        if(!$this->validateCode($data['code'], $id)){
            return false;
        }
        
        // Set điều kiện
        $conditions = [];
        if(isset($data['conditions'])){
            
            $conditions = $data['conditions'];            
            unset($data['conditions']);
             
            
        }
        
        
        foreach ($data as $k=>$v){
            $item->{$k} = $v;
        }
        
//         view($item);
        
        if($item->save()){
            if($id == 0){
                $id = Yii::$app->db->getLastInsertID();
            }
            
            $this->resetCouponState($item);
            
            // Set điều kiện
            if(!empty($conditions)){
                
                foreach ($conditions as $k => $v){
                    
                    $con = PromotionCondition::findOne([
                        'condition_id'  =>  $k,
                        'coupon_id'     =>  $id,
                        
                    ]);
                    
                    if(empty($con)){
                        $con = new PromotionCondition();
                    }
                    
                    $con->promotion_id = 0;
                    
                    $con->condition_id = $k;
                    
                    $con->coupon_id = $id;
                    
                    $con->min_value = isset($v['min_value']) ? cprice($v['min_value']) : 0;
                    $con->max_value = isset($v['max_value']) ? cprice($v['max_value']) : 0;
                   
                   
                    
                    $con->save();
                    
                   
                }
              
                
            }
            
            
            return $id;
        }
        
        return false;
    }
    
    
    public function getCouponCondition($coupon_id, $condition_id)
    {
        return PromotionCondition::findOne([
            'condition_id'  =>  $condition_id,
            'coupon_id'     =>  $coupon_id,
            
        ]);
    }
    
}
