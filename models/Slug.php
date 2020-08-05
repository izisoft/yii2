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
class Slug extends \yii\db\ActiveRecord
{

	public static function tableName(){
		return '{{%slugs}}';
	}


	public static function tableSiteMenu(){
		return '{{%site_menu}}';
	}


	public static function tableItemToCategory(){
		return '{{%items_to_category}}';
	}

	public static function tableAdminMenu(){
		return '{{%admin_menu}}';
	}


	public static function tableArticle(){
		return '{{%articles}}';
	}

	public static function tableRedirect(){
		return '{{%redirects}}';
	}

	public static function findUrl($url = ''){
		return static::find()->where(['url'=>$url,'sid'=>__SID__])->asArray()->one();
	}

	public static function setRedirect($slug){

	    $r = self::getRedirect($slug);
		   
	    if($r['validate'] && getAbsoluteUrl($r['url']) != getAbsoluteUrl(URL_PATH)){
				//Yii::$app->getResponse()->redirect();
	        //header("Location:".$r['url'],true,$r['code']);
					//exit;
					Yii::$app->response->redirect($r['url'], $r['code']);
          Yii::$app->end();
	    }
	}

	private static function getRedirect($slug){
		// check redirect domain
		$rule = '^' . DOMAIN;

		$validate = false; $code = 301;

		$r = (new \yii\db\Query())
		->from(self::tableRedirect())
		->where(['rule'=>$rule,'is_active'=>1,'sid'=>__SID__])->one();
		if(!empty($r) && $r['target'] != "" && $r['target'] != $rule){
			$url = SCHEME . '://' . substr($r['target'], 1) . URL_PORT . URL_PATH;
			return [
			    'url'=>$url,
			    'code'=>$r['code'],
			    'validate'=>true
			];
		}

		if(!empty($slug)){

			//$s =  json_decode($slug['redirect'],1);
		    $s =  isset($slug['seo']['redirect']) ? $slug['seo']['redirect'] : [];

		    if(isset($s['target']) && $s['target'] != ""
		       // && $s['target'] != URL_PATH
		        ){
		        return [
		            'url'=>$s['target'],
		            'code'=>$s['code'],
		            'validate'=>true
		        ];

			}else{

				$r = (new \yii\db\Query())->from(self::tableRedirect())->where(['rule'=>[$slug['url'],FULL_URL],'state'=>1,'is_active'=>1,'sid'=>__SID__])->one();

				if(!empty($r) && $r['target'] != ""){
					return [
					    'url'=>$r['target'],
					    'code'=>$r['code'],
					    'validate'=>true
					];
				}
			}
		}
		else{
			$rule = __DETAIL_URL__ == '' ? '@' : __DETAIL_URL__;
			//view($rule);
			$r = (new \yii\db\Query())
			->from(self::tableRedirect())->where(['rule'=>[$rule,FULL_URL],'is_active'=>1,'state'=>1,'sid'=>__SID__])->one();
		//	view((new \yii\db\Query())
		//	->from(self::tableRedirect())->where(['rule'=>[$rule,FULL_URL],'is_active'=>1,'sid'=>__SID__])->createCommand()->getRawSql());
			if(!empty($r) && $r['target'] != ""){
				return [
				    'url'=>$r['target'],
				    'code'=>$r['code'],
				    'validate'=>true
				];
			}

		}

		return ['validate'=>false];
	}
	/**
	 *
	 */

	public function getAll(){
		$query = static::find()
		->from(['a'=>$this->tableName()])
		->where(['a.sid'=>__SID__])
		->andWhere(['>','a.state',-2]);
		return $query->asArray()->all();
	}

