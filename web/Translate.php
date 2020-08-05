<?php
namespace izi\web;

use Yii;
use yii\db\Query;
class Translate extends \yii\base\Component
{
	public $lang = __LANG__;
	public $t;
	public static function tableName(){
		return '{{%text_translate}}';
	}
	public static function tableUserTranslate(){
		return '{{%user_text_translate}}';
	}
	
	private $_insertNull = false;
	public function setInsertNull($value)
	{
	    $this->_insertNull = $value;
	}
	
	private $system, $user;
	
	public function __construct(){
	    $this->t[$this->lang] = $this->loadJson($this->lang);
	    
	    $this->system[$this->lang] = $this->loadSystemJson($this->lang);
	    
	    //$this->user[$this->lang] = $this->loadJson($this->lang);
	    
		if(defined('__IS_ADMIN__') && __IS_ADMIN__ && ADMIN_LANG != $this->lang){
		    $this->t[ADMIN_LANG] = $this->loadSystemJson(ADMIN_LANG);
		    
		    $this->system[ADMIN_LANG] = $this->loadSystemJson(ADMIN_LANG);
		    
		    //$this->user[ADMIN_LANG] = $this->loadJson(ADMIN_LANG);
		}
	}
	
	public function updateUserData(){
	    return true;
		$l = (new Query())->from($this->tableUserTranslate())->where(['sid'=>__SID__])
		->andWhere(['not in','lang_code',$this->loadJsonLangCode()])
		->all();
		if(!empty($l)){
			foreach ($l as $v){
				$this->updateLangcode($v['lang_code'], $v['lang'], $v['value']);
			}
		}
	}
	
	
	public function loadJsonLangCode($lang = __LANG__){
		$filename = Yii::getAlias('@app') . '/i18n/' . __SID__ . "/${lang}.json";
		if(file_exists($filename)){
			$text = file_get_contents($filename);
			$text = json_decode($text,1);
		}else{
			$text = [];
			writeFile($filename,json_encode($text));
		}
		$r = [];
		if(!empty($text)){
			foreach ($text as $lang_code => $name){
				$r[] = $lang_code;
			}
		}
		return $r;
	}
	
	public function loadSystemJson($lang = __LANG__){
	    $filename = Yii::getAlias('@app') . "/i18n/${lang}.json";
	    if(file_exists($filename)){
	        $text = file_get_contents($filename);
	        $text = json_decode($text,1);
	    }else{
	        $text = [];
	        writeFile($filename,json_encode($text));
	    }
	    return $text;
	}


