<?php

namespace izi\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "site_menu".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $position
 * @property integer $is_active
 * @property string $type
 * @property integer $status
 * @property integer $views
 * @property string $lang
 * @property string $title
 * @property string $short_title
 * @property string $url
 * @property string $check_sum
 * @property string $bizrule
 * @property integer $old_id
 * @property integer $state
 * @property integer $sid
 * @property integer $category_type
 */
class SiteMenu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%site_menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'position', 'is_active', 'status', 'views', 'old_id', 'state', 'sid', 'category_type'], 'integer'],
            [['short_title'], 'required'],
            [['bizrule'], 'string'],
            [['type'], 'string', 'max' => 10],
            [['lang'], 'string', 'max' => 6],
            [['title', 'short_title', 'url'], 'string', 'max' => 255],
            [['check_sum'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'position' => 'Position',
            'is_active' => 'Is Active',
            'type' => 'Type',
            'status' => 'Status',
            'views' => 'Views',
            'lang' => 'Lang',
            'title' => 'Title',
            'short_title' => 'Short Title',
            'url' => 'Url',
            'check_sum' => 'Check Sum',
            'bizrule' => 'Bizrule',
            'old_id' => 'Old ID',
            'state' => 'State',
            'sid' => 'Sid',
            'category_type' => 'Category Type',
        ];
    }
    public static function getItem($id=0,$o=[]){
    	$item = static::find()
    	->where(['id'=>$id, 'sid'=>__SID__]);    	
    	if(isset($o['select'])) $item->select($o['select']);
    	$item = $item->asArray()->one();    
    	return $item;
    }
    public static function getList($o = []){
    	$limit = isset($o['limit']) && is_numeric($o['limit']) ? $o['limit'] : -1;
    	$order_by = isset($o['order_by']) ? $o['order_by'] : ['a.position'=>SORT_ASC, 'a.title'=>SORT_ASC];
    	$p = isset($o['p']) && is_numeric($o['p']) ? $o['p'] : Yii::$app->request->get('p',1);
    	$key  = isset($o['key']) ? $o['key'] : false;
    	$filter_text = isset($o['filter_text']) ? $o['filter_text'] : '';
    	$parent_id = isset($o['parent_id']) ? $o['parent_id'] : -1;
    	//$type_id = isset($o['type_id']) ?  $o['type_id'] : -1;
    	//$is_active = isset($o['is_active']) ? $o['is_active'] : -1;
    	$offset = ($p-1) * $limit;
    	$query = static::find()
    	->from(['a'=>self::tableName()])
    	->where(['>','a.state',-2])
    	->andWhere(['a.sid'=>__SID__,'a.lang'=>__LANG__,'a.is_active'=>1,'a.is_invisibled'=>0]);
    	if($parent_id>-1){
    		$query->andWhere(['a.parent_id'=>$parent_id]);
    	}
    	if($key !== false){
    		$query->andWhere(['a.id'=>(new Query())->select('item_id')->from('{{%items_to_posiotion}}')->where(['position_id'=>$key])]);
    	}
    	
    	if($limit > 0){
    		$query->limit($limit);
    		if($offset > 0 ){
    			$query->offset($offset);
    		}
    	}
    	$query->orderBy($order_by);
    	return $query->asArray()->all();
    }
    
    public static function getAllChildID($ids = 0, $include_id = true){
    	
    	if(!is_array($ids)) $ids = [$ids];
    	$r = $ids;
    	foreach ($ids as $id){
    		
    	$item = self::getItem($id,['select'=>'lft,rgt,id']);
    	if(!empty($item)){
    		$l = static::find()->select(['id','lft','rgt'])->where([
    				'>','lft',$item['lft']
    		])->andWhere([
    				'<','rgt',$item['rgt']
    		])->andWhere(['sid'=>__SID__])-> asArray()->all();
    		if(!empty($l)){
    			foreach ($l as $k=>$v){
    				if(!in_array($v['id'], $r)) $r[] = $v['id'];
    			}
    		}
    	}
    	}
    	return $r;
    }
    public static function get_tree_menu(){
    	//view(CONTROLLER_RGT);
    	if(!defined('CONTROLLER_LFT')) return null;
    	
    	return static::find()->select(['*'])->where([
    			'<=','lft',CONTROLLER_LFT
    	])->andWhere([
    			'>=','rgt',CONTROLLER_RGT
    	])->andWhere(['sid'=>__SID__,'is_invisibled'=>0])
    	->orderBy(['lft'=>SORT_ASC])
    	->asArray()->all();    	 
    }
    
}
