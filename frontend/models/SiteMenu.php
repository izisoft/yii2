<?php 
namespace izi\frontend\models;
use Yii;

class SiteMenu extends \izi\db\ActiveRecord
{
    
    public static function tableName(){
        return '{{%site_menu}}';
    }
    
    
    public static function tableMenu(){
        return '{{%menu}}';
    }
    
    
    public static function tableMenuToLocation(){
        return '{{%menu_to_location}}';
    }
    
    
    public function getMenuLocation($code, $return_type = 1, $lang = false)
    {
        $query = static::find()
        ->from(['a'=>SiteMenu::tableMenu()])
        ->innerJoin(['b'=>SiteMenu::tableMenuToLocation()],'a.id=b.menu_id')
        ->where(['b.location_id'=>$code, 'b.temp_id'=>__TEMP_ID__])
        ->select(['a.title', 'a.json_data'])
        ;
        
        if($lang !== false){
            $query->andWhere(['a.lang'=>$lang]);
        }
        
        $r = $this->populateData($query->asArray()->one());        
        
       
        if($return_type == 2) return $r;
        
        if(isset($r['data'])) return $r['data'];
        
        return [];
    }
    
    public function getItem($id){
        return \izi\db\ActiveRecord::populateData(static::find()->where(['id'=>$id])->asArray()->one());
    }
    
    public function getList($params){ 
         
        
        $limit = isset($params['limit']) && is_numeric($params['limit']) ? $params['limit'] : -1;
        $order_by = isset($params['order_by']) ? $params['order_by'] : (isset($params['orderBy']) ? $params['orderBy'] : ['a.position'=>SORT_ASC, 'a.title'=>SORT_ASC]);
        $p = isset($params['p']) && is_numeric($params['p']) ? $params['p'] : 1;
        
        $position = isset($params['position']) ? $params['position'] : (isset($params['key']) ? $params['key'] : false);
         
        $temp_id = isset($params['temp_id']) && is_numeric($params['temp_id']) ? $params['temp_id'] : 0;

        $fields = isset($params['fields']) ? $params['fields'] : [
            'id',
            'title',
            'type',
            'url',
            'url_link',
            'parent_id',
            'lang',
            'short_title',
            'level',
            'bizrule'
        ];
        
        $parent_id = isset($params['parent_id']) ? $params['parent_id'] : -1;
        
        $required_parent_id = isset($params['required_parent_id']) && $params['required_parent_id'] === true ? true : false;
        //$type_id = isset($o['type_id']) ?  $o['type_id'] : -1;
        //$is_active = isset($o['is_active']) ? $o['is_active'] : -1;
        $offset = ($p-1) * $limit;
        
        $query = static::find()
        ->select($fields    )
        ->from(['a'=>SiteMenu::tableName()])
        ->where(['>','a.state',-2])
        ->andWhere(['a.sid'=>__SID__,'a.lang'=>__LANG__,'a.is_active'=>1,'a.is_invisibled'=>0]);
        if((isset($parent_id) && $parent_id>-1) || $required_parent_id){
            $query->andWhere(['a.parent_id'=>$parent_id]); 
        }
        if($position !== false){
            $query->andWhere(['a.id'=>(new \yii\db\Query())->select('item_id')->from('{{%items_to_posiotion}}')->where(['position_id'=>$position])]);
        }
        
        if($limit > 0){
            $query->limit($limit);
            if($offset > 0 ){
                $query->offset($offset);
            }
        }
        $query->orderBy($order_by);          
        
        return $this->populateData($query->asArray()->all());
    }
    
    
    public function getAllChildID($ids = 0, $include_id = true){
        
        if(!is_array($ids)) $ids = [$ids];
        $r = $ids;
        foreach ($ids as $id){
            
            $item = $this->getItem($id,['select'=>'lft,rgt,id']);
            if(!empty($item)){
                $l = static::find()->select(['id','lft','rgt'])->where([
                    '>','lft',$item['lft']
                ])->andWhere([
                    '<','rgt',$item['rgt']
                ])->andWhere(['sid'=>__SID__])-> asArray()->all();
                if(!empty($l)){
                    foreach ($l as $v){
                        if(!in_array($v['id'], $r)) $r[] = $v['id'];
                    }
                }
            }
        }
        return $r;
    }
    
	
	public function getBreadcrumbs($params = []){
		
		if(empty($params)) $params = (array)Yii::$app->view->category;
		

    	if(!isset($params['lft'])) return null; 
    	
    	return $this->populateData( static::find()->select(['*'])->where([
    			'<=','lft',$params['lft']
    	])->andWhere([
    			'>=','rgt',$params['rgt']
    	])->andWhere(['sid'=>__SID__,'is_invisibled'=>0])
    	->orderBy(['lft'=>SORT_ASC])
    	->asArray()->all());    	 
    }
    
    
    
    
    public function getReverseMenu($id = __CATEGORY_ID__){
        //
        $params = (array)Yii::$app->view->category;
        
        if(!isset($params['lft'])) return null;
        
        return static::find()->where([
            '<=','lft',$params['lft']
        ])->andWhere([
            '>=','rgt',$params['rgt']
        ])->andWhere(['sid'=>__SID__,'is_invisibled'=>0])
        ->orderBy(['lft'=>SORT_ASC])
        ->asArray()->all();
    }
    
    
    
    
    
    
    
    
    
    
    
    
}