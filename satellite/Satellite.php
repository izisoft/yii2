<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\satellite;
use Yii;
use izi\models\StoreWebsite;
use izi\models\CatalogPostWebsite;
class Satellite extends \yii\base\Component
{
	
    
    public function getItem($item_id)
    {
        $item = SatelliteWebModel::findOne($item_id);
  
        return $item;
    }
    
    
    public function getListWebsite($param = [])
    {
        $limit = isset($param['limit'])? $param['limit'] : 30;
        
        $count = isset($param['count']) && $param['count'] ? true : false;
        
        $p = max(1, isset($param['p'])? $param['p'] : getParam('p', 1));
        
        $offset = ($p - 1) * $limit;
        
        $query = SatelliteWebModel::find()->where(['sid' => __SID__]);
        
        if($count){
            $total_records = $query->count(1);
        }else{
            return $query->limit($limit)->offset($offset)->all();
        }
        
        $query->limit($limit)->offset($offset);
        
        $total_pages = ceil($total_records/ $limit);
        
        
        
        return [
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'limit' => $limit,
            'p' => $p,
            'list_items' => $query->all(),
            
        ];
    }
    
    
    
    public function validateDomain($domain, $id = 0){
        $query = SatelliteWebModel::find()->where(['and', ['domain' => $domain], ['not in' ,'id', $id]]);
        
        if($query->count(1) > 0){
            return false;
        }
        
        return true;
    }
    
    
    public function validateAccessToken($token, $id = 0){
        $query = SatelliteWebModel::find()->where(['and', ['access_token' => $token], ['not in' ,'id', $id]]);
        
        if($query->count(1) > 0){
            return false;
        }
        
        return true;
    }
    
    
    public function generateToken($token = '', $id = 0)
    {
        if($token == '')
            $token = Yii::$app->security->generateRandomString();
        
        while(!$this->validateAccessToken($token, $id)){
            $token = Yii::$app->security->generateRandomString();
        }
        
        return $token;
    }
    
    
	 	 
    
    public function createWebsite($param){
        $store_website = isset($param['store_website']) ? $param['store_website'] : [];
        
        unset($param['store_website']);
        
        if(!$this->validateDomain($param['domain'])) return false;
        
        $website = Yii::$app->store->createWebsite($store_website['code'], $store_website['name'], $store_website['type_id']);
        
        if(!empty($website)){
            $param['website_id'] = $website->website_id;
            
            $website = new SatelliteWebModel();
            
            if(!isset($param['sid'])) $param['sid'] = __SID__;
            
            foreach ($param as $k => $v){
                $website->$k = $v;
            }
            
            if($website->save()){
                return $website; 
            }
        }
        
        
    }
    
    
    public function checkPostExisted($post_id, $website_id){
        $w = CatalogPostWebsite::findOne(['post_id' => $post_id, 'website_id' => $website_id]);
        
        if(!empty($w)) return true;
        return false;
    }
    
    public function assignPost($post_id, $sweb)
    {
        $deleteCondition = [
            'and', ['post_id' => $post_id],
            ['not in', 'website_id', $sweb],
            ['website_id' => (new \yii\db\Query())->select('website_id')->from(StoreWebsite::tableName())->where(['sid' => __SID__, 'type_id' => 3])]
        ];
        
        CatalogPostWebsite::deleteAll($deleteCondition);
        
        if(!empty($sweb)){
            foreach ($sweb as $website_id){
                $w = CatalogPostWebsite::findOne(['post_id' => $post_id, 'website_id' => $website_id]);
                if(empty($w)){
                    $w = new CatalogPostWebsite();
                    $w->post_id = $post_id;
                    $w->website_id = $website_id;
                    
                    $w->save();
                    
                    return $w;
                }else{
                    $w->is_active = 1;
                    $w->save();
                }
            }
        }
        
    }
    
}