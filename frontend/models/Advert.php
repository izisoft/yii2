<?php 
namespace izi\frontend\models;

use Yii;
use yii\db\Query;

class Advert extends \izi\db\ActiveRecord
{
    
    public static function tableName(){
        return '{{%adverts}}';
    }
    
    
    public function getItems($params = []){
        $type = isset($params['type']) && is_numeric($params['type']) ? $params['type'] : -1;
        
        $category_id = isset($params['category_id']) && is_numeric($params['category_id']) ? $params['category_id'] : -2;
        
        $default_category_id = isset($params['default_category_id']) ? $params['default_category_id'] : -2;
        
        $box_id = isset($params['box_id']) && is_numeric($params['box_id']) ? $params['box_id'] : -1;
        $lang = isset($params['lang']) ? $params['lang'] : __LANG__;
        $code = isset($params['code']) ? $params['code'] : false;
        $index = isset($params['index']) ? $params['index'] : false;
        $is_all = isset($params['is_all']) ? $params['is_all'] : -1;
        if($index){
            if($category_id  == -1){
                //$category_id = __CATEGORY_ID__;
            }
        }
        $orderBy = isset($params['orderBy']) ? $params['orderBy'] : ['a.position'=>SORT_ASC,'a.title'=>SORT_ASC];
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>$this->tableName()])
        ->where(['a.is_active'=>1])
        ->andWhere(['>','a.state',-2]);
        if($lang !== false){
            $query->andWhere(['a.lang'=>$lang]);
        }
        if($is_all == 1 && $code !== false){
            
        }else{
            $query->andWhere(['a.sid'=>__SID__]);
        }
        if($code !== false){
            $type = -1;
            $query->addSelect(['category_title'=>'b.title'])
            ->innerJoin(['b'=>'{{%adverts_category}}'],'a.type=b.id')
            ->andWhere(['b.code'=>$code,'b.is_active'=>1,'b.is_'.Yii::$app->device=>1] + ($lang !== false ? ['b.lang'=>$lang] : []));
            if($is_all == 1){
                $query->andWhere(['b.is_all'=>1,'b.sid'=>0]);
            }
        }
        if($type > -1){
            $query->andWhere(['a.type'=>$type]);
        }
        if($category_id > -2){
            $query->andWhere(['a.category_id'=>$category_id]);
        }
        if($box_id > -1){
            $query->andWhere(['a.box_id'=>$box_id]);
        }
        
//         view($query->createCommand()->getRawSql());
        
        $rs = $query->orderBy($orderBy)->asArray()->all();
        
        if(empty($rs) && $default_category_id > -2){
            
            $params['category_id'] = $default_category_id;
            
            $params['default_category_id'] = -2;            
            
            return $this->getItems($params);
        }
         
        return $this->populateData($rs);
        
    }
     
    
}