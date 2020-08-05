<?php 

namespace izi\config;

use Yii;
use app\models\SiteConfigs;

class Config extends \yii\base\Component
{
    public $config_key = 'SITE_CONFIGS';
    
    public $view;
    /**
     * Global config for all module
     */
    private $_app;
    
    /**
     * Seo config
     */
    private $_seo;
    
    public $param = [];
    
    
    public function init()
    {
        $this->view = Yii::$app->view;
    }
    
    
    public function setSeoValue($field, $value)
    {
        $this->_seo[$field] = $value;
    }
    
    /* public function validateSlugs($slug){
        
        if(isset($slug['checksum']) && $slug['checksum'] != ""
            && $slug['checksum'] != md5(URL_PATH)){
                // báo link sai & chuyển về link mới
                $url1 = \izi\models\Slug::getUrl($slug['url']);
                if(md5($url1) == $slug['checksum']){
                    $this->getResponse()->redirect($url1,301);
                    //Yii::$app->response->redirect($r['url'], $r['code']);
                    Yii::$app->end();
                }
        }
    } */
    
    public function validateUrl($slug)
    {
        $slug = (array)$slug;
        
//         view($slug);
        
//         if(empty($slug)) return;
        
         
        /**
         * 
         * @var Ambiguous $r
         */
        
         
        
        if(isset($slug['checksum']) && $slug['checksum'] != ""
            && $slug['checksum'] != md5(URL_PATH)){
                // báo link sai & chuyển về link mới
                $url1 = \izi\models\Slug::getUrl($slug['url']);
                if(md5($url1) == $slug['checksum']){
                    Yii::$app->response->redirect($url1,301);
                    Yii::$app->end();
                }
        }
        
        /**
         * 
         * @var Ambiguous $r
         */
        
        $params = [
            __METHOD__,
            $slug,
            URL_PATH
        ];
        
        
        if(Yii::$app->request->isGet){
        
            $r = !YII_DEBUG ? Yii::$app->icache->getCache($params) : null;                       
            
            if(empty($r)){
                
                $r = $this->getRedirectUrl($slug);
            }            
            
            Yii::$app->icache->store($r, $params);
            
            if($r['validate'] && getAbsoluteUrl($r['url']) != getAbsoluteUrl(URL_PATH)){
                
                Yii::$app->response->redirect($r['url'], $r['code']);
                Yii::$app->end();
            }
        }
    }
    
