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
use yii\db\Query;
class Box extends \app\models\Box
{
	
	public static function tableName(){
		return '{{%box}}';
	}
	  
	public function getBox($code, $params = []){
	    	    
	    
	    $v = (new Query())->from(self::tableName())->where([
	        'sid'	=>	__SID__,
	        'code'	=>	$code,
	        'lang'	=>	__LANG__,
	        'is_active'=>1
	    ])->one();
	    
	    if(!!empty($v) && isset($params['required']) && $params['required'] == true){
	        $v2 = (new Query())->from(self::tableName())->where([
	            'sid'	=>	__SID__,
	            'code'	=>	$code,
	            'lang'	=>	__LANG__,
	            //'is_active'=>1
	        ])->one();
	        if(!!empty($v2)){
	            $default = isset($params['default']) ? $params['default'] : [];
	            
	            if(isset($default['bizrule']) && is_array($default['bizrule'])){
	                $default['bizrule'] = json_encode($default['bizrule'], JSON_UNESCAPED_UNICODE);
	            }
	            
	            Yii::$app->db->createCommand()->insert($this->tableName(), array_merge([
	                'sid'	=>	__SID__,
	                'code'	=>	$code,
	                'lang'	=>	__LANG__,
	                'is_active'=>1,
	                //'title'=>$code,
	                
	            ], $default
	            ))->execute();
	        }
	    }
	    return $this->populateData($v);
	}
	
	
	public function showBoxText($code){
	    $r = $this->getBox($code);
	    if(!empty($r) && isset($r['text'])){
	        return uh($r['text'],2);
	    }
	}
	 
	/**
	 * 
	 */
	
	public function getAll(){
		$query = static::find()
		->from(['a'=>$this->tableName()])
		->where(['a.sid'=>__SID__,'is_active'=>1])
		->andWhere(['>','a.state',-2]);
		return $query->asArray()->all();
	}
	
	public static function getItem($id){
		$query = static::find()
		->where(['sid'=>__SID__,'id'=>$id]);		
		$item = $query->asArray()->one();
		if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
			$item += $content;
			unset($item['bizrule']);
		}
		return $item;
	}
		 
	public function getBoxModule($module = 'index', $params = []){
	    $r = static::find()
	    ->where(['module'=>$module,'lang'=>__LANG__,'is_active'=>1,'sid'=>__SID__])
	    ->andWhere(['>','state',-2]);
	    if(isset($params['absulute_menu_id']) && $params['absulute_menu_id']>0){
	        if((new Query())->from('box_to_site_menu')->where(['menu_id'=>$params['absulute_menu_id']])->count(1)>0){
	            $r->andWhere(
	                ['id'=>(new Query())->select('item_id')->from('box_to_site_menu')->where(['menu_id'=>$params['absulute_menu_id']])]
	                
	                );
	        }
	    }
	    	
		$data = $r->orderBy(['position'=>SORT_ASC,'title'=>SORT_ASC])->asArray()->all();
		
		if(empty($data) && isset($params['migrate'])){
			Yii::$app->frontend->migrate($params['migrate']);
		}

		return $this->populateData($data);
	}



	/**
     * update 05/08/2020
     */


    public function migrate($data)
    {
        foreach($data['data'] as $b){
			$item = Box::findOne(['sid' => __SID__, 'title' => $b['title'], 'module'=>$data['module']]);
			if(empty($item)){
				$item = new Box();
				$item->code = randString(6);
				$item->module = $data['module'];
				$item->sid = __SID__;
				if(isset($b['json_data'])){
					$item->bizrule = json_encode($b['json_data']);
					unset($b['json_data']);
				}
				
				foreach($b as $k=>$v){
					$item->$k = $v;
				}

				$item->save();
			}
		}
	}
	

}