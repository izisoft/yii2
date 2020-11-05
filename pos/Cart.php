<?php 
namespace izi\pos;
use Yii;

class Cart extends \yii\base\Component
{
    public $tableName = '{{%cart}}';
    
    private $_key_store, $_key_coupon;
    private $_session_name; 
    private $db;
    
    private $_key_store_version;
    
    private $_config ;
    
    private $_version = 'v1';
    
    
    public $session, $read_cookies , $cookies;
    
    public function __construct() {
        $this->db = Yii::$app->dbs;
        
        $this->read_cookies = Yii::$app->request->cookies;
        $this->cookies = Yii::$app->response->cookies;
        $this->session = Yii::$app->session;
        
        $this->_key_store = md5(DOMAIN . '/cart');
        $this->_key_store_version = md5(DOMAIN . '/cart/version');
        $this->_key_coupon = md5(DOMAIN . '/cart/coupon');
        
        $this->_session_name = md5(DOMAIN . '/cart/get_sesion_driver');
        
        $this->init();
        
        
    }
    
    public function getConfig()
    {
        if($this->_config == null){
            $this->_config['payment_method'] = isset(Yii::$app->cfg->app['payment_method']) ? Yii::$app->cfg->app['payment_method'] : null;
        }
        return $this->_config;
    }
    
    
    public function init($refresh = false)
    {
        
        $crc = md5(__METHOD__ . (Yii::$app->member->isGuest ? 0 : 1));
        /**
         * Cart được lưu ở 3 nơi : Session, Cookies, Database.
         * 1. Khi cart được gọi lần đầu hệ thống sẽ kiểm tra trong DB (nếu member đăng nhập) hoặc Cookies (nếu ko có data trong DB)
         * 2. Dữ liệu trả về từ kết quả trên sẽ được lưu vào Session
         * 3. Các thay đổi trên session sau đó (khách hàng sửa giỏ hàng) sẽ được cập nhật vào Cookie & DB
         *
         */
        
        $ver = isset(Yii::$app->cfg->setting['form']['products']['version']) ? Yii::$app->cfg->setting['form']['products']['version'] : 'v1';
        
//         $ver = $this->session->get($this->_key_store_version);
        
        if($ver != $this->_version){
            switch ($ver) {
                case 'v2':
                    return Yii::$app->cart2->init();
                    break;
                    
                default:
                    ;
                    break;
            }
        }
         
        
        $cart = $this->getSessionItems() ;
        if(!($this->session->get($crc, false)) || empty($cart)){
            //
            if(!Yii::$app->member->isGuest){
                $cart = $this->getDbItems();
            }
            
            if(empty($cart)){
                $cart = $this->getCookiesItems();
            }
            
            //
            if(!empty($cart)){
                $this->setSessionItems($cart);
            }
            
        }
        
        if(!$this->session->get($crc, false) ){
            $this->sync();
            if(!empty($cart)){
                $this->session->set($crc, true);
            }
        }
        
    }
    
    /**
     * Đồng bộ giỏ hàng theo dữ liệu mới nhất từ session
     */
    public function syncSession()
    {
        $this->setCookiesItems($c1 = $this->getSessionItems());
        $this->setDbItems($c1);
    }
    
    /**
     * Đồng bộ giỏ hàng theo dữ liệu mới nhất từ Db
     */
    public function syncDb()
    {
        $this->setCookiesItems($c1 = $this->getDbItems());
        $this->setSessionItems($c1);
    }
    
    /**
     * Đồng bộ giỏ hàng giữa Session - Cookies - Db
     * Dữ liệu trả về sẽ là hợp của toàn bộ các sản phẩm có trong db & session & cookies
     * Dữ liệu mới nhất luôn lưu ở Session
     */
    
