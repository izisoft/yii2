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
class Shop extends \yii\db\ActiveRecord
{
	/**
	 * Configs
	 * @return string
	 */

	public static function tableName(){
		return '{{%shops}}';
	}

	public static function tableArticle(){
		return '{{%articles}}';
	}

	public static function tableTemplate(){
		return '{{%templates}}';
	}

	public static function tableTemplateToShop(){
		return '{{%temp_to_shop}}';
	}

	public static function tableDomain(){
		return '{{%domain_pointer}}';
	}

	
	public function checkDomainExisted($domain){
	    if((new \yii\db\Query())->from(Shop::tableName())->where(['domain'=>$domain])->count(1) > 0){ 
	        return true;
	    }
	    return false;
	}
	
	public function countDomainExisted($sid = __SID__){
	    return (new \yii\db\Query())->from(Shop::tableName())->where(['sid'=>$sid])->count(1);
	}
	
	public function addDomain($domain, $sid = __SID__){
	    $www = strtolower(substr($domain, 0,4)) == 'www.' ? true : false;
	    $count = $this->countDomainExisted($sid);
	    if(!$this->checkDomainExisted($domain)){
	        $columns = [
	            'sid'=>$sid,
	            'domain'=>$domain,
	            'is_default'=>$count>0 ? 2 : 1,
	        ];
	        Yii::$app->db->createCommand()->insert(Shop::tableName(), $columns)->execute();
	        
	        if($www === false && !$this->checkDomainExisted("www.$domain")){
	            $columns = [
	                'sid'=>$sid,
	                'domain'=>"www.$domain",
	                'is_default'=>2,
	            ];
	            Yii::$app->db->createCommand()->insert(Shop::tableName(), $columns)->execute();
	        }
	    }
	}
	
	
	public static function getDomainInfo($domain = __DOMAIN__){		
		
		$params = [
		    __CLASS__,
		    __FUNCTION__,
		    $domain,
		    date('H')
		];
		
		$config = Yii::$app->icache->getCache($params);

		if(!YII_DEBUG && !empty($config)){
			return $config;
		}else{
			$config = static::find()
			->select(['a.sid','a.is_invisible','b.status','b.code','a.is_admin','a.module','b.to_date','a.state','b.layout','b.api_token'])
			->from(['a'=>'{{%domain_pointer}}'])
			->innerJoin(['b'=>'{{%shops}}'],'a.sid=b.id')
			->where(['a.domain'=>__DOMAIN__])->asArray()->one();			
			Yii::$app->icache->store($config, $params);
			return $config;
		}
	}

	/**
	 * Get seo config
	 * @return array
	 */
	public function setSeoConfig(){
		$seo = Yii::$app->db->getConfigs('SEO');
		if(isset($seo['domain_type']) && $seo['domain_type'] == 'multiple'){
			$domains = isset($seo['domain']) && $seo['domain'] != '' ? explode(',', $seo['domain']) : [];
			$sd = [];
			if(!empty($domains)){
				foreach ($domains as $domain){
					if($domain == DOMAIN){
						if(isset($seo[$domain])){
							$sd = $seo[$domain];
							unset($seo[$domain]);
						}
					}else{
						if(isset($seo[$domain])){
							unset($seo[$domain]);
						}
					}
				}
			}
			$this->config['seo'] = array_merge($sd,$seo);
			unset($seo);
		}elseif (isset($seo['domain_type'])){
			$this->config['seo'] = $seo;unset($seo);
		}
		$www = isset($this->config['seo']['www']) ? $this->config['seo']['www'] : -1;
		if(!isset($this->config['seo']['amp'])) {
			$this->config['seo']['amp'] = [];
		}
		switch ($www){
			case 0:
				if(strpos(ABSOLUTE_DOMAIN, 'www.') !== false){
					header('Location:' . SCHEME  . '://' . URL_NON_WWW . URL_PORT . URL_PATH ,301);
					exit;
				}
				break;
			case 1:
				if(strpos(ABSOLUTE_DOMAIN, 'www.') === false){
					header('Location:' . SCHEME  . '://www.' . URL_NON_WWW . URL_PORT . URL_PATH ,301);
					exit;
				}
				break;
		}
		return $this->config['seo'];
	}

