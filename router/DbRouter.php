<?php

namespace izi\router;

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
        
        $config = [];// Yii::$app->icache->getCache($params);
        
        if(!YII_DEBUG && !empty($config)){
            return $config;
        }else{
            $config = static::find()
            ->select(['a.sid','a.is_invisible','b.status','b.code','a.is_admin','a.module','b.to_date','a.state','b.layout','b.api_token'])
            ->from(['a'=>'{{%domain_pointer}}'])
            ->innerJoin(['b'=>'{{%shops}}'],'a.sid=b.id')
            ->where(['a.domain'=>__DOMAIN__])->asArray()->one();
            //Yii::$app->icache->store($config, $params);
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
}