	public function loadJson($lang = __LANG__){
		$filename = Yii::getAlias('@app') . '/i18n/' . __SID__ . "/${lang}.json";
		if(file_exists($filename)){
			$text = file_get_contents($filename);
			$text = json_decode($text,1);
		}else{
			$text = [];
			writeFile($filename,json_encode($text));
		}
		return $text;
	}
	public function translate($lang_code, $lang = __LANG__, $params = []){
	   
	    $system_lang = isset($params['system_lang']) && $params['system_lang'] === true ? $params['system_lang'] : false;;
	    
	    if(!isset($this->system[$lang])){
	        $this->system[$lang] = $this->loadSystemJson($lang);
	    }
	    	     
	    
	    if(isset($this->system[$lang][$lang_code]) && $this->system[$lang][$lang_code] != ""){
	        $system_lang = true;
	        return $this->system[$lang][$lang_code];
	    }elseif (isset($this->system[$lang][$lang_code])){
	        $system_lang = true;
	    }
	    
	    $user_translate = isset($params['user_translate']) && $params['user_translate'] === false ? $params['user_translate'] : true;
	    
	    $insert_null = isset($params['insert_null']) && $params['insert_null'] === true ? true : $this->_insertNull;
	    
	    
	    
	    if($user_translate && !isset($this->t[$lang])){
	        $this->t[$lang] = $this->loadJson($lang);
		}
		if(isset($this->t[$lang][$lang_code]) && $this->t[$lang][$lang_code] != ""){
		    return $this->t[$lang][$lang_code];
		}
		
		//
		$default = isset($params['default']) ? $params['default'] : $lang_code;
		$getdb = isset($params['getdb']) && !$params['getdb'] ? false : true;
		//
		$text = $lang_code; $t = [];
		
		//return $lang_code;
		
		if(!(isset($this->t[$lang][$lang_code]) && $this->t[$lang][$lang_code] != "")){
			$text = '';
			if(!$getdb){
				return $text;
			}
			if($user_translate){
    			$t = (new Query())->from($this->tableUserTranslate())->where([
    					'lang_code'=>$lang_code,'lang'=>$lang,'sid'=>__SID__
    			])->one();
			}
		
			if(!(!empty($t) && $t['value'] != "")){
				$t = (new Query())->from($this->tableName())->where([
						'lang_code'=>$lang_code,'lang'=>$lang
				])->one();
			}
			 
			if(!empty($t) && $t['value'] != ""){
				$text = $t['value'];
				
				if(isset($t['state']) && $t['state'] == 1){
				    $system_lang = true;
				}
				
				if(!$system_lang && $user_translate){
				    $this->t[$lang][$lang_code] = $t['value'];
				    $filename = Yii::getAlias('@app') . '/i18n/' . __SID__ . "/${lang}.json";
				    writeFile($filename,json_encode($this->t[$lang]));
				    
// 				    writeFile("${filename}2",$lang_code . ': '.$t['value'].'
// ','ab');
				    
				}elseif ($system_lang){
				    $this->system[$lang][$lang_code] = $t['value'];
				    $filename = Yii::getAlias('@app') . "/i18n/${lang}.json";
				    writeFile($filename,json_encode($this->system[$lang]));
				}
				
				
				if($insert_null && $text == ""){
				    $text = $lang_code;
				}
			}else{
				// Thêm vào bảng
			    
			    if($insert_null && $system_lang && empty($t)){
    				$id = \app\modules\admin\models\TextTranslate::getID();
    				
    				if($lang != SYSTEM_LANG  && !in_array($this->translate($lang_code, SYSTEM_LANG), ['', $lang_code])){
    				    if((new \yii\db\Query())->from($this->tableName())->where([
    				        
    				        'lang_code' => $lang_code,
    				        'lang' => SYSTEM_LANG,
    				    ])->count(1) == 0){
    				        Yii::$app->db->createCommand()->insert($this->tableName(),[    				            
    				            'lang_code' => $lang_code,
    				            'lang' => SYSTEM_LANG,
    				            'value' => $default == $lang_code ? '' : $default,
    				            'state'=>1
    				        ])->execute();
    				    }
    				}
    				
    				Yii::$app->db->createCommand()->insert($this->tableName(),[
    						'id' => $id,
    						'lang_code' => $lang_code,
    						'lang' => $lang,
    						'value' => '',
    				        'state'=>1
    				])->execute();
			    }
		    
			    
// 			    if($lang_code == 'label_pax')
// 			    {
// 			        view($insert_null);
// 			        view($default);
// 			        view($system_lang);
// 			        view($user_translate);
// 			        view($t);
// 			    }
			    
			    
			    if($insert_null && $default != "" && 
			        !$system_lang && $user_translate 
// 			        && $default != $lang_code 
			        && empty($t)){
		 
			            if($lang != SYSTEM_LANG && !in_array($this->translate($lang_code, SYSTEM_LANG), ['', $lang_code])){
			                
			                if((new \yii\db\Query())->from($this->tableUserTranslate())->where([
			                    'sid' => __SID__,
			                    'lang_code' => $lang_code,
			                    'lang' => SYSTEM_LANG,
			                ])->count(1) == 0){
			                    Yii::$app->db->createCommand()->insert($this->tableUserTranslate(),[
			                        'sid' => __SID__,
			                        'lang_code' => $lang_code,
			                        'lang' => SYSTEM_LANG,
			                        'value' => $default == $lang_code ? '' : $default,
			                    ])->execute(); 
			                }
			            }
						
						if($lang != SYSTEM_LANG && (new \yii\db\Query())->from($this->tableUserTranslate())->where([
			                    'sid' => __SID__,
			                    'lang_code' => $lang_code,
			                    'lang' => $lang,
			                ])->count(1) == 0){
			        
							Yii::$app->db->createCommand()->insert($this->tableUserTranslate(),[
									'sid' => __SID__,
									'lang_code' => $lang_code,
									'lang' => $lang,
									'value' => $default == $lang_code ? '' : $default,
							])->execute();
						}
						
				}
				// 
				return $default;
			}
		}else{
			$text = $this->t[$lang][$lang_code];
		}
		if($text == $lang_code){
			$text = $default;
		}
		return $text;
	}
	