    private function getRedirectUrl($slug){
        // check redirect domain
        
        // redirect all link to new domain
        $rule = ['^' . DOMAIN, DOMAIN . '/*',
            //'@/*'
            
            
        ];
         
        
        $table = '{{%redirects}}';
        
        $validate = false; $code = 301;
        
        $r = \izi\db\ActiveRecord::populateData((new \yii\db\Query())
        ->from($table)
        ->where([
            
            'rule'=>$rule,
            'is_active'=>1,
            'sid'=>__SID__,
            
        ])->one());
        
        
        
        if(!empty($r) && $r['target'] != "" && $r['target'] != $rule){
            
            //$url = SCHEME . '://' . substr($r['target'], 1) . URL_PORT . URL_PATH;                            
            
            $new_url = substr($r['target'], 0, 1) == '^' ? substr($r['target'], 1) : $r['target'];
            
            if(validate_domain($new_url) && stripos($new_url, 'http://') === false && stripos($new_url, 'https://') === false){
                $new_url = SCHEME . "://$new_url";
            }
            
            $new_url = str_replace(['/$1', '$1'], [URL_PORT . URL_PATH], $new_url);      
             
             
            return [
                'url'=>$new_url,
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
                
                $r = \izi\db\ActiveRecord::populateData((new \yii\db\Query())->from($table)->where(['rule'=>[$slug['url'],FULL_URL],'state'=>1,'is_active'=>1,'sid'=>__SID__])->one());
              
                if(!empty($r) && $r['target'] != ""){
                    return [
                        'url'=>$r['target'],
                        'code'=>$r['code'],
                        'validate'=>true
                    ];
                }
            }
        }
        elseif(defined('__DETAIL_URL__')){
            $rule = __DETAIL_URL__ == '' ? '@' : __DETAIL_URL__;
            
            
            
            $r = \izi\db\ActiveRecord::populateData((new \yii\db\Query())
                ->select(['target', 'rule', 'code', 'bizrule'])
                ->from($table)->where(['rule'=>[$rule, URL_WITH_PATH],'is_active'=>1,'state'=>1,'sid'=>__SID__])->one());
            
//             view($r);exit;
            
//             view((new \yii\db\Query())
//                 ->from($table)->where(['rule'=>[$rule, URL_WITH_PATH],'is_active'=>1,'state'=>1,'sid'=>__SID__])->createCommand()->getRawSql(),1,1);
            
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
    
    
    public function getSeo()
    {
        if($this->_seo == null){
            $this->setupSeoConfig();
        }
        return $this->_seo;
    }
    
    public function setupSeoConfig()
    {
        $seo = \app\models\SiteConfigs::getConfigs('SEO2', null);
        
//         view($seo);
        
        if(empty($seo)){
            $seo = \app\models\SiteConfigs::getConfigs('SEO');
            
            if(isset($seo['domain_type']) && $seo['domain_type'] == 'multiple'){
                $domains = isset($seo['domain']) && $seo['domain'] != '' ? explode(',', $seo['domain']) : [];
                $sd = [];
                
                if(!empty($domains)){
                    foreach ($domains as $domain){
                        
                        $dm = str_replace('www.', '', $domain);
                        
                        if($domain == DOMAIN){
                            if(isset($seo[$domain])){
                                $sd = $seo[$domain];
                                unset($seo[$domain]);
                            }
                        }elseif($dm == DOMAIN_NOT_WWW){
                            if(isset($seo[$dm])){
                                $sd = $seo[$dm];
                                unset($seo[$dm]);
                            }
                        }else{
                            if(isset($seo[$domain])){
                                unset($seo[$domain]);
                            }
                        }
                    }
                }
                $seo = array_merge($sd,$seo);
                
            }
        }else{
            
            $value = isset($seo[DOMAIN_NOT_WWW]) ? $seo[DOMAIN_NOT_WWW] : [];
            $seo = array_merge($value, $seo);
             
            
        }
         
        
        $seo['page_seo'] = $seo;
        

        if(!empty($item = $this->view->item)){ // Detail
//             if(isset($item->seo['title']) && $item->seo['title'] != ""){
//                 $seo['title'] = $item->seo['title'];
//             }else{
//                 $seo['title'] = $item->title;
//             }
            
        }elseif(!empty($item = $this->view->category)){ // Category
                        
            
        }
        
        
        if(!empty($item)){
            // Set title
                  
            $seo['title'] = isset($item->seo['title']) && $item->seo['title'] != "" ? $item->seo['title'] : (isset($item->title) ? $item->title : '');
            
            
            // Set description
            $seo['description'] = isset($item->seo['description']) && $item->seo['description'] != "" ?
            $item->seo['description'] : (isset($item->info) && $item->info != "" ? $item->info
                : (isset($item->summary) && $item->summary != "" ? $item->summary : ''));
            
            // Set keyword
            $seo['keyword'] = (isset($item->seo['focus_keyword']) && $item->seo['focus_keyword'] != "" ?
                
                $item->seo['focus_keyword'] . ',' : (isset($item->focus_keyword) && $item->focus_keyword != "" ?
                    $item->focus_keyword . ',' : '') ) . (isset($item->seo['keyword']) && $item->seo['keyword'] != "" ?
                        $item->seo['keyword'] :'');
            
            // Set og_image  
            $seo['og_image'] = isset($item->icon) ? $item->icon : '';
        }
        
        
        
        // Set before & after

        if(isset($seo['before_title']) && $seo['before_title'] != ""){
            $seo['title'] = trim($seo['before_title']) . " ${seo['title']}";
        }
        
        if(isset($seo['after_title']) && $seo['after_title'] != ""){
            $seo['title'] = "${seo['title']} " . trim($seo['after_title']);
        }
        
        
        // Check amp
        if(isset($seo['amp']) && is_array($seo['amp']) && in_array(Yii::$app->controller->id, $seo['amp'])){
            $this->view->hasAmp = true;
        }
        
         
        
        $this->setConfig('_seo', $seo);
    }
    
    
    private $_adminConfigs;
    
    public function getAdminConfigs()
    {
        if($this->_adminConfigs == null){
            
            $key = 'ADMIN_CONFIGS';
            
            $cfg = \app\models\SiteConfigs::getConfigs($key);
            if(empty($cfg)){
                $cfg = \app\models\SiteConfigs::getConfigs($key, null);
            }
            
            $this->_adminConfigs = $cfg;
            
        }
        return $this->_adminConfigs;
    }
    
    public function getApp()
    {
        if($this->_app == null){
            $this->setupAppConfig();
        }
        return $this->_app;
    }
	
    public function setupAppConfig()
    {
        $cfg = \app\models\SiteConfigs::getConfigs($this->config_key);
        
        if(empty($cfg)){
            $cfg = \app\models\SiteConfigs::getConfigs($this->config_key, null);
        }
         
        
        if(isset($cfg['logo']) && !empty($cfg['logo'])){
            foreach ($cfg['logo'] as $key => $value) {
                $cfg[$key] = $value;
            }
        }
        
        if(isset($cfg['logo2']) && !empty($cfg['logo2'])){
            foreach ($cfg['logo2'] as $key => $value) {
                $cfg[$key] = $value;
            }
            unset($cfg['logo2']);
        }
        
        $this->setConfig('_app', $cfg);
    }
    
	
	// Set config variable 
    public function setConfig($config, $value)
    {
        $this->{$config} = $value;
    }
    
    
    
    /**
     * validate www mode
     */
    private $www = false; 
    
    
    private $_redirect = false , $_urlRedirect = null; 
    
    public function setRedirect($value)
    {
        $this->_redirect = $value;
    }
    
    public function setUrlRedirect($value)
    {
        $this->_urlRedirect = $value;
    }
    
    private function setupWwwMode(){
         
        $www = isset($this->getSeo()['www']) ? $this->getSeo()['www'] : -1;
        
        if(!isset($this->_seo['amp'])) {
            $this->_seo['amp'] = [];
        }
        
        switch ($www){
            case 0:
                if(strpos(ABSOLUTE_DOMAIN, 'www.') !== false){
                    $this->www = '';
                    $this->_redirect = true;
                }
                break;
            case 1:
                if(strpos(ABSOLUTE_DOMAIN, 'www.') === false){
                    $this->_redirect = true;
                    $this->www = 'www.';
                }
                break;
        }
        
        defined('WWW') or define('WWW', $this->www);
    }
    
    /**
     * validate https
     */
    private $scheme = 'http';
    
    public function setupHttpsMethod(){
        
        
        
        $this->setupWwwMode();
      
         
        
        if(isset($this->_seo['ssl'])){
            if(isset($this->_seo['ssl'])  && $this->_seo['ssl']== 1){
                
                $this->scheme = 'https';
                
                if(SCHEME == 'http'){
                    $this->_redirect = true;
                }
                
                
            }else{
                
                $this->scheme = 'http';
                if(SCHEME == 'https'){                    
                    $this->_redirect = true;
                }
                
            }
        }
        
        //
         
       
        if($this->_redirect){
            
            if($this->_urlRedirect != null){
                $u = parse_url($this->_urlRedirect);
                $redirect = $this->scheme  . '://' . ($this->www === false ? DOMAIN : $this->www . URL_NON_WWW ) . URL_PORT
                
                . (isset($u['path']) ? $u['path'] : '') 
                . (isset($u['query']) ? '?' . $u['query'] : '') ;
                                 
            }else{
            
                $redirect = $this->scheme  . '://' . ($this->www === false ? DOMAIN : $this->www . URL_NON_WWW ) . URL_PORT 
                . URL_PATH . (URL_QUERY != '' ? '?' . URL_QUERY : '');            
            }
            
            $this->setUrlRedirect($redirect);
            
  
            if($this->_urlRedirect != FULL_URL && !in_array(Yii::$app->controller->id, ['ajax', 'sajax'])){
                                                 
                Yii::$app->getResponse()->redirect($this->_urlRedirect, 301);
                Yii::$app->end();
            }
        }
        //
    }
    
    
    /**
	*	Social config
	*/

	private $_social;
		
    public function getSocial()
	{		
		if($this->_social == null){
			
			if(isset($this->app['socials'])){
				$socials = $this->app['socials'];
			}else{				
				$socials = new \stdClass();
			
			// Facebook
			$socials->facebook = [
				'app'	=>	[
					'id'		=>	'1729388797358505',
					'version'	=>	'v3.2'
				],
				'page'	=>	'',
				'group'	=>	'',
			];
			
			// Google
			$socials->google = [
			    'app'	=>	[
			        'client_id'		=>	'1047088189978-8ntndtsc0jlflgj57gq4qf2amc1mcdmc.apps.googleusercontent.com',
			        'client_secret'	=>	'wX9R0gf_iXSSdjE23D9WDmQ3',
			        'developer_key'   =>  'AIzaSyDfEH5PN67lMUTj5hl7kyxGqcrJ-V3VdqI'
			    ],
// 			    'page'	=>	'',
// 			    'group'	=>	'',
			];
			
			// Twitter
			
			// LinkedIn
			
			// Youtube
			
			// Pinterest 
			
			// Instagram
			
			// Flickr 
			
			// Tumblr 
			
			// Google Plus
			
			// Slide Share
			
			// Flipboard 
			
			// Viber
			
			// Zalo
			
			// Wechat 
			
			// Line
			
			// Whatsapp 
			
			// Myspace 
			
			// Digg
			
			// Reddit 
			
			// Weibo 
			
			// Viadeo 
			
			// Xing
			
			// QQ
			
			// Qzone 
			
			// Baidu Tieba
			
			}
			
			$this->setConfig('_social', $socials);
		}
		return $this->_social;	
	}
    
	
    
    /**
     * Get contact
     * Key: CONTACTS
     */
    private $_contact;
    
    public function getContact()
    {
        if($this->_contact == null){
            $this->_contact = SiteConfigs::getConfigs('CONTACTS');
            if(empty($this->_contact)){
                $this->_contact = SiteConfigs::getConfigs('CONTACTS',null);
            }
        }
        return $this->_contact;
    }
    
    
    /**
     * Get recive feedback
     * Key: EMAIL_SETTINGS
     */ 
    private $_emailSettings;
    
    public function getEmailSettings($field = null)
    {
        if($this->_emailSettings == null){
            $settings = SiteConfigs::getConfigs('EMAIL_SETTINGS');
            
            if(empty($settings)){
                $settings = SiteConfigs::getConfigs('EMAIL_SETTINGS', null);
            }
            
            /**
             * return [
             *      'ORDER_INFOMATION'      =>  [
             *          0 => [
             *              'name'  =>  'A Tỉn',
             *              'email' =>  'atin@iziweb.vn',
             *              'phone' =>  '0898280588'
             *          ],
             *          1 => [
             *              'name'  =>  'A Xỉn',
             *              'email' =>  'axin@iziweb.vn',
             *              'phone' =>  '0898280588'
             *          ]
             *      ],
             *      'CONTACT_INFOMATION'      =>  [
             *          0 => [
             *              'name'  =>  'A Tỉn',
             *              'email' =>  'atin@iziweb.vn',
             *              'phone' =>  '0898280588'
             *          ],
             *          1 => [
             *              'name'  =>  'A Xỉn',
             *              'email' =>  'axin@iziweb.vn',
             *              'phone' =>  '0898280588'
             *          ]
             *      ],
             * 
             */
            
            $this->_emailSettings = $settings;
        }
        
        if($field != null){
            return isset($this->_emailSettings[$field]) ? $this->_emailSettings[$field] : null;
        }
        
        return $this->_emailSettings;
    }
    
    
    
    
    public function getEmailRespon($params)
    {
        // 
        $respon_code = isset($params['respon_code']) ? $params['respon_code'] : '';
        $respon_code2 = isset($params['respon_code2']) ? $params['respon_code2'] : '';
        
        $fx = $this->contact;        
        $fx['sender'] = $fx['email'];
        $fx['short_name']  = $fx['short_name'] != "" ? $fx['short_name'] : $fx['name'];
        
        $fx1 = $this->emailSettings;
        
        
        if(isset($fx1[$respon_code]) && is_array($fx1[$respon_code])){
            $fx['email'] = $fx1[$respon_code];
        }else{
            $fx1 = \app\models\SiteConfigs::getConfigs('EMAILS_RESPON');
            if(isset($fx1[$respon_code2]) && isset($fx1[$respon_code2]['email'])){
                $fx['email'] = $fx1[$respon_code2]['email'];
            }            
        }
        
        
        return $fx;
    }
    
    
    public function getUserConfig($key)
    {
        $cfg = \app\models\SiteConfigs::getConfigs($key);
        
        return $cfg;
    }
    
    public function setUserConfig($key, $value, $replace = false)
    {
        \app\models\SiteConfigs::updateData($value, [
            'sid'=>__SID__,
            'code'=>$key,
            'lang'=>__LANG__
        ], $replace);
        
        return $this;
    }
    
    
    
    
    
}