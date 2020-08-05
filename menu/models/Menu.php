<?php 
namespace izi\menu\models;

use Yii;
use yii\db\Query;

class Menu extends \izi\db\ActiveRecord
{
    
    public static function tableName(){
        return '{{%menu}}';
    }
    
    public static function tableToLocation(){
        return '{{%menu_to_location}}';
    }
    
    
    public function getItem($id){
         
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>$this->tableName()])
        ->where(['a.sid'=>__SID__, 'id'=>$id])
        ;
        
        
        $rs = $query->asArray()->one();
        
        
        return $this->populateData($rs);
        
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
        $orderBy = isset($params['orderBy']) ? $params['orderBy'] : ['a.id'=>SORT_ASC];
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>$this->tableName()])
        ->where(['a.sid'=>__SID__])
        ;
         
     
        $rs = $query->orderBy($orderBy)->asArray()->all();
 
         
        return $this->populateData($rs);
        
    }
    
    public function getCurrentMenu($location_id, $temp_id)
    {
        $rs = static::find()->from(['a'=>'menu'])->innerJoin(['b'=>'menu_to_location'],'a.id=b.menu_id')
        ->where([
            'temp_id'=>$temp_id,
            'location_id'=>$location_id
        ])
        ->asArray()->one();
        
        return $this->populateData($rs);
    }
     
    
    public function validateMenuLocation($menu_id, $temp_id, $location_id)
    {
        return (new \yii\db\Query())->from('menu_to_location')->where(['location_id'=>$location_id, 'menu_id'=>$menu_id, 'temp_id'=>$temp_id])->count(1) > 0 ? true : false;
    }
}