    public function sync()
    {
        $c1 = $this->getSessionItems();
        $c2 = $this->getCookiesItems();
        $c3 = $this->getDbItems();
        
        $changed = false;
        
        if(!empty($c1)){
            $this->setCookiesItems($c1);
            if(!Yii::$app->member->isGuest){
                foreach ($c1 as $item_id    => $value){
                    
                    $value['sid'] = __SID__;
                    $value['customer_id']= Yii::$app->member->id;
                    
                    $last_modify = max($value['time'], isset($value['last_modify']) ? $value['last_modify'] : 0);
                    
                    if(!isset($c3[$item_id])){
                        $this->db->createCommand()->insert($this->tableName, $value)->execute();
                    }else{
                        $last_modify2 = max($c3[$item_id]['time'], isset($c3[$item_id]['last_modify']) ? $c3[$item_id]['last_modify'] : 0);
                        
                        if($last_modify2 > $last_modify){
                            $c1[$item_id] = $c3[$item_id];
                            $changed = true;
                        }else{
                            $this->db->createCommand()->update($this->tableName, $value, [
                                'sid'=>__SID__,
                                'customer_id'=>Yii::$app->member->id,
                                'item_id'=>$item_id
                            ])->execute();
                        }
                        
                    }
                }
            }
        }else{
            
            if(!empty($c3)){
                $changed = true;
                $c1 = $c3;
            }elseif(!empty($c2)){
                $changed = true;
                $c1 = $c2;
                $this->setDbItems($c2);
            }
        }
        
        if($changed){
            $this->setSessionItems($c1);
            $this->setCookiesItems($c1);
        }
        
    }
    

    
    
    public function add($params)
    {
        
        $cart = $this->getSessionItems();
        
        
        
        $item = isset($cart[$params['item_id']]) ? $cart[$params['item_id']] : [];
        
        $item['quantity'] = (isset($item['quantity']) ? $item['quantity'] : 0) + (isset($params['quantity']) ? $params['quantity'] : 1);
        $item['item_id']    =   $params['item_id'];
        
        $i = Yii::$app->frontend->getArticle($item['item_id']);
        
        $item['price2']     =   $i['price2'];
        
        $item['customer_id']    =   isset($params['customer_id']) ? $params['customer_id'] : 0;
        $item['seller_id']    =   isset($params['seller_id']) ? $params['seller_id'] : 0;
        $item['time'] = $item['last_modify'] = time();
        $item['sid'] = __SID__;
        
        $cart[$params['item_id']] = $item;
        
        $this->setSessionItems($cart);
        
//         $this->sync();
        
        $this->setCookiesItems($cart);
        $this->setDbItems($cart);

    }
    
    public function update($params)
    {
        
        /**
         * check behavior
         */
        
        $cart = $this->getSessionItems();
        
        $item = isset($cart[$params['item_id']]) ? $cart[$params['item_id']] : [];
        
        $behavior = isset($params['behavior']) ? $params['behavior'] : 'set';
        
        
        switch ($behavior){
            
            // Giảm số lượng đi 1 đơn vị
            case 'desc': 
            case 'minus':
                $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
                $quantity --;
                break;
                
            // Tăng số lượng đi 1 đơn vị
            case 'inc':
            case 'plus':
                $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
                $quantity ++;
                break;
                
            // Đặt số lượng cố định
            default:
                
                $quantity = isset($params['quantity']) ? $params['quantity'] : 0;                
                
                break;
        }
        
        if($quantity < 1){
            return $this->remove($params['item_id']);
        }
        
        $item['last_modify'] = time();
        $item['quantity'] = $quantity;
        
        
        
        
        if(!isset($item['item_id'])) $item['item_id'] = $params['item_id'];
        
        
        
        $this->setSessionItem($params['item_id'], $item) ;
        
       
        
        $this->sync();
        
        return $this->getItem($params['item_id']);
    }
    
    
    // Clear all cart
    public function clear()
    {
        return $this->remove(0);
    }
    
