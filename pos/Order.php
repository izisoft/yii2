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
class Order extends BasePos
{
	 
    
    public function create($info)
    {
        return $this->getModel()->create($info);
    }
    
    
    public function sentEmail($params)
    {
        $type = isset($params['type']) ? $params['type'] : 'order';
        
        $member = isset($params['member']) ? $params['member'] : [];
        
        $$type = isset($params[$type]) ? $params[$type] : [];
        
        
        $fx = Yii::$app->cfg->contact;
        $fx1 = Yii::$app->cfg->emailSettings;
        
        $fx['sender'] = $fx['email'];
        $fx['short_name']  = $fx['short_name'] != "" ? $fx['short_name'] : $fx['name'];
        
        if(isset($fx1['ORDER_INFOMATION'])){
            $fx['email'] = is_array($fx1['ORDER_INFOMATION']) ? $fx1['ORDER_INFOMATION'] : $fx['email'];
        }
//         $fx['email'] = 'zinzinx8@gmail.com';
        
        /*
        
        // Gửi thông báo
        //                     $notis = [
        //                         'title'=>'Bạn có đơn hàng mới <span class="underline italic">'.$order->code.'</span>',
        // //                         'link'=>getAbsoluteUrl(\app\modules\admin\models\AdminMenu::get_menu_link('orders')) .DS.'edit?id=' .($orderID),
        //                         //'uid'=>Yii::$app->user->id
        //                     ];
        //                     \app\models\Notifications::insertNotification($notis);
        // - Gửi cho Admin
        
        */
        
        $notis = [
            'title'=>'Đơn hàng mới ' . $order->code,
            'link'=>'/admin/don-dat-hang-tu-web/edit?id=' . $order->id,
            //'uid'=>Yii::$app->user->id
        ];
        \app\models\Notifications::insertNotification($notis);
         
        $sented = false;
        
        $from_email = validateEmail($member['email']) ? $member['email'] : 'no-reply@'.DOMAIN;
        
        if(Yii::$app->mailer->sendEmail([
            'subject'=>Yii::$app->cfg->contact['short_name'] . ': Đơn đặt hàng mới '.$order->code.'',
            'body'=>$this->renderEmailForAdmin($order->code),
            'from'=>$from_email,
            'fromName'=>$fx['short_name']  . ' - ' . $member['name'],
            'replyTo'=>$from_email,
            'replyToName'=>$member['name'],
            'to'=>$fx['email'],
        ])){
            $sented = true;
            if(validateEmail($member['email'])){
                Yii::$app->mailer->sendEmail([
                    'subject'=>Yii::$app->cfg->contact['short_name'] . ': Thông tin đơn đặt hàng '.$order->code.'',
                    'body'=>$this->renderEmailForCustomer($order->code),
                    'from'=>$fx['sender'],
                    'fromName'=>$fx['short_name'] != "" ? $fx['short_name'] : $fx['name'],
                    'replyTo'=>$fx['email'],
                    'replyToName'=>$fx['short_name'],
                    'to'=>$member['email'],'toName'=>$member['name']
                ]);
            }
        }
         
        
        
        return $sented;
    }
    
    
    
    
    
