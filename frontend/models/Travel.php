<?php 
namespace izi\frontend\models;
use Yii;
use yii\db\Query;

class Travel extends Articles
{
     
    public static function tableGuestGroup()
    {
        return '{{%guest_groups}}';
    }
 
    public static function tableDepartureType()
    {
        return '{{%travel_departure_type}}';
    }
   
    public static function tableTravelPrices()
    {
        return '{{%travel_item_prices}}';
    }
    
    public function getItem($id, $params = [])
    {
        $query = static::find()
        ->from(['a'=>$this->tableName()])
        ->select(['a.*'])
        ->where(['a.id'=>$id]);
        
        $item = $this->populateData($query->asArray()->one());
        
        $info = Yii::$app->frontend->travel->getTravelInfo($id);
        
        return array_merge($item, $info);
         
        
    }
    
    public function getAllGusetGroups($params = [])
    {
        return static::find()->from(Travel::tableGuestGroup())->where(['type_id'=>0])->orderBy(['min_value'=>SORT_DESC])->asArray()->all();
    }
    
    public function getDefaultGuestGroup($params = [])
    {
        return static::find()->from(Travel::tableGuestGroup())->where(['type_id'=>0, 'is_default'=>1])->asArray()->one();
    }
    
    
    public function getGuestGroup($gid)
    {
    
        
        $query = static::find()->from(Travel::tableGuestGroup())->where(['type_id'=>0]);
        
        $query->andWhere(['id'=>$gid]);
         
        
        $l = $query->asArray()->one();
 
        
        return $l;
    }
    
    public function getGuestGroupsChildren($params = [])
    {
        
        if(isset($params['in'])){
            $g = $params['in'];
        }else{
            $g = Yii::$app->cfg->getUserConfig('USER_AGE_GROUPS');
        }
        
        $query = static::find()->from(Travel::tableGuestGroup())->where(['type_id'=>0, 'is_default'=>0]);
        
        if(is_array($g)){
            $query->andWhere(['id'=>$g]);
        }else{
            $query->andWhere(['is_system_default'=>1]);
        }
        
         
        
        $l = $query->orderBy(['min_value'=>SORT_DESC])->asArray()->all();
        
        if(isset($params['first_init']) && $params['first_init']){
            //
            $age_groups = [];
            
            foreach ($l as $v){
                $age_groups[] = $v['id'];
            }
            
            \app\modules\admin\models\Siteconfigs::updateBizrule(\izi\frontend\models\Articles::tableName(),  [
                'id'    =>  $params['item_id'],
                'sid'   =>  __SID__
            ], [
                'age_groups'  =>  $age_groups
            ]
                );
        }
        
        return $l;
    }
    
    
    public function getGuestGroups($params = [])
    {
        
        if(isset($params['in'])){
            $g = $params['in'];
        }else{        
            $g = Yii::$app->cfg->getUserConfig('USER_AGE_GROUPS');
        }
        
        $query = static::find()->from(Travel::tableGuestGroup())->where(['type_id'=>0]); 
        
        if(is_array($g)){
            $query->andWhere(['id'=>$g]);
        }else{
            $query->andWhere(['is_system_default'=>1]);
        }
        
        $l = $query->orderBy(['min_value'=>SORT_DESC])->asArray()->all();
        
        if(isset($params['first_init']) && $params['first_init']){
            //
            $age_groups = [];
            
            foreach ($l as $v){
                $age_groups[] = $v['id'];
            }
            
            \app\modules\admin\models\Siteconfigs::updateBizrule(\izi\frontend\models\Articles::tableName(),  [
                'id'    =>  $params['item_id'],
                'sid'   =>  __SID__
            ], [
                'age_groups'  =>  $age_groups
            ]
                );
        }
        
        return $l;
    }
    
    
    public function getDepartureType($params = [])
    {
        return static::find()->from(Travel::tableDepartureType())->where(['is_active'=>1])->orderBy(['value'=>SORT_ASC])->asArray()->all();
    }
    
    
    public function updatePrice($price, $condition)
    {
        
        if(isset($condition['price_type'])){
            $price_type = $condition['price_type'];
            unset($condition['price_type']);
        }else{
            $price_type = 0;
        }
        
        $item = TravelItemPrices::find()->where($condition)->one();        
        
        if(empty($item)){
            $item = new TravelItemPrices();
            
            foreach ($condition as $key=>$val){
                $item->{$key} = $val;
            }
            
            if(!isset($condition['departure_date'])){
                $item->departure_date = "0000-00-00";
            }
            
            if(!isset($condition['day_id'])){
                $item->day_id = 0;
            }
                                                 
        }
        
        $item->price_type = $price_type; 
                        
        $item->price = $price;
                
        return $item->save();
    }
    
    
    public function getPrices($item_id)
    {
        return TravelItemPrices::find()->where(['item_id'=>$item_id])->all();
        
         
    }
    
    
    public function getDefaultPrice($item_id, $params = [])
    {
        $item = $this->getItem($item_id);
        
        if(!isset($params['quotation_id'])){
            $params['quotation_id'] = 0;
        }
        
        if(!isset($params['package_id'])){
            $params['package_id'] = 0;
        }
        
        if(!isset($params['nationality_id'])){
            $params['nationality_id'] = 0;
        }
        
        if(!isset($params['group_id'])){
            $params['group_id'] = 0;
        }
        
        if(!isset($params['price_type_id'])){
            $params['price_type_id'] = 0;
        }
        
        if(!isset($params['type_id'])){
            $params['type_id'] = 1;
        }
        
        $params['item_id'] = $item_id;
        
        $params['departure_type_id'] = $item['departure_type'];         
        
        $itemPrice = TravelItemPrices::find()->where($params)->one();
        
        if(!empty($itemPrice))
            return $itemPrice->price;
    }
    
    public function getPrice($params)
    {
        
        if(!isset($params['quotation_id'])){
            $params['quotation_id'] = 0;
        }
        
        if(!isset($params['package_id'])){
            $params['package_id'] = 0;
        }
        
        if(!isset($params['nationality_id'])){
            $params['nationality_id'] = 0;
        }
        
        if(!isset($params['group_id'])){
            $params['group_id'] = 0;
        }
        
        if(!isset($params['price_type_id'])){
            $params['price_type_id'] = 0;
        }
        
        $itemPrice = TravelItemPrices::find()->where($params)->one();
        
        if(!empty($itemPrice))
            return $itemPrice->price;
        
             
    }
    
    
}