    // Remove single item
    // if item_id = 0 : remove all items
    public function remove($item_id)
    {
        if($item_id > 0){
            // Remove single item 
            
            // Db             
            $this->removeDbItem($item_id);
            
            // Cookies
            $this->removeCookiesItem($item_id);
            
            // Session
            $this->removeSessionItem($item_id);
            
            if(empty($this->getSessionItems())){
                return $this->remove(0);
            }
            
        }else{
            // Remove all

            // Remove Db
            $this->clearDbItems();
            // Remove Cookies
            $this->clearCookiesItems();
            // Remove Session
            $this->clearSessionItems();
            
            $this->session->remove($this->_key_coupon);
            $this->session->remove($this->_key_store);
            
        }
        
    }
    
    /**
     * Get all cart data from session
     * @return array|unknown|mixed
     */
    public function getData()
    {
        $ver = $this->session->get($this->_key_store_version);
        
        if($ver != $this->_version){
            switch ($ver) {
                case 'v2':
                    return Yii::$app->cart2->getData();
                    break;
                    
                default:
                    ;
                    break;
            }
        }
        
        
        $l = $this->getSessionItems();
        
        if(!empty($l)){
            foreach($l as $item_id => $v){
                $i = Yii::$app->frontend->getArticle($item_id);
                if(!empty($i)){
                    $l[$item_id]['price2'] = $i['price2'];
                }
            }
        }
        
        return $l;
    }
    
    public function getVersion()
    {
        return $this->session->get($this->_key_store_version);
    }
  
    /*
     * Get single item
     */
    public function getItem($item_id)
    {
        return $this->get($item_id);
    }
    
    /**
     * get cart data width caculate price
     * @param number $_item_id
     * @param array $params
     * @return number[]|unknown[]|string[]|array[]|number[]|array[]|number[][][]|unknown[][][]|string[][][]|array[][][]|unknown[]
     */
    