	////
	public function updateData($data, $lang, $system = false){
	    $filename = $system ? Yii::getAlias('@app') . "/i18n/${lang}.json" : Yii::getAlias('@app') . '/i18n/' . __SID__ . "/${lang}.json";
		writeFile($filename,json_encode($data));
	}
	
	public function deleteLangcode($lang_code, $lang = false){
		$language = (\app\modules\admin\models\AdLanguage::getUserLanguage());
		if(!empty($language)){
			foreach ($language as $v){
				if($lang === false){
					$data = $this->loadJson($v['code']);
					if(isset($data[$lang_code])){
						unset($data[$lang_code]);
						$this->updateData($data, $v['code']);
					}
				}elseif($lang == $v['code']){
					$data = $this->loadJson($v['code']);
					if(isset($data[$lang_code])){
						unset($data[$lang_code]);
						$this->updateData($data, $v['code']);
					}
					break;
				}
			}
		}
	}
	public function updateLangcode($lang_code, $lang, $value){
		$data = $this->loadJson($lang);
		
		$updateDb = false;
		if(isset($data[$lang_code]) && $data[$lang_code] != $value){
		    $updateDb = true;
		}elseif(!isset($data['lang_code'])){
		    $updateDb = true;
		}
		
		$data[$lang_code] = $value;
		
		$this->updateData($data, $lang);
		
		//
		if($updateDb){
		 
// 		    Yii::$app->db->createCommand()->update('user_text_translate', ['value'=>$value], ['sid'=>__SID__, 'lang_code'=>$lang_code, 'lang'=>$lang])->execute();
		  
		  $this->dbUpdateUserTextTranslate($lang_code, $lang, $value);
		  
		}
		//
	}
	
	public function updateSystemLangcode($lang_code, $lang, $value){
	    $data = $this->loadSystemJson($lang);
	    $data[$lang_code] = $value;
	    $this->updateData($data, $lang, true);
	}
	
	public function getTextTranslateByCode($code ){
	    return (new \yii\db\Query())->from($this->tableName())->where(['lang_code'=>$code])->one();
	}
	