	public static function getItem($url = '', $item_id = 0,$item_type = 0){
		$query = static::find()
		//->select(['route'])
		//->from(self::tableName())
		->where(['sid'=>__SID__]);
		if($url != '' ){
			$query->andWhere(['url'=>$url]);
		}else{
			if($item_type == -1){
				$item_type = defined('__IS_DETAIL__') && __IS_DETAIL__ ? 1 : 0;
			}
			$query->andWhere(['item_id'=>$item_id, 'item_type'=>$item_type]);
		}

		$item = $query->asArray()->one();
		if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
			$item += $content;
			unset($item['bizrule']);
		}
		return $item;
	}

	public function getRoute($url = '', $item_id = 0,$item_type = 0){
		$query = (new Query())
		->select(['route'])
		->from($this->tableName())
		->where(['sid'=>__SID__]);
		if($url != '' ){
			$query->andWhere(['url'=>$url]);
		}else{
			if($item_type == -1){
				$item_type = defined('__IS_DETAIL__') && __IS_DETAIL__ ? 1 : 0;
			}
			$query->andWhere(['item_id'=>$item_id, 'item_type'=>$item_type]);
		}

		return $query->scalar();
	}
	/*
	 *
	 */

	public static function getAllParent($id = 0,$inc = true){

		$item = static::find()->from([self::tableSiteMenu()])->where(['id'=>$id])->asArray()->one();

		if(!empty($item)){
			$query = static::find()->from([self::tableSiteMenu()])->select(['*'])->where([
					'<=','lft',$item['lft']
			])->andWhere([
					'>=','rgt',$item['rgt']
			])->andWhere(['sid'=>__SID__]);
			if(!$inc){
				$query->andWhere(['not in','id',$id]);
			}
			return $query->orderBy(['lft'=>SORT_ASC])->asArray()->all();
		}
		return false;
	}


	/**
	 * Lấy domain đc chỉ định hoặc domain đầu tiên trong danh sách domain của site
	 * @param string $domain
	 * @return string|mixed
	 */
	 
	
	public static function getDomain($domain = ''){
	    
	    if($domain == ''){
	        $domains = isset(Yii::$app->cfg->seo['domains']) ? Yii::$app->cfg->seo['domains'] : [];
	        
	        if(!empty($domains)){
	            if(in_array(DOMAIN_NOT_WWW, $domains)){
	                $d = DOMAIN_NOT_WWW;
	            }else{
	                $d = $domains[0];
	            }
	        }else{
	            $d = DOMAIN_NOT_WWW;
	        }
	        
	    }else {
	        $d = $domain;
	    }
	    
	    if(strpos($d, '://') === false){
	        $s = Yii::$app->cfg->seo;
	        
	        $w = str_replace('www.', '', $d);
	        
	        if(isset($s[$w]['ssl']) && $s[$w]['ssl'] == 1){
	            $scheme = 'https';
	        }else{
	            $scheme = SCHEME;
	        }
	        
	        $www = isset($s[$w]['www']) && $s[$w]['www'] == 1 ? 'www.' : '';
	        
	        $d = $scheme . '://' . $www . $w;
	    }
	    return $d;
	}

	/**
	 * Kiểm tra url hợp lệ
	 */

	public static function validateSlug($slug){
		if(isset($slug['checksum']) && $slug['checksum'] != ""
				&& $slug['checksum'] != md5(URL_PATH)){
			// báo link sai & chuyển về link mới
			$url1 = self::getUrl($slug['url']);

			if(md5($url1) == $slug['checksum']){
				Yii::$app->getResponse()->redirect($url1,301);
			}
		}
	}

	/**
	 * Lấy link cố định (đã được tùy chỉnh) của url
	 * @param unknown $url
	 * @param number $item_id
	 * @param unknown $item_type
	 * @param string $domain
	 * @return boolean|string
	 */
	public function getDirectLink($url, $item_id=0, $item_type=null, $domain = ''){

		if(!($item_id>0 && $item_type !== null)){
			$item = $this->getItem($url);
			if(!empty($item)){
				$item_id = $item['item_id'];
				$item_type = $item['item_type'];
			}else{
				return false;
			}
		}

		switch ($item_type){
			case 0: // menu
				$tables = $this->tableSiteMenu();
				break;
			case 1: // bai viết
				$tables = $this->tableArticle();
				break;
			default:
				if(!(substr($url, 0,1) == '/')){
					$url = '/' . $url;
				}
				return $this->getDomain($domain) . $url;
				break;
		}
		$c = static::find()->from($tables)->select('url_link')->where(['id'=>$item_id])->asArray()->one();

		$url = isset($c['url_link']) ? $c['url_link'] : $url;

		if(strpos($url, '://')>0){
			return $url;
		}
		if(!(substr($url, 0,1) == '/')){
			$url = '/' . $url;
		}
		return $this->getDomain($domain) . $url ;
	}

	/**
	 * Lấy link chuẩn theo cấu hình url của site
	 * @param string $url
	 * @param string $absolute
	 * @return string
	 */
	public static function getUrl($url = '',$o = false){

		$domain = isset($o['domain']) ? $o['domain'] : false;
		$absolute = isset($o['absolute']) && $o['absolute'] ? true : (!is_array($o) ? $o : false);
		$url_type = isset($o['url_type']) ? $o['url_type'] :
		(isset(Yii::$app->settings['url_manager']['type']) ? Yii::$app->settings['url_manager']['type'] :
		(isset(Yii::$app->config['seo']['url_config']['type']) ?  Yii::$app->config['seo']['url_config']['type'] : 2));
		$url_link = "/$url";
		if($url_type != 2){
			$item = self::getItem($url);
			if(!empty($item)){
				if($item['item_type'] == 0) {// menu
					$item_id = $item['item_id'];
				}else{
					$item_id = static::find()->select('category_id')->from(self::tableItemToCategory())->where(['item_id'=>$item['item_id']])->scalar();
				}

				switch ($url_type){
					case 1: // Full
						$c = [];
						foreach (self::getAllParent($item_id) as $k=>$v){
							$c[] = $v['url'];
						}
						if($item['item_type'] == 1) {
							$c[] = $url;
						}
						$url_link = "/" . implode('/', $c);
						break;
					case 3: // 1 cate
						$c = [static::find()->select('url')->from(self::tableSiteMenu())->where(['id'=>$item_id])->scalar()];
						if($item['item_type'] == 1) {
							$c[] = $url;
						}
						$url_link = '/' . implode('/', $c);
						break;
					default:
						$url_link = '/'. $item['url'];
						break;
				}


			}
		}
		if($domain !== false){
			return self::getDomain($domain) . \yii\helpers\Url::to($url_link);
		}
		return \yii\helpers\Url::to([$url_link],$absolute);
	}


	public static function getItemCategory($item_id){
		$item = static::find()
		->select('a.*')
		->from(['a'=>self::tableSiteMenu()])
		->innerJoin(['b'=>self::tableItemToCategory()],'a.id=b.category_id')->where(['b.item_id'=>$item_id])->asArray()->one();
		if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
			$item += $content;
			unset($item['bizrule']);
		}
		return $item;
	}

	public static function getCategory($id){
		$item = static::find()
		->select('a.*')
		->from(['a'=>self::tableSiteMenu()])
		->where(['a.id'=>$id])->asArray()->one();
		if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
			$item += $content;
			unset($item['bizrule']);
		}
		return $item;
	}

	public static function getRootItem($item = []){
		if(is_numeric($item)){
			$item = self::getCategory($item);
		}

		if(isset($item['parent_id']) && $item['parent_id'] == 0){
			return $item;
		}else{
			$item = static::find()
			->select('a.*')
			->from(['a'=>self::tableSiteMenu()])
			->where(['a.sid'=>__SID__,'a.parent_id'=>0])
			->andWhere(['<','a.lft',$item['lft']])
			->andWhere(['>','a.rgt',$item['rgt']])
			->asArray()->one();
			if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
				$item += $content;
				unset($item['bizrule']);
			}
			return $item;
		}


	}

	public static function adminFindByUrl($url = ''){
	    return static::find()
	    ->from('{{%admin_menu}}')
	    ->where(['url'=>$url])->asArray()->one();
	}
	public static function checkExistedChild($id){
	    return ((new \yii\db\Query())->from('{{%admin_menu}}')->where(['parent_id'=>$id,'is_active'=>1])->count(1)>0) ? true : false;
	}
	
	public function getAllSitemapUrl($params = []){
	    $query = static::find()
	    ->from(['a'=>Slug::tableName()])
	    ->where(['a.sid'=>__SID__,'is_active'=>1])
	    ->andWhere(['>','a.state',-2])
	    ->andWhere(['not in','a.rel',['nofollow']])
	    ->andWhere(['not in','a.url',['','#','/']])
	    ->andWhere(['not in','a.route',['','#','/']]);
	    
	    
	    return $query->asArray()->all();
	}

	
	public function verifyUrl($url){
	    $slug = $this->getItem($url);
	    $status = 0;
	    if(!empty($slug)){
	        switch ($slug['item_type']) {
	            case 0: // Menu
	                $r = \izi\models\Slug::getCategory($slug['item_id']);
	                if(!empty($r)){
	                    if($r['state']<-2){
	                        return $r['state'];
	                    }
	                    
	                    if($r['is_active'] == 0){
	                        return 0;
	                    }
	                    return 1;
	                }
	            break;
	            
	            
	        }
	    }
	    return $status;
	}
}