    public function get($_item_id = 0, $params = [])
    {
        
        $ver = $this->session->get($this->_key_store_version);
        
        if($ver != $this->_version){
            switch ($ver) {
                case 'v2':
                return Yii::$app->cart2->get($_item_id, $params);
                break;
                
                default:
                    ;
                break;
            }
        }
        
        if ($_item_id ==0 && $this->session->has($this->_key_store)) {
//             return $session->get($this->_session_name);
        }
        
        $total_items = $total_quantity = $total_price = 0;
        $currency = 1;
        $seller = $list_items = [];
         
        $cart = $this->getSessionItems();
         
        $coupon = $this->getCoupon();
        
        if(!empty($cart)){
            array_sort($cart, 'seller_id');
            $total_items = count($cart);
            
            foreach ($cart as $item_id => $item){
                $total_quantity += $item['quantity'];
                
                $i = Yii::$app->frontend->getArticle($item_id);
                if(!empty($i)){

                    $total_price    +=  $item['quantity'] * $i['price2'];
                    
                    $itemd = [
                        'id'            =>  $i['id'],
                        'code'          =>  $i['code'],
                        'title'         =>  $i['title'],
                        'url'           =>  $i['url'],
                        'url_link'      =>  $i['url_link'],
                        'price1'        =>  $i['price1'],
                        'price2'        =>  $i['price2'],
                        'price3'        =>  $i['price3'],
                        'quantity'      =>  $item['quantity'],
                        'amount'        =>  $item['quantity'] * $i['price2'],
                        'icon'          =>  isset($i['icon']) ? $i['icon'] : '',
                        'currency'      =>  $i['currency'],
                        
                        'sid'            =>  $i['sid'],
                        'list_images'   =>  isset($i['list_images']) ? $i['list_images'] : (isset($i['listImages']) ? $i['listImages'] : []),
                        
                        
                    ];
                    
                    $seller[$item['seller_id']]['list_items'][$item_id] = $itemd;
                    
                    $seller[$item['seller_id']]['total_items'] = (isset($seller[$item['seller_id']]['total_items']) ? $seller[$item['seller_id']]['total_items'] : 0) + 1;
                                        
                    $seller[$item['seller_id']]['total_quantity'] = (isset($seller[$item['seller_id']]['total_quantity']) ? $seller[$item['seller_id']]['total_quantity'] : 0) + $item['quantity'];
                    
                    $seller[$item['seller_id']]['total_price'] = (isset($seller[$item['seller_id']]['total_price']) ? $seller[$item['seller_id']]['total_price'] : 0) + ($item['quantity'] * $i['price2']);
                    
                    $list_items[$item_id] = $itemd;
                    
                    if($_item_id > 0 && $item_id == $_item_id){
                        return $itemd;
                    }
                    
                }
            }                                   
            
        }
        
        $validated = true;
        
        $discount_alert = '';
        
        $discount_value = 0;
        
        if(!empty($coupon) && $coupon['state'] == 1 && !empty($coupon['conditions'])){
        
            $time1 = strtotime($coupon['from_date']);
            
            $time2 = strtotime($coupon['to_date'] . ' 23:59:59');
            
            $time = time();
            
            if($time1 > $time ){
                $validated = false;
                $discount_alert .= '<p class="help-block mgb0">Khuyến mại có hiệu lực từ <b>'.date('d/m/Y H:i', $time1).'</b></p>';
            }
            
            if($time2 < $time){
                $validated = false;
                $discount_alert .= '<p class="help-block mgb0">Khuyến mại đã kết thúc <b>'.date('d/m/Y H:i', $time2).'</b></p>';
            }
            
            
            foreach($coupon['conditions'] as $condition){
                
                if(!$validated) break;
                
                switch ($condition['code']) {
                    case 'total_product_per_invoice': // Tổng số sản phẩm / đơn hàng
                        
                        if($total_quantity < $condition['min_value']){
                            $validated = false;
                            
                            $discount_alert .= '<p class="help-block mgb0">
                            Số lượng sản phẩm thấp hơn <b>'.number_format($condition['min_value']).'</b></p>';
                            
                            
                            break;
                        }
                        
                        
                        break;
                    case 'total_price_per_invoice': // Tổng số tiền / đơn hàng
                         
                        
                        if($total_price < $condition['min_value']){
                            
                            $discount_alert .= '<p class="help-block mgb0">Giá trị đơn hàng thấp hơn <b>'.Yii::$app->frontend-> showPrice($condition['min_value'] ,$currency).'</b></p>';
                            
                            $validated = false;
                            break;
                        }
                        
                        break;
                    case 'product_price': // Giá trị sản phẩm
                        ;
                        break;
                    
                     
                }
            }
 
            unset($coupon['conditions']);
            
            
            
            if($validated){
                
                $discount_value = $coupon['discount_value'];
                
                switch ($coupon['discount_type']) {
                    case 1: // %
                        
                        $discount_value = $discount_value / 100 * $total_price;
                        
                        break;
                    
                    default:
                        ;
                    break;
                }
                
                
                
                if($coupon['max_discount_value'] > 0 && $coupon['max_discount_value'] < $discount_value){
                    $discount_value = $coupon['max_discount_value'];
                }
                 
                
            }
            
        }else{
            $validated = false;
            $discount_alert .= '<p class="help-block mgb0">Chương trình đã kết thúc.</p>';
        }
        
    
        
        if(!$validated){
            $discount_alert = 'Khuyến mại không hợp lệ:</br>' . $discount_alert;
        }
        
        $array = [
            'version'           =>  $this->_version,
            'total_items'	    =>	$total_items,
            'total_quantity'    =>	$total_quantity,
            
            'total_price_before_discount'       =>	$total_price,
            'total_price'       =>	$total_price,
            
            'currency'	        =>  $currency,
            'list_items'	    =>	$list_items,
            'seller'	        =>	$seller,
            'coupon'            =>  $coupon,
            
            /**
             * 
             */
            
            'discount_state'    =>  $validated,
            'discount_alert'    =>  $discount_alert,
            'total_price_after_discount'       =>	$total_price - $discount_value,
            'discount_value'   =>  $discount_value,
        ];
        
        $this->session->set($this->_session_name, $array);
        
        return $array;
    }
    
    
    public function getCoupon()
    {
        return $this->session->get($this->_key_coupon);
    }
    