	public function dbUpdateTextTranslate($lang_code, $lang, $value, $state = 0){
	    $con = [
	        'lang_code'=>$lang_code,
	        'lang'=>$lang,
	    ];
	    if((new \yii\db\Query())->from($this->tableName())->where($con)->count(1) == 0){
	        //\app\modules\admin\models\TextTranslate::getItem2($lang_code)
	        $item = $this->getTextTranslateByCode($lang_code);
	        
	        if(!empty($item)){
	            $id = $item['id'];
	        }else{	            
	            
	            $id = \app\modules\admin\models\TextTranslate::getID();
	        }
	        
	        Yii::$app->db->createCommand()->insert($this->tableName(),[
	            'id' => $id,
	            'lang_code' => $lang_code,
	            'lang' => $lang,
	            'value' => $value,
	            'state'=>$state
	        ])->execute();
	    }else{
	        Yii::$app->db->createCommand()->update($this->tableName(), ['value'=>$value], $con)->execute();
	    }
	}
	
	
	public function dbUpdateUserTextTranslate($lang_code, $lang, $value, $state = 0){
	    $con = [
	        'lang_code'=>$lang_code,
	        'lang'=>$lang,
	        'sid'=>__SID__
	    ];
	    if((new \yii\db\Query())->from($this->tableUserTranslate())->where($con)->count(1) == 0){
	        
	        Yii::$app->db->createCommand()->insert($this->tableUserTranslate(),[
	            'sid' => __SID__,
	            'lang_code' => $lang_code,
	            'lang' => $lang,
	            'value' => $value,
	            //'state'=>$state
	        ])->execute();
	    }else{
	        Yii::$app->db->createCommand()->update($this->tableUserTranslate(), ['value'=>$value], $con)->execute();
	    }
	}
	
	
	public function maskDuplicateRecord($params){
	    $limit = isset($params['limit']) ? $params['limit'] : 250;
	    
	    $field = isset($params['field']) ? $params['field'] : 'title';
	    
	    $field_condition = isset($params['field_condition']) ? $params['field_condition'] : [
	        'lang'=>ADMIN_LANG,'sid'=>__SID__
	    ];
	    
	    $con = array_merge($field_condition,['is_duplicate'=>0,'status'=>0]);
	    
	    $l = (new \yii\db\Query())->from($params['table'])->where($con)->limit($limit)->all();
	    if(!empty($l)){
	        
	        $ck = [];
	        foreach ($l as $v){
	            $i = (new \yii\db\Query())->from($params['table'])->where(
	                ['and',[$field=>$v[$field] + $field_condition],['not in', 'lang_code',$v['lang_code']]]
	                
	                )->one();
	            
	                if(!empty($i)){
	                    Yii::$app->db->createCommand()->update($params['table'], ['is_duplicate'=>1],
	                        ['lang_code'=>[$v['lang_code'], $i['lang_code']],'sid'=>__SID__]
	                        )->execute();
	                }
	                $ck[] = $v['lang_code'];
	        }
	        Yii::$app->db->createCommand()->update($params['table'], ['status'=>1],
	            ['lang_code'=>$ck,'sid'=>__SID__]
	            )->execute();
	    }
	    
	    
	}
	
	
	public function unMaskDuplicateSingleRecord($params){
	    
	    $field = isset($params['field']) ? $params['field'] : 'title';
	    
	    $value = isset($params['value']) ? $params['value'] : '';
	    
	    $field_condition = isset($params['field_condition']) ? $params['field_condition'] : [
	        'lang'=>ADMIN_LANG,'sid'=>__SID__
	    ];
	    
	    //$con = array_merge($field_condition,['is_duplicate'=>0,'status'=>0]);
	    
	    $v = (new \yii\db\Query())->from($params['table'])->where(
	        [$field=>$value] + $field_condition
	        
	        )->all();
	        if(count($v) == 1){
	            Yii::$app->db->createCommand()->update($params['table'], ['is_duplicate'=>0, 'status'=>0],
	                ['lang_code'=>$v[0]['lang_code'], 'sid'=>__SID__]
	                )->execute();
	        }
	        
	        
	}
	
	
	public function getDuplicateSingleRecord($params){
	    
	    $field = isset($params['field']) ? $params['field'] : 'title';
	    
	    $lang_code = isset($params['lang_code']) ? $params['lang_code'] : '';
	    
	    $value = isset($params['value']) ? $params['value'] : '';
	    
	    $field_condition = isset($params['field_condition']) ? $params['field_condition'] : [
	        'lang'=>ADMIN_LANG,'sid'=>__SID__
	    ];
	    
	    //$con = array_merge($field_condition,['is_duplicate'=>0,'status'=>0]);
	    
	    $l = (new \yii\db\Query())->from($params['table'])->where(
	        ['and',[$field=>$value] + $field_condition
	        ,[
	            'not in', 'lang_code', $lang_code
	        ]]
	        )->all();
	        
	    return $l;    
	        
	}
	
	public function checkDuplicate($limit = 150){
	    $l = (new \yii\db\Query())->from($this->tableUserTranslate())->where(['lang'=>ADMIN_LANG,'sid'=>__SID__, 'is_duplicate'=>0,'status'=>0])->limit($limit)->all();
	    
	    if(!empty($l)){
	        
	        $ck = [];
	        
	        foreach ($l as $v){
	            $i = (new \yii\db\Query())->from($this->tableUserTranslate())->where(
	                ['and',['value'=>$v['value'],'sid'=>__SID__, 'lang'=>ADMIN_LANG],['not in', 'lang_code',$v['lang_code']]]
	                
	                )->one();
	            if(!empty($i)){
	                Yii::$app->db->createCommand()->update($this->tableUserTranslate(), ['is_duplicate'=>1],
	                    ['lang_code'=>[$v['lang_code'], $i['lang_code']],'sid'=>__SID__]
	                )->execute();
	            }
	            $ck[] = $v['lang_code'];
	        }
	        Yii::$app->db->createCommand()->update($this->tableUserTranslate(), ['status'=>1], 
	            ['lang_code'=>$ck,'sid'=>__SID__]
	        )->execute();
	        
	    }else{
	        view(0);
	    }
	    
	}
	
	public function updateSingleDuplicate($value){
	    $v = (new \yii\db\Query())->from($this->tableUserTranslate())->where(
	       ['value'=>$value, 'lang'=>ADMIN_LANG, 'sid'=>__SID__]
	        
	        )->all();
	     
	        
	        if(count($v) == 1){
            Yii::$app->db->createCommand()->update($this->tableUserTranslate(), ['is_duplicate'=>0],
                ['lang_code'=>$v[0]['lang_code'], 'sid'=>__SID__]
                )->execute();
        }
        
        
	}
	
	
	public function getCustomerLangcode($id ){
	    $lang_code = "text_cpartner_" .  $id ;
	    return $lang_code;
	}
	
	
	/**
	 * Import dữ liệu từ bảng customers vào bảng user_translate để dịch
	 */
	public function importDataFromCustomer($customer_id, $params = []){
	    $customer = isset($params['customer']) && !empty($params['customer']) ? $params['customer'] : 
	    Yii::$app->customer->getItem($customer_id);
	    $lang_code = "text_cpartner_" .  $customer['id'];
	    
	    if($customer['type_id']>0){
	       $this->dbUpdateUserTextTranslate($lang_code, ROOT_LANG, $customer['name']);
	    }
	    
	}
	
