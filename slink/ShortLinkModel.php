<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\slink;
use Yii;
class ShortLinkModel extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%short_links}}';
    }
    
    public function getId($length){
        $id = randString($length);
        $c = 0;
        while((new \yii\db\Query())->from(ShortLinkModel::tableName())->where(['id'=>$id])->count(1) > 0){ 
            $id = randString($length);
            if($c++ > 10){
                $length++;
            }            
        }
        
        return $id;
    }
    
    public function findUrlByChecksum($checksum){
        return (new \yii\db\Query())->from(ShortLinkModel::tableName())->where(['checksum'=>$checksum])->one();
    }
    
    public function validateId($id){
        if((new \yii\db\Query())->from(ShortLinkModel::tableName())->where(['id'=>$id])->count(1) > 0){
            return false;
        }
        return true;
    }
    
    public function updateUrl($id, $params){
        $data = [
            //'original'=>$params['original'],
            //'title'=>$params['title'],
            //'description'=>$params['description'],
            //'sid'=>__SID__,
            'time'=>time(),
            //'id'=>isset($params['id']) ? $params['id'] : $this->getId(6),
        ];
        $st = true;
        
        if(isset($params['original'])){
            $data['original'] = $params['original'] ;
            $checksum = md5($params['original']);
        }
        
        if(isset($params['description'])){
            $data['description'] = $params['description'] ;
        }
        
        if(isset($params['title'])){
            $data['title'] = $params['title'] ;
        }
        
        if(isset($params['id']) && $id != $params['id']){
            $data['id'] = $params['id'] ;
            $st = $this->validateId($params['id']);
        }
        
        if(!$this->validateId($id) && $st){
            Yii::$app->db->createCommand()->update(ShortLinkModel::tableName(),$data,['id'=>$id])->execute();
            return $id;
        }
        return false;
    }
    
    public function createUrl($params){
        //
        if(!(isset($params['title']) && $params['title'] != "")){
            $params['title'] = $this->getPageTitle($params['original']);
        }
        //
        $data = [
            'checksum'=>md5($params['original']),
            'original'=>$params['original'],
            'title'=>$params['title'],
            'description'=>isset($params['description']) ? $params['description'] : '',
            'sid'=>__SID__,
            'time'=>time(),
            'id'=>isset($params['id']) ? $params['id'] : $this->getId(6),
        ];
        if($this->validateId($data['id'])){
            Yii::$app->db->createCommand()->insert(ShortLinkModel::tableName(),$data)->execute(); 
            return $data['id'];
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