    public function removeCoupon()
    {
        $this->session->remove($this->_key_coupon);
        $this->session->remove($this->_session_name);
    }
    
    public function addCoupon($coupon)
    {
        if(is_string($coupon)){
            $coupon = Yii::$app->promotion->coupon->getCoupon($coupon, 1);
            
        }
        
        if(!empty($coupon)){
            $conditions = Yii::$app->promotion->coupon->getCouponConditions($coupon['id']);
            
            $coupon['conditions'] = $conditions;                        
            
            $this->session->set($this->_key_coupon , $coupon);
            
            //$this->setSessionItems($items);
            
//             view($items, 1,1);
            
//             $this->sync();
            
//             if(!empty($conditions)){
                
//                 foreach($conditions as $condition){
//                     view($condition);
//                 }
                
//             }
        }
    }
   
    
    /**
     * Update new 01/2019
     * Level: Session -> Cookies
     *                -> Database  
     */
    
    
    /**
     *  Get single item from session
     */
    protected function getSessionItem($item_id)
    {
        return $this->getSessionItems($item_id);
    }
    
    
    /**
     *  Get all item from session
     */
    protected function getSessionItems($item_id = 0)
    { 
        $cart = $this->session->get($this->_key_store, []);
        
        if($item_id > 0){
            return isset($cart[$item_id]) ? $cart[$item_id] : [];
        }
         
        
        return is_array($cart) ? $cart : [];
    }
    
    /**
     *  Store list items to session
     */
    
    protected function setSessionItems($items)
    {
        if(is_array($items)){ 
            $this->session->set($this->_key_store, $items);
        }else{
            
            $this->session->remove($this->_key_store);   
        }
    }
    
    /**
     *  Store single item to session
     */
    protected function setSessionItem($item_id, $value)
    {        
        
         
        $items = $this->getSessionItems();        
        $items[$item_id] = $value;

        $this->setSessionItems($items); 
        
    }
    
    
    /**
     *  Remove single item from session
     */
    protected function removeSessionItem($item_id)
    {
        
        $cart = $this->getSessionItems();
        
        if(isset($cart[$item_id])){
            unset($cart[$item_id]);
        }
        $this->setSessionItems($cart);
    }
    
    
    /**
     *  Remove all items from session
     */
    protected function clearSessionItems()
    {
        $session = Yii::$app->session;
        $session->remove($this->_key_store);
        $session->remove($this->_session_name);
        
    }
    
    
    /**
     * Cookies
     * 
     */
    
    /**
     *  store list items to cookies
     */
    
    protected function setCookiesItems($items)
    {
        // Cookie stored
//         $cookies = Yii::$app->response->cookies;
        
//         $cookies->remove($this->_key_store);
//         $cookies->remove($this->_key_store, false);
        
        // add a new cookie to the response to be sent
        $this->cookies->add(new \yii\web\Cookie([
            'name'  =>  $this->_key_store,
            'value' =>  $items,
            'expire' => time() + 86400 * 30,
        ]));
        
    }
    
    /**
     *  store single item to cookies
     */
    
    protected function setCookiesItem($item_id, $value)
    {
        // Cookie stored
//         $cookies = Yii::$app->response->cookies;
        
        //         $cookies->remove($this->_key_store);
        //         $cookies->remove($this->_key_store, false);
        
        $items = $this->getSessionItems();
        
        $items[$item_id] = $value;
        
        // add a new cookie to the response to be sent
        $this->cookies->add(new \yii\web\Cookie([
            'name'  =>  $this->_key_store,
            'value' =>  $items,
            'expire' => time() + 86400 * 30,
        ]));
        
    }
    