	public function importAllDataFromCustomer($params = []){
	    $l = (Yii::$app->customer->model->getAll($params));
	    if(!empty($l)){
	        foreach ($l as $v){
	            $this->importDataFromCustomer($v['id'], $v);
	        }
	    }
	}
	
	
	public function removeUserTextTranslate($lang_code)
	{
	    \app\modules\admin\models\UserTextTranslate::deleteUserText($lang_code);
	    $this->deleteLangcode($lang_code);
	}
	
	
	public function cleanEmptyText()
	{
	    // Clean foods name
	    $query = (new \yii\db\Query())
	    ->from($this->tableUserTranslate())
	    ->where(['like', 'lang_code', 'text_food_name_%', false])
	    ->andWhere(['not in', 'lang_code', (new \yii\db\Query())->select('lang_code')->from(\izi\tour\restaurant\models\Foods::tableName()) ])
	    ;
	     	    
	    $l = $query->all();
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            $this->removeUserTextTranslate($v['lang_code']);
	        }
	    }
	   
	    
	}
	
	
	public function syncFoods()
	{
	    // Food -> Translate
	    $query = (new \yii\db\Query())
	    ->from(\izi\tour\restaurant\models\Foods::tableName())	    
	    ->where(['not in', 'lang_code', (new \yii\db\Query())->select('lang_code')->from($this->tableUserTranslate()) ])
	    ;
	    
	    $l = $query->all();
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            
	            if($v['lang_code'] == ""){
	                $v['lang_code'] = 'text_food_name_' . $v['id'];
	                
	                Yii::$app->db->createCommand()->update(\izi\tour\restaurant\models\Foods::tableName(), [
	                   
	                    'lang_code'    =>  $v['lang_code']
	                ], ['id' => $v['id']]
	                    )->execute();
	            }
	            
	            Yii::$app->db->createCommand()->insert($this->tableUserTranslate(), [
	                'sid' => __SID__,
	                'lang' =>  SYSTEM_LANG,
	                'value'    =>  $v['title'],
	                'lang_code'    =>  $v['lang_code']
	            ])->execute();
	        }
	    }
	    
	    
	    // remove text empty
	    $query = (new \yii\db\Query())
	    ->from($this->tableUserTranslate())
	    ->where(['like', 'lang_code', 'text_food_name_%', false])
	    ->andWhere(['not in', 'lang_code', (new \yii\db\Query())->select('lang_code')->from(\izi\tour\restaurant\models\Foods::tableName()) ])
	    ;
	    
	    $l = $query->all();
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            $this->removeUserTextTranslate($v['lang_code']);
	        }
	    }
	    
	}
	
	/**
	 * Update default
	 */
	
	public function updateDefault($lang_code)
	{
	    preg_match('/^[A-Za-z_]+/i', $lang_code, $m);
	    if(!empty($m)){
	        $tb = false;
	        switch ($m[0]){
	            case 'text_ticket_':
	                $tb = 'tickets';
	                break;
	            case 'text_distance_':
	                $tb = 'distances';
	                break;
	                
	            default: 
	                if(!in_array($value = $this->translate($lang_code, ROOT_LANG), ['', $lang_code])){
	                    $this->updateLangcode($lang_code, ROOT_LANG, $value);
	                }
	                break;
	        }
	        
	        if($tb !== false){
    	        $query = (new \yii\db\Query())
    	        ->from($tb)
    	        ->where(['lang_code' => $lang_code]);
    	        
    	        $item = $query->one();
    	        
    	        if(!empty($item)){
    	            $value = isset($item['title']) ? $item['title'] : (isset($item['name']) ? $item['name'] : '');
    	            
    	            if($value != ""){
    	                $this->updateLangcode($lang_code, ROOT_LANG, $value);
    	            }
    	            
    	        }
	        }
	    }
	}
	
	public function syncTextTranslate()
	{
	    
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}