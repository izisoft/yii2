<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\cronjob;
use Yii;
class CronjobModel extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%cronjobs}}';
    }
    /*
     * 
     */
    
    public function getTodayJob($params = []){
        $query = static::find()->where(['sid'=>[0,__SID__]]);
        $query->andWhere(['<','started_time',time()]);
        return $query->orderBy(['priority'=>SORT_DESC])->asArray()->all();
    }
    
    public function getNearestJob($params = []){
        $query = static::find()->where(['sid'=>[0,__SID__]]);
        $query->andWhere(['>','started_time',time()]);
        return $query->orderBy(['started_time'=>SORT_ASC])->asArray()->one();
    }
    
    public function getJobs ($params){
        $query = static::find()->where($params);
        //$query->andWhere(['<','started_time',time()]);
        return $query->orderBy(['started_time'=>SORT_ASC,'priority'=>SORT_DESC])->asArray()->all();
    }
    
    
    
    
    
    public function getId($length){
        $id = randString($length);
        $c = 0;
        while((new \yii\db\Query())->from(CronjobModel::tableName())->where(['id'=>$id])->count(1) > 0){ 
            $id = randString($length);
            if($c++ > 10){
                $length++;
            }            
        }
        
        return $id;
    }
    
    public function findUrlByChecksum($checksum){
        return (new \yii\db\Query())->from(CronjobModel::tableName())->where(['checksum'=>$checksum])->one();
    }
    
    public function validateId($id){
        if((new \yii\db\Query())->from(CronjobModel::tableName())->where(['id'=>$id])->count(1) > 0){
            return false;
        }
        return true;
    }
    
    public function updateJob($id, $params){
        $data = [
            //'original'=>$params['original'],
            //'title'=>$params['title'],
            //'description'=>$params['description'],
            //'sid'=>__SID__,
            'last_modify'=>date('Y-m-d H:i:s'),
            //'id'=>isset($params['id']) ? $params['id'] : $this->getId(6),
        ];
        $st = true;
        
        if(isset($params['type_code'])){
            $data['type_code'] = $params['type_code'] ;
            //$checksum = md5($params['type_code']);
        }
        
        if(isset($params['item_id'])){
            $data['item_id'] = $params['item_id'] ;
        }
        
        if(isset($params['started_time'])){
            $data['started_time'] = $params['started_time'] ;
        }
        
        if(isset($params['state'])){
            $data['state'] = $params['state'] ;
        }
        if(isset($params['priority'])){
            $data['priority'] = $params['priority'] ;
        }
        if(isset($params['bizrule'])){
            $data['bizrule'] = $params['bizrule'] ;
            //$data['checksum']
        }
        
        if(isset($params['checksum'])){
            $data['checksum'] = $params['checksum'] ;
            //$data['checksum']
        }
        
        
        
        
        if(isset($params['id']) && $id != $params['id']){
            $data['id'] = $params['id'] ;
            $st = $this->validateId($params['id']);
        }
        
        if(!$this->validateId($id) && $st){
            Yii::$app->db->createCommand()->update(CronjobModel::tableName(),$data,['id'=>$id])->execute();
            return $id;
        }
        return false;
    }
    
    public function createJob($params){
        
        $data = [
            'type_code'=>($params['type_code']),
            'item_id'=>$params['item_id'],
            'started_time'=>$params['started_time'],
            'bizrule'=>isset($params['bizrule']) ? $params['bizrule'] : '',
            'sid'=>__SID__,
            'checksum'=>isset($params['checksum']) ? $params['checksum'] : md5(isset($params['bizrule']) ? $params['bizrule'] : ''),
            'id'=>isset($params['id']) ? $params['id'] : $this->getId(6),
            'is_locked' => 0
        ];
        if($this->validateId($data['id'])){
            Yii::$app->db->createCommand()->insert(CronjobModel::tableName(),$data)->execute(); 
            return $data['id'];
        }elseif(isset($params['update']) && $params['update']){
            return $this->updateJob($data['id'], $data);
        }
        return false;
        
    }
    
    public function getPageTitle($url) {
        
        $page = file_get_contents($url);
        
        if (!$page) return null;
        
        $matches = array();
        
        if (preg_match('/<title>(.*?)<\/title>/', $page, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }
}