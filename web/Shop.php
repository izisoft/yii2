<?php
/**
 *
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\web;
use Yii;
class Shop extends \yii\base\Component
{
	/**
	 * Configs
	 * @return string
	 */
	public $config, $setting, $item, $info = [] , $category = [];

	public $hasAmp = false, $ampLayout = false, $is_api = false;

	public static $device = 'desktop', $is_mobile = false;


	public function getInfo($domain = __DOMAIN__, $cache = false){
	    
	    if($cache){
	        
	        $params = [
	            __CLASS__,
	            __FUNCTION__,
	            $domain,
	            date('H')
	        ];
	        
	        $r = Yii::$app->icache->getCache($params);
	        if(!empty($r)){
	            return $r;
	        }
	        $r = $this->getModel()->getDomainInfo();
	        Yii::$app->icache->store($r, $params);
	        return $r;
	    }
	    
	    return $this->getModel()->getDomainInfo();
	   
	}


	public function checkSuspended(){
		return false;
	}

	
	private $_model;
	
	public function getModel(){
	    if($this->_model == null){
	        $this->_model = Yii::createObject('izi\models\Shop');
	    }
	    
	    return $this->_model;
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

	public static function setTemplete(){
		$config = Yii::$app->session->get('config');
		$TEMP = self::getTempleteName();
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
			define('__IS_MOBILE_TEMPLETE__' , true );
			define('__MOBILE_TEMPLETE__' , '/' . self::$device  );
		}else {
			define('__IS_MOBILE_TEMPLETE__', false);
			define('__MOBILE_TEMPLETE__' , '' );
		}



		$app_path = Yii::getAlias('@app');
		$themePath = Yii::getAlias('@themes');

		switch (self::$device){
			case 'mobile':

				$dir = $themePath .'/' . __TEMP_NAME__ . __MOBILE_TEMPLETE__;
				$s = removeLastSlashes(\yii\helpers\Url::home()) . '/themes/'. __TEMP_NAME__ . __MOBILE_TEMPLETE__;

				if(!file_exists($dir)){

					$dir = $themePath .'/' . __TEMP_NAME__;
					$s = removeLastSlashes(\yii\helpers\Url::home()) . '/themes/'.__TEMP_NAME__.'';
					self::$is_mobile = false;
				}
				define('__RSPATH__',$dir);
				define('__RSDIR__',__IS_ADMIN__ ? \yii\helpers\Url::base() . '/themes/'.__TEMP_NAME__ : $s);
				break;
			default:

				$dir = $themePath .'/' . __TEMP_NAME__ . __MOBILE_TEMPLETE__;
				define('__RSPATH__',$dir);
				define('__RSDIR__',  \yii\helpers\Url::base() . '/themes/'.__TEMP_NAME__ . __MOBILE_TEMPLETE__);
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

	public static function getTempleteName($cached =  true){
		defined('__TEMPLETE_DOMAIN_STATUS__') or define('__TEMPLETE_DOMAIN_STATUS__', 1);
		$config = Yii::$app->session->get('config');
		$c = __SID__ .'_'. PRIVATE_TEMPLETE;

		if(!YII_DEBUG && isset($config['templete'][$c][__LANG__]['name']) && $config['templete'][$c][__LANG__]['name'] != ""){
			return $config['templete'][$c][__LANG__];
		}else{
			$r = [];
			if(PRIVATE_TEMPLETE>0){
				$r = static::find()
				->select(['a.*'])
				->from(['a'=>self::tableTemplete()])
				->where(['a.id'=>PRIVATE_TEMPLETE])->asArray()->one();

			}
			if(empty($r)){
				//
				$r = static::find()
				->select(['a.*'])
				->from(['a'=>self::tableTemplete()])
				->innerJoin(['b'=>self::tableTempleteToShop()],'a.id=b.temp_id')
				->where(['b.state'=>__TEMPLETE_DOMAIN_STATUS__,'b.sid'=>__SID__,'b.lang'=>__LANG__])->asArray()->one();
				if(empty($r)){
					$r = static::find()
					->select(['a.*'])
					->from(['a'=>self::tableTemplete()])
					->innerJoin(['b'=>self::tableTempleteToShop()],'a.id=b.temp_id')
					->where(['b.state'=>__TEMPLETE_DOMAIN_STATUS__,'b.sid'=>__SID__])->asArray()->one();
				}
				//
				if(empty($r) && __TEMPLETE_DOMAIN_STATUS__ > 1){
					$r = static::find()
					->select(['a.*'])
					->from(['a'=>self::tableTemplete()])
					->innerJoin(['b'=>self::tableTempleteToShop()],'a.id=b.temp_id')
					->where(['b.state'=>1,'b.sid'=>__SID__,'b.lang'=>__LANG__])->asArray()->one();
					if(empty($r)){
						$r = static::find()
						->select(['a.*'])
						->from(['a'=>self::tableTemplete()])
						->innerJoin(['b'=>self::tableTempleteToShop()],'a.id=b.temp_id')
						->where(['b.state'=>1,'b.sid'=>__SID__])->asArray()->one();
					}
				}
			}
			$config['templete'][$c][__LANG__] = $r;
			Yii::$app->session->set('config', $config);
			return $r;
		}
	}

	public function getLayouts(){
	    return [
	        "" =>  'Mặc định',
	        //"main" =>  'Main',
	        //"v2" =>  'V2',
	        
	        "bootstrap4" =>  'Bootstrap v4.1.3',
	        "bootstrap41x" =>  'Bootstrap v4.1.x (lastest)',
	        "bootstrap5" =>  'Bootstrap v5',
	        "bootstrap6" =>  'Bootstrap v6',
	        
	        ////
			"foundation5"	=>	'Foundation v5',
			"foundation6"	=>	'Foundation v6.5.0',
	        "foundation65x"	=>	'Foundation v6.5.x (lastest)',
	        "react" =>  'React (beta)',
	    ];
	}
	
	public function findTemplateByItemId($item_id){
	    $query = (new \yii\db\Query())->from(['a'=>'templates'])
	    ->innerJoin(['b'=>'item_to_template'],'a.name=b.temp_id')
	    ->where(['b.item_id'=>$item_id]);
	    return $query->one();
	}
	
	public function findTemplate($id){
	    return (new \yii\db\Query())->from('templates')->where(['id'=>$id])->one();
	}
	
	public function quickRegisterShop($params){
	    
	    // 1. Đăng ký user
	    $password = $params['password'] = randString(6);
	   // $user = ;
	    
	     
	    // 2. Đăng ký shops
	    if(Yii::$app->user->verifyRegisterUser($params)){
	        //
	        $temp_id = isset($params['temp_id']) ? $params['temp_id'] : 0;
	        
	        $item_id = isset($params['item_id']) ? $params['item_id'] : 0;
	        
	        $temp = $this->findTemplateByItemId($item_id);
	        
	        
	        
	        if(!!empty($temp)){
	            $temp = $this->findTemplate($temp_id);
	        }
	        
	        if(!empty($temp)){
	            $temp_category = $temp['parent_id'];
	        }else{
	            $temp_category = isset($params['temp_category']) ? $params['temp_category'] : 0;
	        }
	        
	        $d = [
	            'code'=>$this->getAutoCode(),
	            'parent_id'=>$temp_category,
	            'to_date'=>date('Y-m-d', mktime(0,0,0,date('m'),date('d') + 15, date('Y'))),
	            'last_modify'=>date('Y-m-d H:i:s')
	        ];
	        
	        //$d['code'] = 's4';
	        
	        Yii::$app->db->createCommand()->insert('shops', $d)->execute();
	        
	        $shop = $this->findItem($d['code']);
	        
	        $params['sid'] = $shop['id'];
	        
	        // Đăng ký user mới 
	        $user = Yii::$app->user->registerUser($params);
	        
	        // 3. Kết nối uid & sid + Gán quyền admin cho user vừa tạo
	        
	        if(!empty($shop)){
	            
	            $this->assignUser($user['id'], $shop['id']);
	            
	            $role = Yii::$app->authManager->createRole(Yii::$app->authManager->adminRole);  
	            	             	            
	            if(!!empty(Yii::$app->authManager->getAssignment(Yii::$app->authManager->adminRole, $user['id'], $shop['id']))){
	                Yii::$app->authManager->assign($role, $user['id'], $shop['id']);
	            }
	            	
	            if((new \yii\db\Query())->from('user_groups')->where([
	                'sid'=>$shop['id'],
	                'name'=>Yii::$app->authManager->adminRole
	            ])->count(1) == 0){
	                $gid = Yii::$app->zii->insert('user_groups', [
	                    'sid'=>$shop['id'],
	                    'name'=>Yii::$app->authManager->adminRole,
	                    'title'=>'Administrator'
	                ]);
	                Yii::$app->db->createCommand()->insert('user_to_group', [
	                    'user_id'=>$user['id'],
	                    'group_id'=>$gid
	                ])->execute();
	            }
	            
	            // 4. Set template
	            if((new \yii\db\Query())->from('temp_to_shop')->where([
	                'temp_id'=>$temp['id'],
	                'sid'=>$shop['id']
	            ])->count(1) == 0){
	                Yii::$app->db->createCommand()->insert('temp_to_shop', [
	                    'temp_id'=>$temp['id'],
	                    'sid'=>$shop['id'],
	                    'state'=>1
	                ])->execute();
	            }
	            
	            // 5. Point domain
	            $domain = 'cs' . $shop['id'] . '.iziweb.net';
	            if((new \yii\db\Query())->from('domain_pointer')->where([
	                'domain'=>$domain,
	                //'name'=>Yii::$app->authManager->adminRole
	            ])->count(1) == 0){
	                Yii::$app->db->createCommand()->insert('domain_pointer', [
	                    'sid'=>$shop['id'],
	                    'domain'=>$domain,
	                    'is_default'=>1
	                ])->execute();
	            }
	            
	            // 6. Đăng dữ liệu mẫu cho website mới
	            $this->copyData($this->getSidFromDomain($this->getDemoLink($item_id)), $shop['id']);
	            
	            // 7. Gửi thông tin tài khoản tới email khách hàng
	            
	            $fx = Yii::$app->contact;
	            $fx1 = Yii::$app->db->getConfigs('EMAILS_RESPON');
	            
	            $text1 = Yii::$app->zii->getTextRespon(['code'=>'RP_REGISTER_SHOP', 'show'=>false]);
	            $text2 = Yii::$app->zii->getTextRespon(['code'=>'RP_REGISTER_SHOP_ADMIN', 'show'=>false]);
	            
	            $regex = array(
	                '{LOGO}' => isset(Yii::$app->config['logo']['logo']['image']) ? '<img src="'.Yii::$app->config['logo']['logo']['image'].'" style="max-height:100px"/>' : '',
	                '{DOMAIN}' => __DOMAIN__,
	                '{{%DOMAIN}}' =>DOMAIN,
	                '{ORDER_TIME}' => date("d/m/Y H:i:s"),
	                '{{%TIME}}' => date("d/m/Y H:i:s"),
	                '{{%REGISTER_NAME}}' =>$user['name'],
	                '{{%ABSOLUTE_DOMAIN}}' => DOMAIN,
	                '{{%REGISTER_DOMAIN}}' => '<a target="_blank" href="http://'.$domain.'">' . $domain .'</a>' ,
	                '{{%REGISTER_DOMAIN_ADMIN}}' =>  $domain . '/admin',
	                '{{%REGISTER_EMAIL}}'=>$user['email'],
	                '{{%REGISTER_PHONE}}'=>$user['phone'],
	                '{{%REGISTER_TEL}}'=>$user['phone'],
	                '{{%REGISTER_PASSWORD}}'=>$password,
	                 
	                '{{%CLIENT_BROWSER}}'=> '',
	                '{{%CLIENT_DEVICE}}'=> '',
	                '{{%CLIENT_IP}}' => getClientIP()
	                
	            );
	            
	            $fx['sender'] = $fx['email'];
	            $fx['short_name']  = $fx['short_name'] != "" ? $fx['short_name'] : $fx['name'];
	            if(isset($fx1['RP_CONTACT'])){
	                $fx['email'] = $fx1['RP_CONTACT']['email'] != "" ? $fx1['RP_CONTACT']['email'] : $fx['email'];
	            }
	            
	            $form1 = replace_text_form($regex, ($text1['value']));
	            $form2 = replace_text_form($regex, ($text2['value']));
	            
	            if(Yii::$app->mailer->sentEmail(array(
	                'subject'=>replace_text_form($regex , $text1['title']) ."  (".date("H:i d/m/Y").")",
	                'body'=>$form1,
	                'from'=>$fx['email'],	                
	                'fromName'=>$fx['short_name'],
	                'replyTo'=>$fx['email'],
	                'replyToName'=>$fx['short_name'] ,
	                'to'=>$user['email'],'toName'=>$user['name']
	            ))){
	                Yii::$app->mailer->sentEmail(array(
	                    'subject'=>replace_text_form($regex , $text2['title']) ."  (".date("H:i d/m/Y").")",
	                    'body'=>$form2,
	                    'from'=>$user['email'],
	                    'fromName'=>$user['name'],
	                    'replyTo'=>$user['email'],
	                    'replyToName'=>$user['name'],
	                    'to'=>$fx['email'],'toName'=>$fx['short_name']
	                ));
	            }
	            
	            $notis = [
	                'title'=> $user['name']  . ' ('.$user['email'].') đăng ký dùng thử website.',
	                
	                //'uid'=>Yii::$app->user->id
	            ];
	            \app\models\Notifications::insertNotification($notis);
	            
	            $shop['domain'] = $domain;
	            $shop['user'] = $user;
	            
	            return $shop;
	        }
	        
	    }else{
	        return false;
	    }
	    
	    
	    
	    
	    return false;
	}
	
	
	public function copyData($from, $to, $replace = false){
	    return $this->getModel()->copyData($from, $to, $replace);
	}
	
	
	public function assignUser($user_id, $shop_id, $state = 1){
	    $table = 'user_to_shop';
	    $columns = [
	        'sid'=>$shop_id, 'user_id'=>$user_id, 'state'=>$state
	    ];
	    if((new \yii\db\Query())->from($table)->where(['sid'=>$shop_id, 'user_id'=>$user_id])->count(1) == 0){
	        Yii::$app->db->createCommand()->insert($table, $columns)->execute();
	    }
	}
	
	public function getAutoCode(){
	    $c = (new \yii\db\Query())->from('shops')->where(['like','code','s%',false])->count(1) + 1;
	    $code = "s$c";
	    while((new \yii\db\Query())->from('shops')->where(['code'=>$code])->count(1) > 0){
	        $code = "s" . (++$c);
	    }
	    return $code;
	}
	
	public function findItem($code){
	    return (new \yii\db\Query())->from('{{%shops}}')->where(['code'=>$code])->one();
	}
	
	public function getDemoLink($item_id){
	    $item = $this->getItemDetail($item_id);
	    if(!empty($item)){
	        return $item['demo_url']; 
	    }
	}
	
	public function getSidFromDomain($url){
	    $domain = (parse_url($url, PHP_URL_HOST));
	   
	    $s = (new \yii\db\Query())->from('domain_pointer')->where(['domain'=>$domain])->one();
	    if(!empty($s)){
	        return $s['sid'];
	    }
	    return 0;
	}
	
	public function getItemDetail($item_id){
	    return (new \yii\db\Query())->from('articles')->where(['id'=>$item_id])->one();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
