<?php
namespace izi\sim;

use Yii;

class Sale extends \yii\base\Component
{
   public $sim;
   
   private $_cache;
    /**
     * init
     */
   
    public function getRoundValue($price, $round = 10000)
    {
        if($price < 1000000){
            
        }elseif($price < 5000000){
            $round = max($round, 50000);
        }elseif($price < 1000000){
            $round = max($round, 100000);
        }elseif($price < 3000000){
            $round = max($round, 100000);
        }elseif($price < 100000000){
            $round = max($round, 100000);
        }else{
            $round = max($round, 1000000);
        }
        
        return $round;
    }
   
    
    /**
     * Tính giá bán lẻ / giá đại lý / giá ctv theo nhóm (đã chọn)
     * Trường hợp đại lý nằm trong nhiều nhóm, giá sẽ tính theo nhóm có mức ưu tiên cao nhất.
     * @input: array
     * [
     *  price: giá đầu vào (giá thu),
     *  group_id: int | array - nhóm khách 
     *  quotation_id: int (nhập báo giá nếu đã có mã)
     *  info: array - thông tin chi tiết số sim.
     *  type_id: int - loại khách (nếu có)
     *  agent_id: int - id đại lý (nếu có)
     *  agent: array - thông tin chi tiết đại lý (nếu có)
     * ]
     * 
     * @output: int price.
     */
    public function getSellPrice($params)
    {
        $price = isset($params['price']) ? $params['price'] : 0;
        $info = isset($params['info']) ? $params['info'] : [];
        
        if($price == 0 && ($price = isset($info['price']) ? $info['price'] : 0) == 0){
            return 0;
        }
        
        // group (nhóm đại lý)
        $group_id = isset($params['group_id']) ? $params['group_id'] : 0;
        
        if(is_array($group_id) && !empty($group_id)){
            $group_id = $group_id[0];
        }
        
        // Làm tròn kết quả (mặc định sẽ làm tròn lên 1000đ)
        $round = $this->getRoundValue($price,  isset($params['round']) && $params['round']>0 ? $params['round'] : 10000);
        
        
        // Agent 
        
        $agent_id = isset($params['agent_id']) ? $params['agent_id'] :
        (isset($params['agent']['id']) ? $params['agent']['id'] : 0);
        
        $quotation = $this->getQuotationByAgent($agent_id, $group_id);
        
        if(!empty($quotation)){
            $conditions = $this->getConditionsByPrice($price, $quotation['id']);
            if(!empty($conditions)){
                $profitPrice = $this->getProfitPrice($price, $conditions, $params);
                
                $sellPrice = $price + $profitPrice;
                
                $p1 = (int) ($sellPrice / $round);
                
                if($p1 * $round < $sellPrice){
                    $sellPrice = ($p1 * $round) + $round;
                }
                
                return $sellPrice;
                
            }else{
                return 0;
            }             
        }
        
        return $price;
    }
    
    
    /**
     * Lấy giá trị chênh lệch theo điều kiện cho trước
     */
    public function getProfitPrice($price, $conditions, $params = [])
    {
        $profit_value = 0;
        
        $profit_price = 0;
        
        $con = [];
        
        $info = isset($params['info']) ? $params['info'] : (isset($params['simInfo']) ? $params['simInfo'] :
            (isset($params['simId']) ? Yii::$app->sim->getSimInfo($params['simId']) : [])
            ) ;
        
        /**
         * Công thức tính giá ngược
         * VD:
         * Ta có giá gốc  = 300k && ck = 30%
         * Nếu tính giá thuận => giá mới = 300k * 1.3
         * Nếu tính giá ngược ta phải tìm giá mới sau khi chiết khấu 30% = 300k
         * @var Ambiguous $reverse
         */
        $reverse = isset($params['reverse']) && $params['reverse'] === true ? true : false;
        
        if(!empty($conditions)){
            foreach ($conditions as $c){
                
                $profit_value = $c['profit_value'];
                
                
                
                //
                if(count($conditions) > 1 && !empty($info)){
                    
                    $con = $c; break;
                    
                    
                    $st = false;
                    
                    if($c['condition1'] != ""){
                        $c1 = json_decode($c['condition1'],1);
                        if(isset($c1['category_id']) && (in_array($info['category_id'], $c1) || in_array($info['category3_id'], $c1)) ){
                            $con = $c;
                            $st = true;
                        }else{
                            $st = false;
                        }
                    }
                    
                    if($c['condition2'] != ""){
                        $c1 = json_decode($c['condition2'],1);
                        if(isset($c1['category2_id']) && in_array($info['category2_id'], $c1)){
                            $con = $c;
                            $st = true;
                        }else{
                            $st = false;
                        }
                    }
                    
                    if($st ){
                        $con = $c; break;
                    }else{
                        $con = $conditions[0];
                    }
                    
                }else{
                    $con = $c;
                }
            }
        
        
        if($reverse){
            $profit_price = $price / (1 - $profit_value/100) - $price;
        }else{
            $profit_price = $price * $profit_value/100;
        }
        
        $profit_price = max($con['min_value'], $profit_price);
        
        if($con['max_value'] > 0){
            $profit_price = min($con['max_value'], $profit_price);
        }
        
        }
        return $profit_price;
        
    }
    
    /**
     * Lấy toàn bộ các khoảng giá thỏa mãn điều kiện của báo giá
     * 
     */
    public function getConditionsByPrice($price, $quotation_id)
    {
        return $this->sim->quotation->model->getConditionsByQuotation($quotation_id, [
            'price' => $price
        ]);
    }
    
    
    
    /**
     * Lấy báo giá hiện tại của 1 đại lý
     */
    
