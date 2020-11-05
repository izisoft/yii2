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
class Pos extends BasePos
{
	
    private $_order;
    
    public function getOrder()
    {
        if($this->_order == null){
            $this->_order = Yii::createObject([
                'class' =>  'izi\\pos\\Order'
            ]);
        }
        return $this->_order;
    }
    
    /**
     * Tạo mã bill tự động
     */
    public function genCode($type_id = 1, $table = ''){
        if($table == ''){
            $table = $this->getModel()->tableName();
        }
        $code = false;
        switch ($type_id){
            case 1: $item['type'] = 'bill'; break;
            case 2: $item['type'] = 'bill_import'; break;
            case 3: $item['type'] = 'bill_export'; break;
        }
        $code_regex = Yii::$app->cfg->app['settings'][$item['type']]['code'];
        
        
        $con = ['sid'=>__SID__];
        $query = (new \yii\db\Query())->from($table)->where(['sid'=>__SID__]);
        if($code_regex['code_before'] != ""){
            $query->andWhere(['like','id',$code_regex['code_before'].'%',false]);
        }
        if($code_regex['code_after'] != ""){
            $query->andWhere(['like','id','%'.$code_regex['code_after'],false]);
        }
        $count = $query->count(1);
        if(isset(Yii::$app->cfg->app['settings'][$item['type']]['code']) && !empty(Yii::$app->cfg->app['settings'][$item['type']]['code'])){
            
            
            $code_rule = Yii::$app->cfg->app['settings'][$item['type']]['code']['code_rule'];
            $code_length = $code_regex['code_length'] > 0 ? $code_regex['code_length'] : 3;
            
            $code_length = max($code_length,strlen($count));
            
            if($code_regex['code_length']>0){
                $code_length = $code_regex['code_length'] - (strlen($code_regex['code_before']) + strlen($code_regex['code_after']));
            }
            
            if(isset($code_regex['sort_asc']) && $code_regex['sort_asc'] == 'on'){
                $code_identity = $count+1;
            }else{
                $code_identity = randString($code_length,$code_regex['code_regex']);
            }
            
            
            $replace_regex['{CODE_IDENTITY}'] = danhso($code_identity,$code_length);
            $replace_regex['{CODE_RANDOM}'] = randString($code_length,$code_regex['code_regex']);
            $replace_regex['{TOUR_START_PLACE}'] = '';
            $replace_regex['{CODE_BEFORE}'] = $code_regex['code_before'];
            $replace_regex['{CODE_AFTER}'] = $code_regex['code_after'];
            
            
            //
            $code = replaceCode ($code_rule,$replace_regex);
            while((new \yii\db\Query())->from($table)->where([
                //'and',[
                'id'=>$code,
                'sid'=>__SID__
                
                //],[
                //		'not in','id',$id
                //]
            ])->count(1)>0){
                if(isset($code_regex['sort_asc']) && $code_regex['sort_asc'] == 'on'){
                    $code_identity++;
                }else{
                    $code_identity = randString($code_length,$code_regex['code_regex']);
                }
                $replace_regex['{CODE_IDENTITY}'] = danhso($code_identity,$code_length);
                $code = replaceCode($code_rule,$replace_regex);
            }
        }
        return $code;
    }
    
    
    public function importBillFromOrder($order_code)
    {
        
        
        $order = $this->getOrder()->model->getItem($order_code);
         
        
        if(!empty($order)){
            
//             if($order['version'] == 1){
//                 return self::importBillFromOrder1($order_code);
//             }
            
            $status = 2;
            $order_id = $order['code'];

            
            $bill['customer'] = $order['customer'];
            $bill['customer']['id'] = $order['mem_id'];
            $bill['list_items'] = [];
            $bill['discount'] = ['value'=>0,'type'=>'%'];
            
            $bill['sub_total'] = $order['total_price'];
            //$bill['total'] = $order['total_price'];
            $bill['total_item'] = $order['total_quantity'];
            $bill['discount_value'] = $order['discount_value'];
            $bill['discount_total'] = $order['discount_value'];
            $bill['ship_total'] = 0;
            
            //
            if(isset($order['coupon']) && !empty($coupon = $order['coupon'])){
                $bill['discount'] = ['value'=>$coupon['discount_value'],'type'=> $coupon['discount_type'] == 1 ? '%' : ''];
            }
            
            $bill['guest_pay'] = $order['total_price'];
            $bill['excess_cash'] = 0;
            $bill['guest_cash'] = [
                'cash'=>$order['total_price'],
                'atm'=>0,
                'visa'=>0
            ];
            
            $bill['currency'] = $order['currency'];
            
            $bill['grand_total'] = $bill['sub_total'] - $bill['discount_total'] + $bill['ship_total'];
            
            $bill['owed_total'] = $bill['guest_pay'] - $bill['grand_total'];
            
            
            if(!empty($order['data'])){
                foreach ($order['data'] as $item_id => $item){
                    $i = Yii::$app->frontend->model->getItem($item_id);
                    $item['code'] = $i['code'];
                    $item['price'] = isset($item['price2']) ? $item['price2'] : $i['price2'];
                    $item['price1'] = isset($item['price1']) ? $item['price1'] : $i['price1'];
                    $item['price2'] = isset($item['price2']) ? $item['price2'] : $i['price2'];
                    $item['icon'] = isset($item['icon']) ? $item['icon'] : $i['icon'];
                    
                    $item['id'] = $item_id;
                    $item['currency'] = $order['currency'];
                    
                    $item['title'] = $i['title'];                    
                    $item['url'] = $i['url'];
                    $item['url_link'] = $i['url_link'];
                    
                    $bill['list_items'][$item_id] = $item;
                    $bill['list_items'][$item_id]['sub_total'] = $item['quantity'] * $item['price'];
                }
            }
//             if(!empty($order['seller'])){
//                 foreach ($order['seller'] as $seller_id => $cart){
//                     if(!empty($cart['list_items'])){
//                         foreach ($cart['list_items'] as $v){
//                             if(!isset($v['code'])){
//                                 $i = \app\modules\admin\models\Content::getItem($v['id']);
//                                 $v['code'] = $i['code'];
//                             }
//                             $v['price'] = $v['price2'];
//                             $v['discount'] = ['value'=>0,'type'=>'%'];
//                             $bill['list_items'][$v['id']] = $v;
//                             $bill['list_items'][$v['id']]['sub_total'] = $v['amount'];
//                         }
//                     }
//                 }
                
//             }
            $bill_id = $this->genCode();
            //
            $data = [
                'id'			=>	$bill_id,
                'sid'			=>	__SID__,
                'created_by'	=>	Yii::$app->user->id,
                'customer_id'	=>	$bill['customer']['id'],
                'created_at'	=>	__TIME__,
                'updated_at'	=>	__TIME__,
                'branch_id'		=>	Yii::$app->user->branch_id,
                'order_id'		=> 	$order_id,
                'status'		=>	$status,
                'total_item'	=>	$bill['total_item'],
                'sub_total'		=>	$bill['sub_total'],
                'grand_total'	=>	$bill['grand_total'],
                'owed_total'	=>	$bill['owed_total'],
                'guest_pay'		=>	$bill['guest_pay'],
                'discount_total'=>  $bill['discount_value'],
                'bizrule'		=> 	json_encode(['bill'=>$bill]),
                'currency'		=>	$order['currency'],
            ];
            
            
            
            if(!empty($this->findBillFromOrder($order_id))){
                unset($data['id']);
                Yii::$app->db->createCommand()->update(PosModel::tableName(), $data,[
                    'order_id'=>$order_id,
                    'sid'=>__SID__
                ])->execute();
                $bill = (new \yii\db\Query())->from(PosModel::tableName())->where([
                    'order_id'=>$order_id,
                    'sid'=>__SID__
                ])->one();
                $bill_id = $bill['id'];
            }else{
                Yii::$app->db->createCommand()->insert(PosModel::tableName(), $data)->execute();
            }
            
            return $bill_id;
        }
        
        
        
    }
    
    
    public function findBillFromOrder($code){
        if($code != ""){
            return PosModel::find()->where([
                'sid'=>__SID__,
                'order_id'=>$code
            ])->asArray()->one();
        }
        return false;
    }
}