<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\local\models;
use Yii;
class Local extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%local}}';
    }
    
    public function findCountryByIso2($iso2){
        $query = static::find()->where(['code'=>$iso2]);
         
        return $query->one();
    }
    
    public function findCityByName($cityName, $country_id){
        $query = static::find()->where(['parent_id'=>$country_id]);
        $query->andFilterWhere(['international_title'=>$cityName]);
        $v = $query->one();
        if(!empty($v)){
            return $v;
        }
        
        $query = static::find()->where(['parent_id'=>$country_id]);
        $query->andFilterWhere(['title'=>$cityName]);
        return $query->one();
    }
    
     
    public function getAll($params = []){
        $query = static::find()->where(1);
        
        if(isset($params['parent_id'])){
            $query->andWhere(['parent_id'=>$params['parent_id']]);
        }
        
        return $query->orderBy(['title'=>SORT_ASC])->asArray()->all();
    }
    
    public function getItem($id){
        return static::find()->where(['id'=>$id])->asArray()->one();
    }
    
    
    public function getLocal($id){
        return static::find()->where(['id'=>$id])->one();
    }
    
    public function parseLocal($id = 0, $default = 0){
        
        if($id < 1) $id = $default;
        
        $query= (new \yii\db\Query())->select(['id','lft','rgt','title','level','type_id','lang_code'])
        ->from(Local::tableName());
        if($id>0){
            $query->where(['id'=>$id]);
        }else {
            return false;
            $query->where(['is_default'=>1,'parent_id'=>0]);
        }
        $item = $query->one();
        
        if(!empty($item)){
            $r = (new \yii\db\Query())->select(['id','lft','rgt','title','level','type_id','lang_code'])
            ->from(Local::tableName())->where([
                'and',
                ['<','lft',$item['lft']],
                ['>','rgt',$item['rgt']]
            ])->orderBy(['lft'=>SORT_ASC])->all();
            if(!empty($r)){
                $r[] = $item;
            }else{
                $r[0] = $item;
            }
            return [
                'country'=>$r[0],
                'province'=>isset($r[1]) ? $r['1'] : ['id'=>'-1','title'=>'-','type_id'=>0],
                'district'=>isset($r[2]) ? $r['2'] : ['id'=>'-1','title'=>'-','type_id'=>0],
                'ward'=>isset($r[3]) ? $r['3'] : ['id'=>'-1','title'=>'-','type_id'=>0],
            ];
        }
        return false;
    }
    
    
    
    public function parseLocal2($id , $default = 0){
        
        $locals = $this->parseLocal($id, $default);
        
        if(!empty($locals)){
            $locals = (array_reverse($locals) );
            foreach ($locals as $k=> $v){
                if(!($v['id']>0)){
                    unset($locals[$k]);
                }
            }
        }
         
        return $locals;
    }
    
    
    public function countChildSingleLocal($local_id){
        return static::find()->where(['parent_id'=>$local_id])->count(1);
    }
    
    public function countChildRecursive($local_id, $child = 0){
        $l = static::find()->select('id')->where(['parent_id'=>$local_id])->asArray()->all();
        if(!empty($l)){
            $child += count($l);
            foreach ($l as $v){
                $child = $this->countChildRecursive($v['id'], $child);
            }
        }
        
        return $child;
    }
    
    
    public function updateCountChild($local_id, $mode = 1){
        if($mode == 1){
            Yii::$app->db->createCommand()->update(Local::tableName(), ['count_child'=>$this->countChildRecursive($local_id)],['id'=>$local_id])->execute();
        }else{
            $l = static::find()->select('id')->where(['parent_id'=>$local_id])->asArray()->all();
            if(!empty($l)){                 
                foreach ($l as $v){
                    Yii::$app->db->createCommand()->update(Local::tableName(), ['count_child'=>$this->countChildRecursive($v['id'])],['id'=>$v['id']])->execute();
                    $this->updateCountChild($v['id'], $mode);
                }
            }else{
                Yii::$app->db->createCommand()->update(Local::tableName(), ['count_child'=>$this->countChildRecursive($local_id)],['id'=>$local_id])->execute();
            }
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}