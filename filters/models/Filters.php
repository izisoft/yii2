<?php 
namespace izi\filters\models;
use Yii;
use yii\db\Query;

class Filters extends \izi\db\ActiveRecord
{
    public $backend;
    
    
    public static function tableName()
    {
        return '{{%filters}}';
    }
    
    public function getItem($id){
        return static::find()->where(['id'=>$id])->asArray()->one();
    }
    
    public function getItemsByCode($code, $params = [])
    { 
        $query = static::find()->where([
            'code'  =>  $code,
            'sid'   =>  __SID__,
            
        ]);
        
        if(!(isset($params['lang']) && $params['lang'] == null)){
            $query->andWhere(['lang'=>__LANG__]);
        }
        
        if(isset($params['parent_id']) && $params['parent_id'] > -1){
            $query->andWhere(['parent_id'=>$params['parent_id']]);
        }
        
        if(!(isset($params['is_active']) && $params['is_active'] == -1)){
            $query->andWhere(['is_active'=>1]);
        }
        
//         view($query->createCommand()->getRawSql());
        
        return $query->asArray()->all();
    }
    
    
    
    public function getFilters($o = array()){
        $parent_id = isset($o['parent_id']) ? $o['parent_id'] : -1;
        $id = isset($o['id']) ? $o['id'] : false;
        $filter_value = isset($o['filter_value']) ? $o['filter_value'] : -1;
        $queryMethod = isset($o['query']) && $o['query'] == 'one' ? $o['query'] : 'all';
        $code = isset($o['code']) ? $o['code'] : false;
        $ncode = isset($o['!code']) ? $o['!code'] : array();
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $position = isset($o['position']) ? $o['position'] : false;
        $select = isset($o['select']) ? $o['select'] : 'a.*';
        $is_destination = isset($o['is_destination']) ? $o['is_destination'] : -1;
        $query = new Query();
        $query->select($select)->from(['a'=>Filters::tableName()]);
        //$sql = "SELECT $select FROM  `filters` as a ";
        if($position !== false){
            $query->innerJoin(['b'=>Filters::tableToPosition()],"a.id=b.item_id  and b.position_id='$position' and b.type=2");
        }
        $query->where(['>','a.state',-2]);
        $query->andWhere(['a.is_active'=>1,'a.lang'=>__LANG__,'a.sid'=>__SID__]);
        if($parent_id>-1){
            $query->andWhere(['a.parent_id'=>$parent_id]);
        }
        if($code !== false){
            $query->andWhere(['a.code'=>$code]);
        }
        if($item_id > 0){
            $query->andWhere(['in','a.id',(new Query())->select('filter_id')->from('{{%articles_to_filters}}')->where(['item_id'=>$item_id])]);    		//
        }
        if(is_array($ncode) && !empty($ncode)){
            $query->andWhere(['not in','a.code',implode(',', $ncode)]);
        }
        if($filter_value>-1){
            $query->andWhere(['a.value'=>$filter_value]);
        }
        if($id !== false){
            $query->andWhere(['in','a.id',(is_array($id) && !empty($id) ? $id : (int)$id )]);
        }
        $query->orderBy(['a.code'=>SORT_ASC,'a.position'=>SORT_ASC,'a.title'=>SORT_ASC]);
        
        //view($query->createCommand()->getRawSql());
        
        return $this->populateData($query->$queryMethod());
        //return Zii::$db->$query($sql);
    }
    
    
    
}

