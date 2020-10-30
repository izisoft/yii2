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

class Place2 extends \izi\db\ActiveRecord
{
	
	public static function tableName(){
		return '{{%global_places}}';
	}
	  
	
	public function getItem($id){
	    return static::find()->where(['id'=>$id])->asArray()->one();
	}
	 
	public function findPlace($id){
	    return static::find()->where(['id'=>$id])->one();
	}
	
	
	public function findPlaces($params = []){
	    $query = static::find()->select(['a.*'])->from(['a'=>$this->tableName()])->where(['a.sid'=>__SID__]);
	    
	    if(isset($params['in']) && is_array($params['in'])){
	        $query->andWhere(['a.id'=>$params['in']]);
	    }
	    
	    if(isset($params['not_in']) && is_array($params['not_in'])){
	        $query->andWhere(['not in', 'a.id', $params['not_in']]);
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
}