<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\models;
use Yii;

class Place extends \yii\db\ActiveRecord
{
	
	public static function tableName(){
		return '{{%places}}';
	}
	  
	
	public function getItem($id){
	    return static::find()->where(['id'=>$id])->asArray()->one();
	}
	 
	public function findPlace($id){
	    return static::find()->where(['id'=>$id])->one();
	}
	
	
	public function findPlaces($params = []){
	    $query = static::find()->select(['a.*'])->from(['a'=>$this->tableName()])->where(['a.sid'=>__SID__]);
	    
	    if(isset($params['in']) && is_array($params['in']) && $params['in'] !== null){
	        $query->andWhere(['a.id'=>$params['in']]);
	    }
	    
	    if(isset($params['not_in']) && is_array($params['not_in']) && !empty($params['not_in'])){
	        $query->andWhere(['not in', 'a.id', $params['not_in']]);
	    }
	    
	    if(($filter_text = isset($params['filter_text']) ? $params['filter_text'] : '') != '*'){
	        $query->andFilterWhere(['or',[
	            'like','a.title',$filter_text
	        ],
	        //    ,
// 	            [
// 	                'like','a.name',$filter_text
// 	            ],
	            [
	                'like','a.lang_code',$filter_text
	            ]
	            
	        ]);
	    }
	    
	    /**
	     * Find text instruction place
	     */
	    if(isset($params['text_instruction_id'])){
	        
	        $query->innerJoin(['t'=>'text_instruction_to_place'],'a.id=t.place_id');
	        
	        $query->andWhere(['t.text_id' => $params['text_instruction_id']]);
	    }
	    
	    
	    $l = $query->orderBy(['a.title'=>SORT_ASC])->asArray()->all();
	    
	    if(isset($params['return_column'])){
	        $r = [];
	        if(!empty($l)){
	            foreach ($l as $v) {
	                $r[] = $v[$params['return_column']];
	            }
	        }
	        return $r;
	    }
	    
	    return $l;
	}
	
	
	
	public function getPlaceByFilter($filter_id){
	    
	    if(!is_array($filter_id)){
	        $filter_id = [$filter_id];
	    }
	    
	    $r = []; $dt  = [];
	    
	    foreach ($filter_id as $fid){
    	    $filter = Yii::$app->filter->model->getItem($fid);
    	    
    	   
    	   
    	    if(!empty($filter) && $filter['local_id']>0){
    	        $dt[] = $filter['local_id'];
    	        
    	    }
	    
	    }
	    
	    $r = static::find()->where(['and',['sid' => __SID__, 'is_invisible'=>0], ['>', 'state', -2] ,[ 'or',
	        ['local_id1' => $dt],
	            ['local_id2' => $dt],
	                ['local_id3' => $dt],
	        
	    ]])->orderBy(['title' => SORT_ASC])->asArray()->all();
	    
	    return $r;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}