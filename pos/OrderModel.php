<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\pos;
use Yii;
class OrderModel extends \izi\db\ActiveRecord
{
	 
    public static function tableName()
    {
        return '{{%orders}}';
    }
    
    /**
     * Generation a new code
     * @return string
     */
    public function code(){
        $i = 0; $k = 6;
        $code = strtoupper(randString($k));
        while((new \yii\db\Query())->from(OrderModel::tableName())->where(['code'=>$code])->count(1) > 0){
            $code = strtoupper(randString($i < 10 ? $k : ++$k));
            $i++;
        }
        return $code;
    }
    
    public function getOne($order_code)
    {
        return OrderModel::findOne(['code'=>$order_code]); 
    }
    
    public function getItem($order_code)
    {
        return $this->populateData(OrderModel::find()->where(['code'=>$order_code])->asArray()->one());
    }
    
    public function create($info)
    {
        $info['sid'] = __SID__;
        $info['code'] = $this->code();
        $info['check_sum'] = md5($info['code'] . __SID__);
        
        $order = new OrderModel();
        
        foreach ($info as $k=>$v){
            $order->{$k} = $v;
        }
        
        if($order->save()) return $order;
    }
    
    public function updateData($order_code, $info)
    {
//         $info['sid'] = __SID__;
//         $info['code'] = $this->code();
//         $info['check_sum'] = md5($info['code'] . __SID__);
        
        $order = $this->getOne($order_code);
        
        foreach ($info as $k=>$v){
            $order->{$k} = $v;
        }
        
        if($order->save()) return $order;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}