    public function getQuotationByAgent($agentId = 0, $group_id = 0)
    {
        $k1 = md5(__METHOD__);
        $k2 = md5(json_encode([$agentId, $group_id]));
        
        if(isset($this->_cache[$k1][$k2])){
            return $this->_cache[$k1][$k2];
        }
        
        if($agentId == 0){
            $agentId = Yii::$app->member->id;
        }
        
        if(!($group_id>0)){
            $group_id = Yii::$app->customer->model->getSingleGroupIdByCustomer($agentId);
             
        }        
        
        return ($this->_cache[$k1][$k2] = $this->sim->quotation->model->getQuotationByGroup($group_id));
    }
    
    
    /**
     * Tính giá đại lý từ giá bán lẻ dựa vào báo giá của đại lý
     */
    
    public function getAgentPriceFromSellPrice($sellPrice, $params)
    {
        /**
         * Lấy báo giá
         */
        
        $k1 = md5(__METHOD__);
        
        $quotation_id = isset($params['quotation_id']) ? $params['quotation_id'] : 
        (isset($this->_cache[$k1]['quotation_id']) ? $this->_cache[$k1]['quotation_id'] : 0);
        
        if(!($quotation_id>0)){
            
            $partner_id = isset($params['partner_id']) ? $params['partner_id'] : 0;
            
            $quotation = Yii::$app->sim->sale->getQuotationByAgent($partner_id);
            if(!empty($quotation)){
                $quotation_id = $this->_cache[$k1]['quotation_id'] = $quotation['id'];
            }
        }
        
        
        if($quotation_id > 0){
            $conditions = Yii::$app->sim->quotation->model->getConditionsByQuotation($quotation_id, [
                'price' => $sellPrice
            ]);
            
        }else{
            
            if(!isset($params['group_name'])){
                $params['group_name'] = "partner_" . $params['partner_id'];
            }
            
            $group_name = $params['group_name'];
            
            $conditions = Yii::$app->sim->getDiscountConditions($group_name, [
                'price' => $sellPrice
            ]);
        }
        
       
        
        $profit_value = 0;
        
        if(!empty($conditions)){
            foreach ($conditions as $c){
                
                $profit_value = $c['profit_value'];
            }
        }
        
        $agentPrice = $sellPrice * (1 - $profit_value/100);
        
        $round = isset($params['round']) ? $params['round'] : 10000;
        
        $p1 = (int) ($agentPrice / $round);
        
        if($p1 * $round < $agentPrice){
            $agentPrice = ($p1 * $round) + $round;
        }
        
        return $agentPrice;
        
    }
    
    
    
    /**
     * Tính giá bán lẻ từ giá gốc, theo báo giá của đơn vị chỉ định
     */
    public function getSellPriceFromAgentPrice($agentPrice, $params = [])
    {
        
        if($agentPrice == 0){
            return 0;
        }
        
        /**
         * Công thức tính giá ngược
         * VD:
         * Ta có giá gốc  = 300k && ck = 30%
         * Nếu tính giá thuận => giá mới = 300k * 1.3
         * Nếu tính giá ngược ta phải tìm giá mới sau khi chiết khấu 30% = 300k
         * @var Ambiguous $reverse
         */
        $reverse = isset($params['reverse']) && $params['reverse'] === true ? true : false;
        
        // Làm tròn kết quả (mặc định sẽ làm tròn lên 1000đ)
//         $round = isset($params['round']) && $params['round']>0 ? $params['round'] : 10000;
        $round = $this->getRoundValue($agentPrice,  isset($params['round']) && $params['round']>0 ? $params['round'] : 10000);
        
        $conditions = [];
        
        if(isset($params['quotation_id']) && $params['quotation_id'] > 0){
            
            $conditions = $this->sim->quotation->model->getConditionsByQuotation($params['quotation_id'], [
                'price' => $agentPrice
            ]);
            
            
        }elseif(isset($params['quotation_code']) && $params['quotation_code'] != ""){
            $conditions = $this->sim->quotation->model->getConditionsByQuotationCode($params['quotation_code'], [
                'price' => $agentPrice
            ]);
            
        }else{
            $agent_id = isset($params['agent_id']) ? $params['agent_id'] :
            (isset($params['agent']['id']) ? $params['agent']['id'] : 0);
            
            // group (nhóm đại lý)
            $group_id = isset($params['group_id']) ? $params['group_id'] : 0;
             
            
            $quotation = $this->getQuotationByAgent($agent_id, $group_id);
             
            
            if(!empty($quotation)){
                $conditions = $this->getConditionsByPrice($agentPrice, $quotation['id']);                 
            }
        }                
         
        
        if(empty($conditions) && isset($params['partner_id'])){
            
            if(!isset($params['group_name'])){
                $params['group_name'] = "partner_" . $params['partner_id'];
            }
            
            $group_name = $params['group_name'];
            
            $conditions = $this->sim->getDiscountConditions($group_name, [
                'price' => $agentPrice
            ]);                        
            
            if(empty($conditions)){
                return 0;
            }
            
        }
        
        $info = isset($params['info']) ? $params['info'] : (isset($params['simInfo']) ? $params['simInfo'] : 
            (isset($params['simId']) ? Yii::$app->sim->getSimInfo($params['simId']) : [])
        ) ;
        
        $params['info'] = $info;
        
        $profit_price = $this->getProfitPrice($agentPrice, $conditions,$params);

        $sellPrice = $agentPrice + $profit_price;

        $p1 = (int) ($sellPrice / $round);
        
        if($p1 * $round < $sellPrice){
            $sellPrice = ($p1 * $round) + $round;
        }
        
        return $sellPrice;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