    /**
     *  get all item from cookies
     */
    protected function getCookiesItems($item_id = 0)
    {
        $cart = $this->read_cookies->getValue($this->_key_store, []);
        
        if($item_id > 0){
            return isset($cart[$item_id]) ? $cart[$item_id] : [];
        }
        
        return $cart;
    }
    
    /**
     *  get single item from cookies
     */
    
    protected function getCookiesItem($item_id)
    {
        return $this->getCookiesItem($item_id);
    }
    
    
    /**
     *  Remove single item from cookies
     */
    protected function removeCookiesItem($item_id)
    {
        $items = $this->getCookiesItems();
        
        if(isset($items[$item_id])){
            unset($items[$item_id]);
        }
        
        $this->setCookiesItems($items);
    }
    
    
    /**
     *  Remove all items from cookies
     */
    protected function clearCookiesItems()
    {

        $this->cookies->remove($this->_key_store);
        $this->cookies->remove($this->_key_store, true);
    }
    
    
    /**
     *  DB ITEMS
     */
    
    
    
    protected function getDbItems($item_id = 0)
    {
        if(!Yii::$app->member->isGuest){
            if($item_id>0){
                return (new \yii\db\Query())->from($this->tableName)->where([
                    'sid'=>__SID__,
                    'customer_id'=>Yii::$app->member->id,
                    'item_id'   =>  $item_id,
                ])->one($this->db);
            }
            
            $l = (new \yii\db\Query())->from($this->tableName)->where([
                'sid'=>__SID__,
                'customer_id'=>Yii::$app->member->id
            ])->all($this->db);
            
            $items = [];
            if(!empty($l)){
                foreach ($l as $v) {
                    $items[$v['item_id']] = $v;
                }
            }
            
            return $items;
        }
    }
    
    protected function getDbItem($item_id)
    {
        return $this->getDbItems($item_id);
    }
    
    protected function setDbItems($items)
    {
        
        if(!Yii::$app->member->isGuest){
            if(!empty($items)){
                foreach ($items as $item_id=>$value){
                    $this->setDbItem($item_id, $value);
                }
            }
            
            $this->db->createCommand()->delete($this->tableName, ['and',[
                'sid'   =>  __SID__,
                'customer_id'   =>  Yii::$app->member->id
            ], [
                'not in'  , 'item_id',  array_keys($items)
            ]])->execute();
        }
    }
    
    protected function setDbItem($item_id, $value)
    {
        if(!Yii::$app->member->isGuest){
            $value['item_id']   =   $item_id;
            $value['sid']   =   __SID__;
            
            if((new \yii\db\Query())->from($this->tableName)->where([
                'sid'=>__SID__,
                'item_id'=>$item_id,
                'customer_id'=>Yii::$app->member->id
            ])->count(1, $this->db) == 0){
                $this->db->createCommand()->insert($this->tableName, $value)->execute();
            }else{
                $this->db->createCommand()->update($this->tableName,$value, [
                    'sid'   =>  __SID__,
                    'item_id'   =>  $item_id,
                    'customer_id'   =>  Yii::$app->member->id
                ])->execute();
            }
            
        }
    }
    
    protected function clearDbItems()
    {
        if(!Yii::$app->member->isGuest){
            $this->db->createCommand()->delete($this->tableName, [
                'sid'   =>  __SID__,
                'customer_id'   =>  Yii::$app->member->id
            ])->execute();
        }
    }
    
    protected function removeDbItem($item_id)
    {
        if(!Yii::$app->member->isGuest){
            $this->db->createCommand()->delete($this->tableName, [
                'sid'   =>  __SID__,
                'item_id'   =>  $item_id,
                'customer_id'   =>  Yii::$app->member->id
            ])->execute();
        }
    }
    
    /**
     * import & set session
     */
    
    protected function setFromCookies()
    {
        $this->setSessionItems($this->getCookiesItems());
    }
    
    protected function setFromDb()
    {
        $this->setSessionItems($this->getDbItems());
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}