    public function renderEmailForCustomer($order_code){
        
        $order = $this->getModel()->getItem($order_code);
        
        
        $html = '';
        
        $text1 = Yii::$app->frontend->getTextRespon(array('code'=>'RP_ORDER_CUS', 'show'=>false));
        $regex = [
            
            '{{%LOGO}}'=>'<a target="_blank" href="'.ABSOLUTE_DOMAIN.'">
            <img style="max-height:100px;max-width:300px" alt="logo" class="" src="'.(isset(Yii::$app->cfg->app['logo']['image']) ? getAbsoluteUrl(Yii::$app->cfg->app['logo']['image'])  : '').'" /></a>',
            '{{%MY_COMPANY_NAME}}'=>Yii::$app->cfg->contact['name'],
            '{{%MY_COMPANY_ADDRESS}}'=>Yii::$app->cfg->contact['address'],
            '{{%MY_COMPANY_PHONE}}'=>'Hotline: ' .Yii::$app->cfg->contact['hotline'],
            '{{%MY_COMPANY_EMAIL}}'=>'Email: ' .Yii::$app->cfg->contact['email'],
            '{{%MY_COMPANY_INFOMATION}}'=>'<b>' . Yii::$app->cfg->contact['name'] . '</b><br/>
Địa chỉ: '.Yii::$app->cfg->contact['address'].'<br/>
Hotline: '.Yii::$app->cfg->contact['hotline'].'<br/>
Email: '.Yii::$app->cfg->contact['email'].'<br/>
',
            '{{%DOMAIN}}'=>DOMAIN,
            '{{%DOMAIN_LINK}}'=>ABSOLUTE_DOMAIN,
            
            '{{%ORDER_NUMBER}}'	=>	$order['code'],
            '{{%ORDER_TIME}}'	=>	date('d/m/Y H:i',strtotime($order['time'])),
            '{{%ORDER_TAX_INFOMATION}}'	=>	'',
            '{{%ORDER_OTHER_REQUEST}}'	=> isset($order['other_request']) ?	uh($order['other_request']) : '',
            '{{%ORDER_PRODUCTS_LIST}}'	=>	$this->renderOrderProducts($order['code'], ['ref' => 'email-customer']),
            '{{%ORDER_PAYMENT_METHOD}}'	=>	isset($order['payment_method']) ? \app\models\States::showState($order['payment_method']) : '',
            
            '{{%CUSTOMER_NAME}}'	=>	isset($order['customer']['name']) ? $order['customer']['name']
            : (isset($order['guest']['name']) ? $order['guest']['name'] : ''),
            '{{%CUSTOMER_PHONE}}'	=>	isset($order['customer']['phone']) ? $order['customer']['phone']
            : (isset($order['guest']['phone']) ? $order['guest']['phone'] : ''),
            '{{%CUSTOMER_EMAIL}}'	=>	isset($order['customer']['email']) ? $order['customer']['email']
            : (isset($order['guest']['email']) ? $order['guest']['email'] : ''),
            '{{%CUSTOMER_ADDRESS}}'	=>	isset($order['customer']['address']) ? $order['customer']['address']
            : (isset($order['guest']['address']) ? $order['guest']['address'] : ''),
            //'{{%CUSTOMER_NAME}}'	=>	'',
            
            
            
            
            
            
            
            
        ];
        $html .= replace_text_form($regex, uh($text1['value'],2));
        return $html;
    }
    
    
    
    
    
    public function renderEmailForAdmin($order_code){
        
        $order = $this->getModel()->getItem($order_code);
         
        $html = '';
        
        $text1 = Yii::$app->frontend->getTextRespon(array('code'=>'RP_ORDER_ADMIN', 'show'=>false));
        $regex = [
            
            '{{%LOGO}}'=>'<a target="_blank" href="'.ABSOLUTE_DOMAIN.'">
            <img style="max-height:100px;max-width:300px" alt="logo" class="" src="'.(isset(Yii::$app->cfg->app['logo']['image']) ? getAbsoluteUrl(Yii::$app->cfg->app['logo']['image'])  : '').'" /></a>',
            '{{%MY_COMPANY_NAME}}'=>Yii::$app->cfg->contact['name'],
            '{{%MY_COMPANY_ADDRESS}}'=>Yii::$app->cfg->contact['address'],
            '{{%MY_COMPANY_PHONE}}'=>'Hotline: ' .Yii::$app->cfg->contact['hotline'],
            '{{%MY_COMPANY_EMAIL}}'=>'Email: ' .Yii::$app->cfg->contact['email'],
            '{{%MY_COMPANY_INFOMATION}}'=>'<b>' . Yii::$app->cfg->contact['name'] . '</b><br/>
Địa chỉ: '.Yii::$app->cfg->contact['address'].'<br/>
Hotline: '.Yii::$app->cfg->contact['hotline'].'<br/>
Email: '.Yii::$app->cfg->contact['email'].'<br/>
',
            '{{%DOMAIN}}'=>DOMAIN,
            
            '{{%ADMIN_LINK}}' => ABSOLUTE_DOMAIN . '/admin',
            
            '{{%ORDER_NUMBER}}'	=>	$order['code'],
            '{{%ORDER_TIME}}'	=>	date('d/m/Y H:i',strtotime($order['time'])),
            '{{%ORDER_TAX_INFOMATION}}'	=>	'',
            '{{%ORDER_OTHER_REQUEST}}'	=> isset($order['other_request']) ?	uh($order['other_request']) : '',
            '{{%ORDER_PRODUCTS_LIST}}'	=>	$this->renderOrderProducts($order['code'], ['ref' => 'email-admin']),
            '{{%ORDER_PAYMENT_METHOD}}'	=>	isset($order['payment_method']) ? \app\models\States::showState($order['payment_method']) : '',
            
            '{{%CUSTOMER_NAME}}'	=>	isset($order['customer']['name']) ? $order['customer']['name']
            : (isset($order['guest']['name']) ? $order['guest']['name'] : ''),
            '{{%CUSTOMER_PHONE}}'	=>	isset($order['customer']['phone']) ? $order['customer']['phone']
            : (isset($order['guest']['phone']) ? $order['guest']['phone'] : ''),
            '{{%CUSTOMER_EMAIL}}'	=>	isset($order['customer']['email']) ? $order['customer']['email']
            : (isset($order['guest']['email']) ? $order['guest']['email'] : ''),
            '{{%CUSTOMER_ADDRESS}}'	=>	isset($order['customer']['address']) ? $order['customer']['address']
            : (isset($order['guest']['address']) ? $order['guest']['address'] : ''),
            //'{{%CUSTOMER_NAME}}'	=>	'',
            
            
            
            
            
            
            
            
        ];
        $html .= replace_text_form($regex, uh($text1['value'],2));
        return $html;
    }
    
    
    