	public static function getArticleDetail($item_id){
		$item = static::find()->from(self::tableArticle())->where([
				'id'=>$item_id,'sid'=>__SID__
		])->asArray()->one();
		if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
			$item += $content;
			unset($item['bizrule']);
		}

		if(isset($item['content']) && ($content = json_decode($item['content'],1)) != NULL){
			$item += $content;
			unset($item['content']);
		}
		return $item;
	}

	public static function setViewedCount($item_id){
		$sesision_id = session_id();
		if(!(isset($_SESSION[$sesision_id]['last_viewed'][$item_id]['time']) && __TIME__-$_SESSION[$sesision_id]['last_viewed'][$item_id]['time'] < 300)){
			$_SESSION[$sesision_id]['last_viewed'][$item_id]['time'] = __TIME__;
			Yii::$app->db->createCommand("update articles set viewed=viewed+1 where id=".$item_id)->execute();
		}
	}

	public static function setTemplate(){
		$config = Yii::$app->session->get('config');
		$TEMP = self::getTemplateName();
		switch (SHOP_STATUS){

			default:
				define('__TEMP_NAME__', __IS_ADMIN__ ? 'admin' : ($TEMP['name'] != "" ? $TEMP['name'] : 'coming1'));
				break;
		}

		$config['TCID'][__SID__] = !empty($TEMP) ? $TEMP['parent_id'] : 0;
		$config['TID'][__SID__] = !empty($TEMP) ? $TEMP['id'] : 0;
		define('__TID__', $config['TID'][__SID__]);
		define('__TCID__', $config['TCID'][__SID__]);


		// Get device
		if(isset($config['set_device']) && in_array($config['set_device'],['mobile','desktop'])){
			self::$device=$config['device']=$config['set_device'];
			$t = false;
		}else{
			$t = true;
		}

		//
		if($t || !isset($config['device'])){
			$useragent=$_SERVER['HTTP_USER_AGENT'];

			if(preg_match('/(android|bb\d+|meego).+mobile|(android \d+)|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
					||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
						self::$device = 'mobile';
						self::$is_mobile = true;
			}
			$config['device'] = self::$device;
		}else{
			self::$device = $config['device'];
		}


		Yii::$app->session->set('config', $config);
		if(self::$device != 'desktop' && $TEMP['is_mobile'] == 1){
			define('__IS_MOBILE_TEMPLATE__' , true );
			define('__MOBILE_TEMPLATE__' , '/' . self::$device  );
		}else {
			define('__IS_MOBILE_TEMPLATE__', false);
			define('__MOBILE_TEMPLATE__' , '' );
		}



		$app_path = Yii::getAlias('@app');
		$themePath = Yii::getAlias('@themes');

		switch (self::$device){
			case 'mobile':

				$dir = $themePath .'/' . __TEMP_NAME__ . __MOBILE_TEMPLATE__;
				$s = removeLastSlashes(\yii\helpers\Url::home()) . '/themes/'. __TEMP_NAME__ . __MOBILE_TEMPLATE__;

				if(!file_exists($dir)){

					$dir = $themePath .'/' . __TEMP_NAME__;
					$s = removeLastSlashes(\yii\helpers\Url::home()) . '/themes/'.__TEMP_NAME__.'';
					self::$is_mobile = false;
				}
				define('__RSPATH__',$dir);
				define('__RSDIR__',__IS_ADMIN__ ? \yii\helpers\Url::base() . '/themes/'.__TEMP_NAME__ : $s);
				break;
			default:

				$dir = $themePath .'/' . __TEMP_NAME__ . __MOBILE_TEMPLATE__;
				define('__RSPATH__',$dir);
				define('__RSDIR__',  \yii\helpers\Url::base() . '/themes/'.__TEMP_NAME__ . __MOBILE_TEMPLATE__);
				break;
		}
		if(__IS_ADMIN__){
			define ('__VIEW_PATH__',__RSPATH__ . DIRECTORY_SEPARATOR . 'views');
		}else{

		}
		define ('__IS_MOBILE__',self::$is_mobile);
		define('__LIBS_DIR__',Yii::getAlias('@libs'));
		define('__LIBS_PATH__',Yii::getAlias('@frontend/web/libs'));

	}

	public function getTemplateBySid($sid = __SID__){
	    defined('__TEMPLATE_DOMAIN_STATUS__') or define('__TEMPLATE_DOMAIN_STATUS__', 1);
	    $r = static::find()
	    ->select(['a.*'])
	    ->from(['a'=>Shop::tableTemplate()])
	    ->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
	    ->where(['b.state'=>__TEMPLATE_DOMAIN_STATUS__,'b.sid'=>$sid,'b.lang'=>__LANG__])->asArray()->one();
	    if(empty($r)){
	        $r = static::find()
	        ->select(['a.*'])
	        ->from(['a'=>Shop::tableTemplate()])
	        ->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
	        ->where(['b.state'=>__TEMPLATE_DOMAIN_STATUS__,'b.sid'=>$sid])->asArray()->one();
	    }
	    //
	    if(empty($r) && __TEMPLATE_DOMAIN_STATUS__ > 1){
	        $r = static::find()
	        ->select(['a.*'])
	        ->from(['a'=>Shop::tableTemplate()])
	        ->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
	        ->where(['b.state'=>1,'b.sid'=>__SID__,'b.lang'=>__LANG__])->asArray()->one();
	        if(empty($r)){
	            $r = static::find()
	            ->select(['a.*'])
	            ->from(['a'=>Shop::tableTemplate()])
	            ->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
	            ->where(['b.state'=>1,'b.sid'=>$sid])->asArray()->one();
	        }
	    }
	    
	    return $r;
	}
	
	public function getTemplateName($cached =  true){
		defined('__TEMPLATE_DOMAIN_STATUS__') or define('__TEMPLATE_DOMAIN_STATUS__', 1);
		$config = Yii::$app->session->get('config');
		$c = __SID__ .'_'. PRIVATE_TEMPLATE;
		$params = [
		    __CLASS__,
		    __FUNCTION__,
		    __SID__,
		    PRIVATE_TEMPLATE,
		    __LANG__,
		    
		]; 
		//view2($cached);
		if($cached){
		    $r = Yii::$app->icache->getCache($params);
		    //view2($r);
		}

		if(0>1 && !YII_DEBUG && isset($config['template'][$c][__LANG__]['name']) && $config['template'][$c][__LANG__]['name'] != ""){
			return $config['template'][$c][__LANG__];
		}else{
			$r = [];
			if(PRIVATE_TEMPLATE>0){
				$r = static::find()
				->select(['a.*'])
				->from(['a'=>Shop::tableTemplate()])
				->where(['a.id'=>PRIVATE_TEMPLATE])->asArray()->one();

			}
			if(empty($r)){
				//
				$r = static::find()
				->select(['a.*'])
				->from(['a'=>Shop::tableTemplate()])
				->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
				->where(['b.state'=>__TEMPLATE_DOMAIN_STATUS__,'b.sid'=>__SID__,'b.lang'=>__LANG__])->asArray()->one();
				if(empty($r)){
					$r = static::find()
					->select(['a.*'])
					->from(['a'=>Shop::tableTemplate()])
					->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
					->where(['b.state'=>__TEMPLATE_DOMAIN_STATUS__,'b.sid'=>__SID__])->asArray()->one();
				}
				//
				if(empty($r) && __TEMPLATE_DOMAIN_STATUS__ > 1){
					$r = static::find()
					->select(['a.*'])
					->from(['a'=>Shop::tableTemplate()])
					->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
					->where(['b.state'=>1,'b.sid'=>__SID__,'b.lang'=>__LANG__])->asArray()->one();
					if(empty($r)){
						$r = static::find()
						->select(['a.*'])
						->from(['a'=>Shop::tableTemplate()])
						->innerJoin(['b'=>Shop::tableTemplateToShop()],'a.id=b.temp_id')
						->where(['b.state'=>1,'b.sid'=>__SID__])->asArray()->one();
					}
				}
			}
			
			
			
			Yii::$app->icache->store($r, $params);
			
			$config['template'][$c][__LANG__] = $r;
			Yii::$app->session->set('config', $config);
			return $r;
		}
	}
	
	
	public function getDomains($www_mode = false)
	{
	    $l = Shop::find()->from(Shop::tableDomain())->where(['sid' => __SID__])->asArray()->all();
	    
	    if($www_mode){
	        return $l;
	    }
	    
	    $ex = []; $data = [];
	    if(!empty($l)){
	        foreach ($l as $v){
	            $www = substr($v['domain'], 0, 4);
	            if($www == 'www.'){
	                continue;
	            }
	            
	            $data[] = $v;
	        }
	    }
	    
	    return $data;
	}
	
	public function getAllActiveShop(){
	    $l = static::find()->from(['a'=>Shop::tableName()])
	    ->innerJoin(['b'=>Shop::tableDomain()],'a.id=b.sid')
	    ->where(['>','a.state',0])
	    ->andWhere(['b.is_default'=>1])
	    ->select(['a.*','b.domain'])
	    ;
	    return $l->orderBy(['b.domain'=>SORT_ASC])->asArray()->all();
	}
	
	private $copyModules = [
	    'siteconfig',
	    'menu',
	    'filters',
        'content',
	    'box',
	    
	];
	
	public function copyData($from, $to, $replace = false){
	    $config = Yii::$app->db->getConfigs('COPY_DATA');
	    
	    foreach ($this->copyModules as $module){
	        $method = 'copy' . ucfirst($module);
	        if (!(isset($config[$module]) && $config[$module] = 1) && method_exists($this, $method)) {
	            $this->$method($from, $to, $replace);
	        }	        
	    }
	    
	}
	
	private function copySiteconfig($from, $to, $replace){
	    $state = true;
	    $table = 'site_configs';
	    $l = (new \yii\db\Query())->from($table)->where(['sid'=>$from])->all();
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            $state = true;
	            $code = $v['code']; $lang = $v['lang'];
        	    if(!$replace){
        	        if((new \yii\db\Query())->from($table)->where(['code'=>$v['code'], 'sid'=>$to, 'lang'=>$lang])->count(1) > 0){
        	            $state = false;
        	        }
        	    }
        	    if($state){
        	        $sql = "INSERT INTO site_configs (sid, code,lang, bizrule)
                            SELECT $to, '$code','$lang', bizrule FROM site_configs
                            WHERE sid=$from and code = '$code' and lang='$lang'";        	        
        	        Yii::$app->db->createCommand($sql)->execute();
        	    }
	    }}
	}
	
	private function copyBox($from, $to, $replace = false){
	    $state = true;
	    $table = 'box';
	    
	    $sql = "SELECT * FROM `$table` where sid=$from";
	    
	    $l = Yii::$app->db->createCommand($sql)->queryAll(null, false);
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            $state = true;
	            $old_id = $v['id'];
	            if($v['menu_id']>0){
	                
	                $v['menu_id'] = $this->getNewMenuIdFromOld($v['menu_id'], $from, $to);
	                
	                //view($v['menu_id']);
	            }
	            
	            unset($v['id']);
	            $v['sid'] = $to;
	            $lang = $v['lang'];
	            
	            $new = (new \yii\db\Query())->from($table)->where(['code'=>$v['code'], 'sid'=>$to, 'lang'=>$lang])->one();
	            
	            if(!$replace && !empty($new)){
	                $new_id = $new['id'];
	                if((new \yii\db\Query())->from($table)->where(['code'=>$v['code'], 'sid'=>$to, 'lang'=>$lang])->count(1) > 0){
	                    $state = false;
	                    //Yii::$app->db->createCommand()->update($table, $v,['id'=>$new_id])->execute();
	                }
	                
	            }
	            if($state){
	                $new_id = Yii::$app->zii->insert($table, $v);
	                $new = (new \yii\db\Query())->from($table)->where(['code'=>$v['code'], 'sid'=>$to, 'lang'=>$lang])->one();
	            }
	            
	            
	            if(isset($new['make_url']) && $new['make_url'] == 'on'){
	                
	               // $i = (new \yii\db\Query())->from('slugs')->where(['item_id'=>$old_id,'item_type'=>2, 'sid'=>$from])->one();
	                $sql = "SELECT * FROM `slugs` where sid=$from and item_id=$old_id and item_type=2";
	                
	                $i = Yii::$app->db->createCommand($sql)->queryOne(null, false);
	                
	                if(!empty($i) && (new \yii\db\Query())->from('slugs')->where(['url'=>$i['url'], 'sid'=>$to])->count(1) == 0){
	                    //$v2 = (new \yii\db\Query())->from('slugs')->where(['url'=>$v['url'], 'sid'=>$from])->one(null,true);
	                    //$sql3 = "SELECT * FROM `slugs` where sid=$from and url='".$v['url']."'";
	                    $v2 = $i;//Yii::$app->db->createCommand($sql3)->queryOne(null, false);
	                    $v2['item_id'] = $new_id;
	                    $v2['sid'] = $to;
	                    //view($v2);
	                    Yii::$app->db->createCommand()->insert('{{%slugs}}',$v2)->execute();
	                }
	            }
	        }
	    }
	}
	
	private function getNewMenuIdFromOld($old_id, $from,$to){
	    $table = 'site_menu';
	    $sql2 = "select id from $table where sid=$to and url = (select url from $table where sid=$from and id=$old_id)";
	    return Yii::$app->db->createCommand($sql2)->queryScalar();
	}
	
	private function copyMenu($from, $to, $replace = false){
	    $state = true;
	    $table = 'site_menu';
	    
	    $sql = "SELECT * FROM `$table` where sid=$from and state>-2 order by parent_id asc";
	    $l = Yii::$app->db->createCommand($sql)->queryAll(null, false);
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            $state = true;
	            $old_id = $v['id'];
	            unset($v['id']);
	            $v['sid'] = $to;
	            $lang = $v['lang'];
	            
	            if($v['parent_id']>0){
	                $sql2 = "select id from $table where sid=$to and url = (select url from $table where sid=$from and id=".$v['parent_id'].")";
	                $v['parent_id'] = Yii::$app->db->createCommand($sql2)->queryScalar();
	            }
	            
	            $new = (new \yii\db\Query())->from($table)->where(['url'=>$v['url'], 'sid'=>$to, 'lang'=>$lang])->one();
	            
	            
	            if(!$replace && !empty($new)){
	                
	                $new_id = $new['id'];
	                
	                if((new \yii\db\Query())->from($table)->where(['url'=>$v['url'], 'sid'=>$to, 'lang'=>$lang])->count(1) > 0){
	                    $state = false;
	                }
	            }
	            if($state){
	                $new_id = Yii::$app->zii->insert($table, $v);
	            }
	            
	            // Update position
	            $pos = (new \yii\db\Query())->from('{{%items_to_posiotion}}')->where(['item_id'=>$old_id])->all();
	            Yii::$app->db->createCommand()->delete('{{%items_to_posiotion}}',['item_id'=>$new_id])->execute();
	            
	            if(!empty($pos)){
	                foreach ($pos as $v2){
	                    $v2['item_id'] = $new_id;
	                    Yii::$app->db->createCommand()->insert('{{%items_to_posiotion}}',$v2)->execute();
	                }
	            }
	            
	            if((new \yii\db\Query())->from('slugs')->where(['url'=>$v['url'], 'sid'=>$to])->count(1) == 0){
	                //$v2 = (new \yii\db\Query())->from('slugs')->where(['url'=>$v['url'], 'sid'=>$from])->one(null,true);
	                $sql3 = "SELECT * FROM `slugs` where sid=$from and url='".$v['url']."'";
	                $v2 = Yii::$app->db->createCommand($sql3)->queryOne(null, false);
	                $v2['item_id'] = $new_id;
	                $v2['sid'] = $to;
	                //view($v2);
	                Yii::$app->db->createCommand()->insert('{{%slugs}}',$v2)->execute();
	            }
	            
	        }
	    }
	    
	}
	
	
	private function copyFilters($from, $to, $replace = false){
	    
	}
	
	private function copyContent($from, $to, $replace = false){
	    foreach ($this->getUserForms($from) as $value) {
	        $table = 'articles';
	        
	        $mn = (new \yii\db\Query())->from('site_menu')->where(['sid'=>$from,'type'=>$value['code']])->all();
	        if(!empty($mn)){
	            foreach ($mn as $m) {
	                $sql = "SELECT * FROM `$table` where type='".$value['code']."' and is_active=1 and sid=$from and state>-2 
and id in (select  item_id from items_to_category where category_id=${m['id']})
order by updated_at desc limit 15";
	                $l = Yii::$app->db->createCommand($sql)->queryAll(null, false);
	                
	                if(!empty($l)){
	                    foreach ($l as $v){
	                        $state = true;
	                        $old_id = $v['id'];
	                        unset($v['id']);
	                        $v['sid'] = $to;
	                        $lang = $v['lang'];
	                        
	                        if($v['category_id']>0){
	                            $sql2 = "select id from site_menu where sid=$to and url = (select url from site_menu where sid=$from and id=".$v['category_id'].")";
	                            $v['category_id'] = Yii::$app->db->createCommand($sql2)->queryScalar();
	                        }
	                        
	                        $new = (new \yii\db\Query())->from($table)->where(['url'=>$v['url'], 'sid'=>$to, 'lang'=>$lang])->one();
	                        
	                        
	                        if(!$replace && !empty($new)){
	                            
	                            $new_id = $new['id'];
	                            
	                            if((new \yii\db\Query())->from($table)->where(['url'=>$v['url'], 'sid'=>$to, 'lang'=>$lang])->count(1) > 0){
	                                $state = false;
	                            }
	                        }
	                        if($state){
	                            $new_id = Yii::$app->zii->insert($table, $v);
	                        }
	                        
	                        // Update category
	                        //$model = new \app\modules\admin\models\Content();
	                        $this->updateCategory($old_id, $new_id, $from,$to);
	                        $this->updateAttr($old_id, $new_id);
	                        $this->updateTab($old_id, $new_id);
	                        
	                        
	                        if((new \yii\db\Query())->from('slugs')->where(['url'=>$v['url'], 'sid'=>$to])->count(1) == 0){
	                            //$v2 = (new \yii\db\Query())->from('slugs')->where(['url'=>$v['url'], 'sid'=>$from])->one(null,true);
	                            $sql3 = "SELECT * FROM `slugs` where sid=$from and url='".$v['url']."'";
	                            $v2 = Yii::$app->db->createCommand($sql3)->queryOne(null, false);
	                            $v2['item_id'] = $new_id;
	                            $v2['sid'] = $to;
	                            //view($v2);
	                            Yii::$app->db->createCommand()->insert('{{%slugs}}',$v2)->execute();
	                        }
	                        
	                    }
	                }
	            }
	        }
	        
	        
	        
	        
	    }
	}
	private function updateTab($old, $new){
	    $table = 'tab_details';
	    $sql = "SELECT * FROM `$table` where item_id=$old";
	    $l = Yii::$app->db->createCommand($sql)->queryAll(null, false);
	    
	    if(!empty($l)){
	        foreach ($l as $v){
	            unset($v['id']);
	            $v['item_id'] = $new;
	            if((new \yii\db\Query())->from($table)->where(['item_id'=>$new])->count(1) == 0){
	               Yii::$app->db->createCommand()->insert($table,$v)->execute();
	            }
	        }
	    }
	}
	
	private function updateAttr($old, $new){
	    $table = 'articles_to_attrs';
	    $l = (new \yii\db\Query())->from(['a'=>$table])->where(['a.item_id'=>$old])->all();
	    Yii::$app->db->createCommand()->delete($table,['item_id'=>$new])->execute();
	    if(!empty($l)){
	        foreach ($l as $v){
	            $v['item_id'] = $new;
	            Yii::$app->db->createCommand()->insert($table,$v)->execute();
	        }
	    }
	}
	private function updateCategory($old, $new, $from, $to){
	    $table = 'items_to_category';
	    $l = (new \yii\db\Query())->from(['a'=>$table])->where(['a.item_id'=>$old])->all();
	    Yii::$app->db->createCommand()->delete($table,['item_id'=>$new])->execute();    
	    if(!empty($l)){
	        foreach ($l as $v){
	            
	            $c = $this->getNewMenuIdFromOld($v['category_id'], $from, $to);
	            if($c>0){
	            Yii::$app->db->createCommand()->insert($table,[
	                'item_id'=>$new,
	                'category_id'=>$c
	            ])->execute();    
	            }
	        }
	    }
	}
	
	private function getUserForms($sid = __SID__, $params=[]){
	    //$is_content = isset($params['is_content']) ? $params['is_content'] : -1;
	    
	    $temp = $this->getTemplateBySid($sid);
	    
	    if(!empty($temp)){
	        $query = (new \yii\db\Query())->from(['a'=>\app\modules\admin\models\AdForms::tableName()])->where(['a.is_active'=>1,'a.is_content'=>1]);
	        $query->andWhere(['a.id'=>(new \yii\db\Query())->select('form_id')->from(\app\modules\admin\models\AdForms::tableToTemp())->where([
	            'temp_id'=>$temp['parent_id'], 'type_id'=>0
	        ])]);
	        $query->orderBy(['a.position'=>SORT_ASC, 'a.title'=>SORT_ASC]);
	        
	        return $query->all();
	    }
	    
	}
}
