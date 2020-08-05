<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\ads\models;
use Yii;
class Ads extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%ads}}';
    }
         
    
    public function getId($length = 6){
        $id = randString($length);
        $c = 0;
        while((new \yii\db\Query())->from(Ads::tableName())->where(['id'=>$id])->count(1) > 0){
            $id = randString($length);
            if($c++ > 10){
                $length++;
            }
        }
        
        return $id;
    }
    
    public function findUrlByChecksum($checksum){
        return (new \yii\db\Query())->from(Ads::tableName())->where(['checksum'=>$checksum])->one();
    }
    
    public function getAdsById($id){
        
        return static::findOne(['id'=>$id, 'sid'=>__SID__]);
    }
    
    public function validateId($id){
        if((new \yii\db\Query())->from(Ads::tableName())->where(['id'=>$id])->count(1) > 0){
            return false;
        }
        return true;
    }
    
    public function updateAds($id, $params){
        $data = [
            //'original'=>$params['original'],
            //'title'=>$params['title'],
            //'description'=>$params['description'],
            //'sid'=>__SID__,
            //'time'=>time(),
            //'id'=>isset($params['id']) ? $params['id'] : $this->getId(6),
        ];
        $st = true;
        
        if(isset($params['text'])){
            $data['text'] = $params['text'] ;
            $params['checksum'] = md5($params['text']);
        }
        
        if(isset($params['started_date'])){
            $data['started_date'] = $params['started_date'] ;
        }
        if(isset($params['expired_date'])){
            $data['expired_date'] = $params['expired_date'] ;
        }
        
        if(isset($params['title'])){
            $data['title'] = $params['title'] ;
        }
        
        if(isset($params['id']) && $id != $params['id']){
            $data['id'] = $params['id'] ;
            $st = $this->validateId($params['id']);
        }
        
        if(!$this->validateId($id) && $st){
            Yii::$app->db->createCommand()->update(Ads::tableName(),$data,['id'=>$id])->execute();
            return $id;
        }
        return false;
    }
    
    public function createAds($params){
        
        //
        $data = [
            'text'=>($params['text']),
            'checksum'=>md5($params['text']),
            'started_date'=>isset($params['started_date']) ? $params['started_date'] : time(),
            'expired_date'=>isset($params['expired_date']) ? $params['expired_date'] : time() + (3650 * 86400),
            'title'=>$params['title'],
            'sid'=>__SID__,
            'time'=>time(),
            'created_by'=>Yii::$app->user->id,
            'id'=>isset($params['id']) ? $params['id'] : $this->getId(6),
        ];
        if($this->validateId($data['id'])){
            Yii::$app->db->createCommand()->insert(Ads::tableName(),$data)->execute();
            return $data['id'];
        }
        return false;
        
    }
    
}