    public function renderProductList($params = []){
        
        $seller_id = isset($params['seller_id']) ? $params['seller_id'] : 0;
        
        $html = '<table cellpadding="0" cellspacing="0" class="table table-bordered vmiddle table-striped" style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;">';
        $html .= '<colgroup><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"></colgroup>';
        $html .= '<thead><tr>
<th colspan="9" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;background-color:#dedede;">Sản phẩm</th>

<th colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">SL</th>
<th colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">Đơn giá</th>
</tr></thead><tbody>';
        $carts = Yii::$app->cart->get();
        
      
        //
        if($seller_id>0 && isset($carts['seller'])){
            //
            foreach ($carts['seller'] as $seller=>$cart){
                
              
                
                if($seller_id==$seller){
                    if(!empty($cart['list_items'])){
                        foreach ($cart['list_items'] as $v){
                            $html .= '<tr>
<td colspan="9" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"><a target="_blank" href="'.getAbsoluteUrl($v['url_link']).'">'.uh($v['title']).'</a></td>

<td colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center"><b>'.$v['quantity'].'</b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red">'.Yii::$app->frontend->showPrice($v['price2'],$v['currency']).'</b></td>
</tr>';
                        }
                    }
                    
                    $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng cộng </b> ('.$cart['total_quantity'].' sản phẩm)</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($cart['total_price'],$cart['currency']).'</b></td>
</tr>';
                    
                    
                    
                    
                    if(isset($cart['note']) && $cart['note'] != ""){
                        $html .= '<tr>
<td colspan="12" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:left"><b>Ghi chú: </b> '.uh($cart['note']).'</td>
    
</tr>';
                    }
                }
            }
        }elseif( isset($carts['seller'])){
            //
            foreach ($carts['seller'] as $seller=>$cart){
                
                if(!empty($cart['list_items'])){
                    if($seller>0){
                        $html .= '<tr>
<td colspan="12" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:left "><b>EMZ SHOP</b></td>
                            
</tr>';
                    }
                    
                    foreach ($cart['list_items'] as $v){
                        $html .= '<tr>
<td colspan="9" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"><a target="_blank" href="'.getAbsoluteUrl($v['url_link']).'">'.uh($v['title']).'</a></td>

<td colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center"><b>'.$v['quantity'].'</b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red">'.Yii::$app->frontend->showPrice($v['price2'],$v['currency']).'</b></td>
</tr>';
                    }
                }
                
                
                $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng cộng </b> ('.$carts['total_quantity'].' sản phẩm)</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($carts['total_price'],$carts['currency']).'</b></td>
</tr>';
                
                if(!empty($coupon = $carts['coupon'])){
                $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Chiết khấu </b> ('.$coupon['code'].')</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($carts['discount_value'],$carts['currency']).'</b></td>
</tr>';
                
                $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng thanh toán </b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($carts['total_price_after_discount'],$carts['currency']).'</b></td>
</tr>';
                }
                if(isset($cart['note']) && $cart['note'] != ""){
                    $html .= '<tr>
<td colspan="12" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:left"><b>Ghi chú: </b> '.uh($cart['note']).'</td>
    
</tr>';
                }
                
            }
        }
        //}
        
        $html .= '</tbody></table>';
        return $html ;
    }
    
    
    public function getOrderDetail($order_code)
    {
        $order = $this->getModel()->getItem($order_code);
        
        return ($order);
    }
    
    
    public function renderOrderProducts($order_code, $param = [])
    {
        $html = '';
        
        $ref = isset($param['ref']) ? $param['ref'] : 'origin';
        
        $order = $this->getOrderDetail($order_code);
        if(!empty($order) && !empty($order['data'])){
            
            $html .= '<table cellpadding="0" cellspacing="0" class="table table-bordered vmiddle table-striped" style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;">';
            $html .= '<colgroup><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"></colgroup>';
            $html .= '<thead><tr>
<th colspan="9" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;background-color:#dedede;">Sản phẩm</th>
                
<th colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">SL</th>
<th colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">Đơn giá</th>
</tr></thead><tbody>';
            
            foreach ($order['data'] as $item_id => $item){
                
                 
                $attr_label = [];
                switch ($order['products_version']){
                    case 'v2':
                        
                        $root_item_id = isset($item['root_item_id']) && $item['root_item_id']> 0 ?$item['root_item_id'] : $item_id;
                        
                        $i2 = Yii::$app->product->model->getItem($root_item_id);

                        $item_info ['name'] = $item_info['title'] = Yii::$app->product->getAttrValue($root_item_id, 'name');
                        $item_info ['url_link'] = Yii::$app->product->getAttrValue($root_item_id, 'url_link');
                        
                        $prices = Yii::$app->product->getPrices($item_id);
                        
                        $sale_price = isset($item['price']) ? $item['price'] : $prices->sale_price;
                        
                        if(isset($i2->type_id)){
                            switch ($i2->type_id){
                                case 'configurable':
                                    $configurable_attributes = Yii::$app->product->model->getJsonAttributeValue($item['root_item_id'], 'settings', 'configurable_attributes');
                                    $ops = []; $ev = []; $attr_label = [];
                                    
//                                     $attr_label .= '<div class="top-part"><div class="item-name itemName f12px">';
                                    foreach($configurable_attributes as $attribute_id => $option_ids){
                                        $eav = Yii::$app->product->getEavAtrributeById($attribute_id);
                                        
                                        
                                        
                                        $options = Yii::$app->product->getListEavAttrOption($eav->attribute_code, $option_ids);
                                        foreach($options as $option){
                                            $ops[$option->option_id] = $option;
                                        }
                                        
                                        $option_id = Yii::$app->product->getAttrValue($item_id,$eav->attribute_code);
                                        
                                        $attr_label[] = $eav->frontend_label . ': ' . ($ops[$option_id])->value;
                                        
                                        
                                        // 			            view($option_id, $v['i']);
                                        
                                        //$attr_label[] = ($ev[$attribute_id])->frontend_label . ': '
                                        //. ($ops[Yii::$app->product->getAttrValue($v['root_item_id'], ($ev[$attribute_id])->attribute_code)])->value;
                                    }
//                                     echo implode(' | ', $attr_label);
//                                     echo '</div></div>';
                                    break;
                            }
                        }
                        
                        
                        
                        break;
                    
                    default:
                        $item_info = Yii::$app->frontend->model->getItem($item_id); 
                        $sale_price = isset($item_info['price2']) ? $item_info['price2'] : 0;
                        break;
                }
                
                
                
                if(empty($item_info)){
                    $item_info = $item;
                }
                
                if(!empty($item_info)){
                    
                    if(isset($item['price2']) && $item['price2'] > 0)
                    {
                        $item_info['price'] = $sale_price = $item['price'];
                    }
                    
                    $html .= '<tr>
<td colspan="9" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;">
<a style="display:inline-block;width:100%" target="_blank" href="'.getAbsoluteUrl($item_info['url_link']).'?_ref='.$ref.'">'.uh($item_info['title']).'</a>
'.implode(' | ' , $attr_label).'
</td>
    
<td colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center"><b>'.$item['quantity'].'</b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red">'.Yii::$app->frontend->showPrice($sale_price,$order['currency']).'</b></td>
</tr>';
                }
            }
            
            $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng cộng </b> ('.$order['total_quantity'].' sản phẩm)</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($order['total_price'],$order['currency']).'</b></td>
</tr>';
            
            if(isset($order['coupon']) && !empty($coupon = $order['coupon'])){
                $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Chiết khấu </b> ('.$coupon['code'].')</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($order['discount_value'],$order['currency']).'</b></td>
</tr>';
                
                $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng thanh toán </b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.Yii::$app->frontend->showPrice($order['total_price_after_discount'],$order['currency']).'</b></td>
</tr>';
            }
            
            $html .= '</tbody></table>';
        }
        return $html;
    }
}