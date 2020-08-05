<?php

namespace izi\router;

use Yii;

class DbRouter extends \yii\db\ActiveRecord
{
    
    public static function tableName(){
        return '{{%slugs}}';
    }
    
    /**
     * DB init
     */
    public static function getDomainInfo($domain = __DOMAIN__){
        
        $params = [
            __CLASS__,
            __FUNCTION__,
            $domain,
            date('H')
        ];
        
        $config = Yii::$app->icache->getCache($params);
        
        if(!YII_DEBUG && !empty($config)){
            return $config;
        }else{
            $config = static::find()
            ->select(['a.sid','a.is_invisible','b.status','b.code','a.is_admin','a.module','a.layout','b.to_date','a.state','b.layout','b.api_token'])
            ->from(['a'=>'{{%domain_pointer}}'])
            ->innerJoin(['b'=>'{{%shops}}'],'a.sid=b.id')
            ->where(['a.domain'=>__DOMAIN__])->asArray()->one();
            Yii::$app->icache->store($config, $params);
            return $config;
        }
    }
    
    
    public static function findUrl($url = ''){
        $v = static::find()->where(['url'=>$url,'sid'=>__SID__])->asArray()->one();
        
        if(isset($v['bizrule']) && ($content = json_decode($v['bizrule'], 1)) != false){
            $v += $content;
            unset($v['bizrule']);
        }
        
        return $v;
    }
    
    
    
    public function getCategoryDetail($item_id){
        
        $item = static::find()
        ->from('{{%site_menu}}')
        ->where([
            "id" => $item_id ,
            'is_active'=>1 ,
            'sid'=>__SID__
            
        ])->asArray()->one();
        
        if(!empty($item)) {
            if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                $item += $content;
                unset($item['bizrule']);
            }
            return $item;
        }
    }
    
    public function getRootCategoryDetail($item = []){
        if(is_numeric($item)){
            $item = $this->getCategoryDetail($item);
        }
        
        if(!empty($item)){
            
            if(isset($item['parent_id']) && $item['parent_id'] == 0){
                return $item;
            }else{
                
                $item = static::find()
                ->from('{{%site_menu}}')
                ->where(['and',[
                    "parent_id" => 0,
                    'is_active'=>1 ,
                    'sid'=>__SID__
                ],
                    ['<', 'lft', $item['lft']],
                    ['>', 'rgt', $item['rgt']],
                ])->asArray()->one();
                                 
                if(!empty($item)) {                    
                    if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                        $item += $content;
                        unset($item['bizrule']);
                    }
                    return $item;
                }
            }
            
        }
    }
    
    
    
    public function getItemDetail($item_id){
        
        $item = static::find()
        ->from('{{%articles}}')
        ->where([
            "id" => $item_id ,
            'is_active'=>1 ,
            'sid'=>__SID__
            
        ])->asArray()->one();
        
        
        if(!empty($item)) {
            
            if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                $item += $content;
                unset($item['bizrule']);
            }
            return $item;
        }
    }
    
    
    public function getItemCategory($item_id){

        
        $item = static::find()
        ->from(['a'=>'{{%site_menu}}'])
        ->innerJoin(['b'=>'{{%items_to_category}}'],'a.id=b.category_id' )
        ->where([
            "b.item_id" => $item_id             
        ])->asArray()->one();
        
        if(!empty($item)){
            if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                $item += $content;
                unset($item['bizrule']);
            }
            
            return $item;
        }
        
    }
    
    
    public function getBoxDetail($item_id){
        
        $item = static::find()
        ->from('{{%box}}')
        ->where([
            "id" => $item_id ,
            'is_active'=>1 ,
            'sid'=>__SID__
            
        ])->asArray()->one();
        
        
        if(!empty($item)) {
            
            if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                $item += $content;
                unset($item['bizrule']);
            }
            return $item;
        }
    }
    
    
    public function getTemplate(){
        
        $item = [];
        
        $params = [
            __METHOD__,
            __FILE__
        ];
        
        $cached = Yii::$app->icache->getCache($params);
        
        if(!YII_DEBUG && !empty($cached)){
            return $cached;
        }
        
        if(defined('CATEGORY_TEMPLATE') && CATEGORY_TEMPLATE>0){
            $item = DbRouter::findOne(["id" => CATEGORY_TEMPLATE]);
            if(!empty($item)) {
                $item = $item->toArray();
            }
        }
        
        if(empty($item)){
            
            $item = DbRouter::find()
            ->select(['a.*'])
            ->from(['a' => '{{%templates}}'])
            ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
            ->where(
                [
                    'b.state'=>__TEMPLATE_DOMAIN_STATUS__,
                    'b.sid'=>__SID__,
                    'b.lang'=>__LANG__,
                ])
                ->asArray()
                ->one();
                
                
                
                if(empty($item)){
                    
                    $item = DbRouter::find()
                    ->select(['a.*'])
                    ->from(['a' => '{{%templates}}'])
                    ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
                    ->where(
                        [
                            'b.state'=>__TEMPLATE_DOMAIN_STATUS__,
                            'b.sid'=>__SID__,
                            //'b.lang'=>__LANG__,
                        ])
                        ->asArray()
                        ->one();
                        
                        
                        if(empty($item) && __TEMPLATE_DOMAIN_STATUS__ > 1){
                            
                            $item = DbRouter::find()
                            ->select(['a.*'])
                            ->from(['a' => '{{%templates}}'])
                            ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
                            ->where(
                                [
                                    'b.state'=>1,
                                    'b.sid'=>__SID__,
                                    'b.lang'=>__LANG__,
                                ])
                                ->asArray()
                                ->one();
                                
                                if(empty($item)){
                                    
                                    $item = DbRouter::find()
                                    ->select(['a.*'])
                                    ->from(['a' => '{{%templates}}'])
                                    ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
                                    ->where(
                                        [
                                            'b.state'=>1,
                                            'b.sid'=>__SID__,
                                            //'b.lang'=>__LANG__,
                                        ])
                                        ->asArray()
                                        ->one();
                                        
                                }
                        }
                }
                
                
        }
        
        Yii::$app->icache->store($item, $params);
        
        return $item;
    }
    
}
