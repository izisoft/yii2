<?php
namespace izi\router2;

use Yii;

class UrlManager extends \yii\web\UrlManager
{
    private $_frontendModules = [
        'member'
    ];
    
    /**
     * Setup model class
     */
    private $_model ;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject(DbRouter::class);
        }
        return $this->_model;
    }
    
    
    private $_moduleModel ;
    
    public function getModuleModel(){
        if($this->_moduleModel == null){
            
            
            $class_name = "\\app\\modules\\{$this->_router['module']}\\Request";
            
            if(class_exists($class_name)){
                $this->_moduleModel = Yii::createObject($class_name);
            }else{
                
                $class_name = __NAMESPACE__ . '\\' . ucfirst($this->_router['module']) . 'Model';
                
                
                if(class_exists($class_name)){
                    $this->_moduleModel = Yii::createObject($class_name);
                }else{
                    $this->_moduleModel = Yii::createObject(ModuleModel::class);
                }
                
            }
        }
        return $this->_moduleModel;
    }
    
    /**
     * Parse $_SERVER
     * @return string[]|unknown[]|mixed[]|NULL[]
     */
    
    protected function getServerInfo(){
        $s = $_SERVER;
        //$ssl = (isset($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:
        
        //(isset($s['HTTP_X_FORWARDED_PROTO']) && strtolower($s['HTTP_X_FORWARDED_PROTO']) == 'https' ? true : false);
        
        $ssl = false;
        
        if(isset($s['HTTPS']) && $s['HTTPS'] == 'on'){
            $ssl = true;
        }elseif(isset($s['HTTP_X_FORWARDED_PROTO']) && strtolower($s['HTTP_X_FORWARDED_PROTO']) == 'https'){
            $ssl = true;
        }else{
            $ssl = (isset($s['SERVER_PORT']) && $s['SERVER_PORT'] == 443) ? true: false;
        }
        
        
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        
        $SERVER_PORT = isset($s['SERVER_PORT']) ? $s['SERVER_PORT'] : 80;
        
        $port = $SERVER_PORT;
        $port = in_array($SERVER_PORT , ['80','443']) ? '' : ':'.$port;
        
        
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME']);
        $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['HTTP_X_ORIGINAL_URL']) ? $_SERVER['HTTP_X_ORIGINAL_URL'] : $_SERVER['QUERY_STRING']);
        $url = $protocol . '://' . $host . $port . $path;
        $pattern = ['/index\.php\//','/index\.php/'];
        $replacement = ['',''];
        $url = preg_replace($pattern, $replacement, $url);
        $a = parse_url($url);
        $a['host'] = strtolower($a['host']);
        return [
            'FULL_URL'=>$url,
            'URL_NO_PARAM'=> $a['scheme'].'://'.$a['host'].$port.$a['path'],
            'URL_WITH_PATH'=>$a['scheme'].'://'.$a['host'].$port.$a['path'],
            'URL_NOT_SCHEME'=>$a['host'].$port.$a['path'],
            'ABSOLUTE_DOMAIN'=>$a['scheme'].'://'.$a['host'],
            'URL_QUERY'=>isset($a['query']) ? $a['query'] : '',
            'DYNAMIC_SCHEME_DOMAIN'  =>  '//'.$a['host'].$port,
            'SITE_ADDRESS'=>Yii::$app->homeUrl,
            'SCHEME'=>$a['scheme'],
            'DOMAIN'=>$a['host'],
            "__DOMAIN__"=>$a['host'],
            'DOMAIN_NOT_WWW'=>preg_replace('/www./i','',$a['host'],1),
            'URL_NON_WWW'=>preg_replace('/www./i','',$a['host'],1),
            'URL_PORT'=>$port,
            'URL_PATH'=>$a['path'],
            '__TIME__'=>time(),
            'DS' => '/',
            'ROOT_USER'=>'root',
            'ADMIN_USER'=>'admin',
            'DEV_USER'=>'dev',
            'DEMO_USER'=>'demo',
            'USER'=>'user'
        ];
    }
    
    
    /**
     * {@inheritDoc}
     * @see \yii\web\UrlManager::init()
     */
    
    public function init()
    {
        parent::init();
        
        Yii::$app->view->theme = new \izi\web\Theme([
            'basePath'   =>  "@app/web",
            'viewPath'   =>  "@app/views",
        ]);
        
    }
    
    
    private $_moduleNames;
    public function getModuleNames(){
        if($this->_moduleNames == null){
            $this->_moduleNames = array_keys(Yii::$app->getModules());
        }
        return $this->_moduleNames;
    }
    
    private $_router;
    
    public function beforeRequest($request)
    {
        /**
         * Define $_server
         */
        foreach($this->getServerInfo() as $k=>$v){
            defined($k) or define($k,$v);
        }
        
        /**
         * define shop info
         *
         */
        $domain_module = false;
        $domain_module_name = '';
        $shop = $this->getModel()->getDomainInfo();
        
        
        $DOMAIN_INVISIBLED = false;
        if(!empty($shop)){
            
            define ('SHOP_STATUS',($shop['status']));
            define ('__SID__',(float)$shop['sid']);
            define ('DOMAIN_TEMPLATE',$shop['temp_id']);
            define ('__SITE_NAME__',$shop['code']);
            define ('__TEMPLATE_DOMAIN_STATUS__',$shop['state']);
            define ('DOMAIN_LAYOUT',$shop['layout']);
            define ('DOMAIN_LANGUAGE',!in_array($shop['lang'], ['', 'auto']) ? $shop['lang'] : '');
            
            define ('SHOP_CATEGORY',$shop['parent_id']);
            
            
            if($shop['module'] != "" && in_array($shop['module'], $this->getModuleNames())){
                $this->_router['module'] = $domain_module_name = $shop['module'];
                $domain_module = true;
                
            }
            
            $DOMAIN_INVISIBLED = isset($shop['is_invisible']) && $shop['is_invisible'] == 1 ? true : false;
            
        }else{
            define ('SHOP_STATUS',0);
            define ('__SID__',0);
            define ('DOMAIN_LAYOUT', 'main');
            define ('__TEMPLATE_DOMAIN_STATUS__',1);
            define ('SHOP_CATEGORY', 0);
        }
        
        //
        defined('DOMAIN_INVISIBLED') or define('DOMAIN_INVISIBLED', $DOMAIN_INVISIBLED);
        defined('__DOMAIN_MODULE__') or define('__DOMAIN_MODULE__', $domain_module);
        defined('__DOMAIN_MODULE_NAME__') or define('__DOMAIN_MODULE_NAME__', $domain_module_name);
        //
        // Define router
        
        $router = array_filter(explode('/', trim(URL_PATH,'/')));
        
        
        if(!empty($router)){
            if(!isset($this->_router['module']) && in_array($router[0], $this->getModuleNames())){
                $this->_router['module'] = array_shift($router);
            }
            
            if(!empty($router)){
                foreach ($router as $v) {
                    
                    if(isset(Yii::$app->view->specialPage) && !(DOMAIN_LAYOUT != "" && in_array(DOMAIN_LAYOUT, Yii::$app->view->specialPage))
                        && in_array($v, Yii::$app->view->specialPage)){
                            define('SPECIAL_LAYOUT', array_shift($router));
                    }
                    
                    break;
                }
                foreach ($router as $k=>$v) {
                    switch ($k) {
                        case 0: // controller
                            $this->_router['controller'] = $v;
                            break;
                        case 1: // action
                            $this->_router['action'] = $v;
                            break;
                            
                        default:
                            $this->_router["param" . ($k-1)] = $v;
                            break;
                    }
                }
            }
            
        }
        
        $this->addRules([
            '/'=>Yii::$app->defaultRoute . "/index",
            //             '<module:\w+>/<alias:login|logout|forgot>'=>'<module>/default/<alias>',
        ]);
        
        
        
        
        if(isset($this->_router['module'])){
            $this->addRules([
            //                 '/'=>Yii::$app->defaultRoute . "/index",
                '<module:\w+>/<alias:login|logout|forgot>'=>'<module>/default/<alias>',
            ]);
            
            
            // set rule for module
            define('__IS_MODULE__',true);
            
            $method_name = "parse". ucfirst($this->_router['module'])."Request";
            
            defined('__MODULE_NAME__') || define('__MODULE_NAME__', $this->_router['module']);
            
            defined('__DOMAIN_ADMIN__') || define('__DOMAIN_ADMIN__',__DOMAIN_MODULE__);
            
            defined('MODULE_ADDRESS') || define('MODULE_ADDRESS', __DOMAIN_MODULE__ ? cu(['/']) : cu(['/' . __MODULE_NAME__]));
            
            
            
            Yii::$app->user->loginUrl = [
                
                (defined('__DOMAIN_MODULE__') && __DOMAIN_MODULE__ ? '' : __MODULE_NAME__) . '/login'
                
            ];
            $request->router = $this->_router;
            $moduleClass = "\\app\\modules\\{$this->_router['module']}\\Module";
            
            
            if(method_exists($moduleClass, 'parseRequest')){
                $moduleClass::parseRequest($request, $this);
                
                
            }else{
                
                if(method_exists($this, $method_name)){
                    $this->$method_name($request);
                }
            }
            
            
            
        }else{
            // set rule for frontend
            define('__IS_MODULE__',false);
            defined('__MODULE_NAME__') || define('__MODULE_NAME__', 'app-frontend');
            
            
            $this->parseFrontendRequest($request);
            
        }
        
        //  Setup language
        $this->setLanguage($this->_slug);
        
        /**
         * Setup globa setting
         */
        //         Yii::$app->cfg->setupAppConfig();
        
        /**
         *
         */
        //         Yii::$app->cfg->setupSeoConfig();
        
        // Setup https
        //         Yii::$app->cfg->setupHttpsMethod();
        
        // Setup template
        $this->setTemplate($this->_router);
        
        
    }
    
    
    
    
    /**
     *
     */
    public function validateSlugs($slug){
        
        
        
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
    }
    
    protected  function setTemplate(){
        
        
        //
        
        $device = 'desktop';
        
        if(Yii::$app->session->has('manual_set_device')){
            $device = Yii::$app->session->get('manual_set_device',$device);
        }else{
            
            $useragent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT'] : 'unknown';
            
            if(preg_match('/(android|bb\d+|meego).+mobile|(android \d+)|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
                ||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
                    $device = 'mobile';
                    //                         $this->is_mobile = true;
            }
        }
        
        Yii::$app->setDevice($device);
        
        
        
        $temp = $this->getModel()->getTemplate();
        
        if(!empty($temp)){
            
            define('__TEMP_NAME__', ($temp['name'] != "" ? $temp['name'] : 'welcome'));
            
            define('__TID__', !empty($temp) ? $temp['id'] : 0);
            
            define('__TEMP_ID__', !empty($temp) ? $temp['id'] : 0);
            
            define('__HAS_MOBILE__', !empty($temp) && $temp['is_mobile'] == 1 ? true : false);
            
            define('__TCID__', !empty($temp) ? $temp['parent_id'] : 0);
            
            define ('MAIN_LAYOUT', isset($temp['layout']) && $temp['layout'] != "" ? $temp['layout'] : 'main');
            
            define ('TEMPLATE_VERSION', isset($temp['version']) && $temp['version'] != "" ? $temp['version'] : '');
        }else{
            define ('MAIN_LAYOUT',  'main');
            define ('__HAS_MOBILE__',  false);
            define('__TEMP_NAME__', 'welcome');
        }
        
        
        if(__IS_MODULE__
            // && !in_array($this->_router['module'], $this->_frontendModules)
            ){
                // Setup template for module
                
        }else{
            
            // Setup template for frontend
            
            
            //             $temp = $this->getModel()->getTemplate();
            
            if(!empty($temp)){
                
                //                 define('__TEMP_NAME__', ($temp['name'] != "" ? $temp['name'] : 'welcome'));
                
                //                 define('__TID__', !empty($temp) ? $temp['parent_id'] : 0);
                
                //                 define('__HAS_MOBILE__', !empty($temp) && $temp['is_mobile'] == 1 ? true : false);
                
                //                 define('__TCID__', !empty($temp) ? $temp['id'] : 0);
                
                //                 define ('MAIN_LAYOUT', isset($temp['layout']) && $temp['layout'] != "" ? $temp['layout'] : 'main');
                
                
                ///////////////////////////////////////////////////////////////////////////////////////////////////
                
                $appViews = $tempPath = '@app/themes/' . __TEMP_NAME__;
                
                $baseUrl = '@web/themes/' . __TEMP_NAME__;
                
                $basePath = '@app/themes/' . __TEMP_NAME__ . '/assets';
                
                
                
                
                //$rsDir = Yii::getAlias('@web/themes/' . __TEMP_NAME__);
                if(__HAS_MOBILE__ && $device == 'mobile'){
                    
                    $appViews .= "/$device";
                    $basePath = '@app/themes/' . __TEMP_NAME__ . "/$device" . '/assets';
                    $baseUrl .= "/$device";
                    
                }
                
                /**
                 * Set theme
                 */
                
                $theme = Yii::$app->view->theme;
                
                
                /**
                 * set current theme version
                 */
                
                // get all version of current template
                
                
                $versions = isset($temp['versions']) ? $temp['versions'] : [];
                //                 [
                //                     'v1.0',
                //                     'v1.5',
                //                     'v1.2',
                //                     'v2.0',
                //                 ] ;
                
                sort($versions, SORT_LOCALE_STRING );
                
                $version = isset($temp['version']) ? $temp['version'] : null; // Current version of template
                
                $theme->setVersion($version);
                
                $views = [
                    $appViews
                ];
                
                $widgets = [
                    "$tempPath/widgets"
                ];
                
                $modules = [
                    "$tempPath/modules"
                ];
                
                if(!empty($versions)){
                    foreach ($versions as $v) {
                        if($v != ""){
                            array_unshift($views, "$appViews/$v");
                            array_unshift($widgets, "$tempPath/$v/widgets");
                            array_unshift($modules, "$tempPath/$v/modules");
                        }
                    }
                }
                
                $theme->pathMap['@app/views']       =   $views;
                $theme->pathMap['@app/widgets']     =   $widgets;
                $theme->pathMap['@app/modules']     =   $modules;
                
                $theme->setBasePath($basePath);
                $theme->setBaseUrl($baseUrl);
                $theme->setViewPath($appViews);
                
                
                define('__RSDIR__', $theme->getBaseUrl());
                
                define('__RSPATH__', $theme->getBasePath());
                
                define('__LIBS_DIR__', Yii::getAlias('@web/libs'));
                
                define('__LIBS_PATH__', Yii::getAlias('@app/web/libs'));
                
            }
            
            Yii::$app->view->setTemplate((object)$temp);
            
        }
        
    }
    
    
    private function setModuleLanguage($slug){
        
        /**
         *  Define default system language
         */
        defined('ROOT_LANG') or define("ROOT_LANG",'vi-VN');
        defined('SYSTEM_LANG') or define("SYSTEM_LANG",ROOT_LANG);
        
        /**
         *  Default value is SYSTEM_LANG
         *  You can change to dynamic detected by ip address
         */
        
        
        
        $key = md5('config_language_' . __MODULE_NAME__);
        
        $lang = Yii::$app->session->get($key, null);
        
        $module_lang = $lang != null ? $lang : SYSTEM_LANG;
        
        
        defined('ADMIN_LANG') or define("ADMIN_LANG",$module_lang);
        defined('MODULE_LANG') or define("MODULE_LANG",$module_lang);
        defined('DEFAULT_LANG') or define("DEFAULT_LANG",SYSTEM_LANG);
        
        $language = SYSTEM_LANG;
        
        
        
        switch (__MODULE_NAME__) {
            
            
            default:
                
                $key = md5('module_config_frontend_language');
                
                $lang = Yii::$app->session->get($key, []);
                //                 $lang = Yii::$app->l->getItem(DOMAIN_LANGUAGE, true);
                //                 Yii::$app->session->set($key, $lang);
                
                
                if(empty($lang)){
                    
                    if(defined('DOMAIN_LANGUAGE') && DOMAIN_LANGUAGE != ""){
                        $lang = Yii::$app->l->getItem(DOMAIN_LANGUAGE, true);
                    }else{
                        $lang = Yii::$app->l->getDefault();
                    }
                    
                    
                    if(!empty($lang)){
                        Yii::$app->session->set($key, $lang);
                    }
                }
                
                
                if(!empty($lang)){
                    $language = $lang['code'];
                }else{
                    $language = Yii::$app->l->initDefaultLanguage();
                    
                }
                
                break;
        }
        
        
        defined('__LANG__') or define("__LANG__", $language);
        
        Yii::$app->language = Yii::$app->l->language;
        
        defined('__LANG2__') or define("__LANG2__", $language == 'en-US' ? SYSTEM_LANG : 'en-US');
        
    }
    
    private function setLanguage($slug = null){
        
        /**
         *  Use iso 639-1 or iso 639-1 alpha 2 for default language code.
         *  vi | vi-VN  =>  Vietnamese
         *  en | en-US  =>  English (US)
         *  en-GB       =>  English (GB)
         *
         *  Priority
         *
         *  1.  Check language by slug :    if existed -> return
         *      Note: Alway check and return newest value
         *
         *  2.  Check language by module :  if existed session || cookie language -> return
         *      Else get module config,     if existed language -> return
         *      Note: 1st check and save to session or cookie
         *
         *  3.  Check language by domain:   if existed session domain language -> return
         *      Else get domain config,     if existed language -> return
         *      Note: 1st check and save to session or cookie
         *
         *  4.  Check language by site:     if existed session site language -> return
         *      Else get site config,       if existed language -> return
         *      Note: 1st check and save to session or cookie
         *
         */
        
        
        if($slug === null){
            $slug = $this->_slug;
        }
        
        if(__IS_MODULE__){
            return $this->setModuleLanguage($slug);
        }
        
        /**
         *  Define default system language
         */
        defined('ROOT_LANG') or define("ROOT_LANG",'vi-VN');
        defined('SYSTEM_LANG') or define("SYSTEM_LANG",ROOT_LANG);
        
        /**
         *  Default value is SYSTEM_LANG
         *  You can change to dynamic detected by ip address
         */
        defined('ADMIN_LANG') or define("ADMIN_LANG",SYSTEM_LANG);
        defined('MODULE_LANG') or define("MODULE_LANG",SYSTEM_LANG);
        defined('DEFAULT_LANG') or define("DEFAULT_LANG",SYSTEM_LANG);
        
        $language = SYSTEM_LANG;
        
        if(!__IS_MODULE__ && !empty($slug) && $slug['lang'] != "" ){
            $language = $slug['lang'];
        }else{
            /**
             * Check language
             */
            
            
            
            
            if(defined('DOMAIN_LANGUAGE') && DOMAIN_LANGUAGE != ""){
                $lang = Yii::$app->l->getItem(DOMAIN_LANGUAGE, true);
            }else{
                $lang = Yii::$app->l->getDefault();
            }
            
            if(!empty($lang)){
                $language = $lang['code'];
            }else{
                $language = Yii::$app->l->initDefaultLanguage();
            }
            
            
        }
        
        
        defined('__LANG__') or define("__LANG__", $language);
        
        Yii::$app->language = Yii::$app->l->language;
        
        defined('__LANG2__') or define("__LANG2__", $language == 'en-US' ? SYSTEM_LANG : 'en-US');
    }
    
    public function parseRequest($request)
    {
        $this->beforeRequest($request);
        
        $parentRequest = parent::parseRequest($request);
        
        return $parentRequest;
    }
    
    
    private $_slug;
    
    public function setSlug($value){
        $this->_slug = $value;
    }
    
    public function getSlug(){
        return $this->_slug;
    }
    
    /**
     * Setup admin module request
     */
    
    
    public function parseAdminRequest($request)
    {
        ///
        Yii::setAlias('@libs', '/libs');
        
        
        
        
        ///
        defined('__DOMAIN_ADMIN__') || define('__DOMAIN_ADMIN__', __DOMAIN_MODULE__);
        
        if(!empty($this->_router)){
            foreach ($this->_router as $k=>$v) {
                $br = false;
                
                switch ($k) {
                    case 'controller':
                        
                        // Define special controller
                        if(in_array($v, Yii::$app->allowController)){
                            $this->_router['controller'] = $v;
                            //$this->_router['action'] = $v;
                            $br = true;
                            break;
                        }
                        
                        if(!isset($this->_router['action']))    $this->_router['action'] = 'index';
                        
                        // Get slug info
                        $this->_slug = $this->getModuleModel()->findUrl($v);
                        
                        Yii::$app->view->setCategory((object) $this->_slug);
                        
                        
                        
                        
                        if(!empty($this->_slug) ){
                            
                            if(in_array($this->_slug['route'], ['#', ''])){
                                $this->_slug['hasChild'] = true;
                                $this->_router['controller'] = 'default';
                            }else{
                                $this->_slug['hasChild'] = false;
                                $this->_router['controller'] = $this->_slug['route'];
                            }
                            
                            
                            
                            defined('__DETAIL_URL__') or define('__DETAIL_URL__', $this->_slug['url']);
                            defined('__RCONTROLLER__') or define('__RCONTROLLER__', $this->_slug['url']);
                            
                            defined('CONTROLLER_TEXT') or define('CONTROLLER_TEXT', $this->_slug['url']);
                            defined('CONTROLLER_CODE') or define('CONTROLLER_CODE', $this->_slug['child_code']);
                            defined('__CONTROLLER__') or define('__CONTROLLER__', $this->_router['controller']);
                            
                            defined('CONTROLLER_RGT') or define('CONTROLLER_RGT', $this->_slug['rgt']);
                            defined('CONTROLLER_LFT') or define('CONTROLLER_LFT', $this->_slug['lft']);
                            
                        }else{
                            
                            defined('__DETAIL_URL__') or define('__DETAIL_URL__', $this->_router['controller']);
                            defined('__CONTROLLER__') or define('__CONTROLLER__', $this->_router['controller']);
                            
                        }
                        
                        Yii::$app->slug = $this->_slug ;
                        $br = true;
                        break;
                }
                
                
                if($br) break;
            }
            
            if(isset($this->_router['controller']) && isset($this->_router['action'])){
                // define variable after setup controller
                $class = '\\app\\modules\\admin\\controllers\\' . ucfirst($this->_router['controller']) . 'Controller';
                
                $actions = explode('-', $this->_router['action']);
                $action = 'action' . implode('', array_map('ucfirst', $actions));
                
                if(!(class_exists($class) && method_exists($class, $action))
                    && !in_array($this->_router['controller'], array_merge([], ['member', 'user', 'collaborator','sim2', 'sim-api','cart','order']))
                    && !in_array($this->_router['action'], ['captcha'])
                    ){
                    unset($this->_router['action']);
                    $this->_router['controller'] = 'error';
                }
                // Set url
            }
            
            
//             if(!$is_validate_url && isset($this->_router['action'])
//                 && !in_array($this->_router['controller'], array_merge($catchAllController, ['member', 'user', 'collaborator','sim2', 'sim-api','cart','order']))
//                 && !in_array($this->_router['action'], ['captcha'])
//                 && !(class_exists($class)
//                     && method_exists($class, "action" . ucfirst($this->_router['action'])))){
//                         unset($this->_router['action']);
//             }
             
            
            define('__CATEGORY_ID__', isset($this->_slug['id']) ? $this->_slug['id'] : -1);
            define('__CATEGORY_URL__', isset($this->_slug['url']) ? $this->_slug['url'] : -1);
            define('__CATEGORY_NAME__', isset($this->_slug['title']) ? $this->_slug['title'] : '');
            define('CHECK_PERMISSION', isset($this->_slug['is_permission']) && $this->_slug['is_permission'] == 1 ? true : false);
            
            
            
            $request->setUrl('/' . implode('/', $this->_router));
             
            
        }
        
        
    }
    /**
     * setup frontend request
     * @param unknown $request
     */
    
    public function parseFrontendRequest($request)
    {
      
        $is_validate_url = false;
        
        // Add special alias site
        switch (SHOP_CATEGORY){
            case 12: // Simonline site
                
                
                if(isset($this->_router['controller']) && $this->_router['controller'] == 'tim-sim'){
                    $is_validate_url = true;
                }
                
                
                $this->addRules([
                    '<id:\d{10,12}>' => 'sim/detail',
                    //             '<id:\d+>.<suffix:\w+>' => 'sim/detail/?suffix',
                    [
                        'pattern' => '<id:\d{10,12}>.<suffix:\w+>',
                        'route' => 'sim/detail',
                        // 'defaults' => ['page' => 1, 'tag' => ''],
                    ],
                    
                    [
                        'pattern' => 'sim-so-dep-<id:\d{10,11}>',
                        'route' => 'sim/detail',
                        // 'defaults' => ['page' => 1, 'tag' => ''],
                    ],
                    
                    
                    
                    
                    [
                        'pattern' => '<alias:(tim-sim)>/<sosim:[^/]+>',
                        'route' => 'sim/index',
                        
                    ],
                    
                    [
                        'pattern' => 'tim-sim',
                        'route' => 'sim/index',
                        
                    ],
                    
                    [
                        'pattern' => 'sim-dau-so-<dauso:\d{4}-\d{3}>',
                        'route' => 'sim/index',
                        
                    ],
                    
                    [
                        'pattern' => 'sim-dau-so-<dauso:\d{4}>',
                        'route' => 'sim/index',
                        
                    ],
                    
                    [
                        'pattern' => '<alias:[^/]+>.<suffix:\w+>',
                        'route' => Yii::$app->defaultRoute . "/<alias>",
                        // 'defaults' => ['page' => 1, 'tag' => ''],
                    ],
                    
                ]);
                break;
        }
        
        // Add rule for default
        $this->addRules([
        //             '<id:\d+>' => 'sim/detail',
        //             '<id:\d+>.<suffix:\w+>' => 'sim/detail/?suffix',
        //             [
            //                 'pattern' => '<id:\d+>.<suffix:\w+>',
            //                 'route' => 'sim/detail',
            //                // 'defaults' => ['page' => 1, 'tag' => ''],
            //             ],
            
            '<alias:\w+>'=>Yii::$app->defaultRoute . "/<alias>",
            
        ]);
        
        $catchAllController = [
            'goto'
        ];
        
        foreach ($catchAllController as $controller){
            $this->addRules([
                '<controller:'.$controller.'>/<view>'=>"<controller>/index",
            ]);
        }
        
        // preparse slug
        
        
        $detail_url = '';
        
        $isDetail = false;
        if(!empty($this->_router)){
            foreach ($this->_router as $k=>$v) {
                
                $detail_url = $v;
                
                if($is_validate_url) break;
                
                $br = false;
                switch ($k) {
                    case 'controller':
                        
                        if($v == 'acp-module'){
                            if(isset($this->_router['module'])){
                                $this->_router['module'] = 'acp';
                            }else{
                                $this->_router = ['module'=>'acp'] + $this->_router;
                            }
                            
                            $this->_router['controller'] = 'default';
                            $this->_router['action'] = 'index';
                            $br = true;
                            break;
                        }
                        
                        // Define special controller
//                         if(in_array($v, ['robots.txt', 'sitemap.xml'])){
//                             $this->_router['controller'] = str_replace(['.txt','.xml'],'',$v);
//                             $br = true;
//                             break;
//                         }
                        
                        
                        
                        if(!isset($this->_router['action']))    $this->_router['action'] = 'index';
                        
                        // Get slug info
                        $this->_slug = $this->getModel()->findUrl($v);
                         
                        
                        if(!empty($this->_slug)){
                            
                            $this->_router['controller'] = $this->_slug['route'];
                            defined('__DETAIL_URL__') or define('__DETAIL_URL__', $this->_slug['url']);
                            defined('__CONTROLLER__') or define('__CONTROLLER__', $this->_slug['route']);
                            define('__ITEM_ID__', $this->_slug['item_id']);
                            define('__ITEM_TYPE__', $this->_slug['item_type']);
                            
                            //
                            switch(__ITEM_TYPE__){
                                case 0: // Category
                                    $category = $this->getModel()->getCategoryDetail(__ITEM_ID__);
                                    
                                    /**
                                     *
                                     */
                                    if($category['route'] == 'manual'){
                                        $this->_router['action'] = trim($category['link_target'],'/');
                                    }
                                    //
                                    if(isset($category['temp_id']) && $category['temp_id']>0){
                                        defined('CATEGORY_TEMPLATE') or define('CATEGORY_TEMPLATE',$category['temp_id']);
                                    }
                                    //
                                    if(isset($category['style']) && $category['style']>0){
                                        defined('CATEGORY_STYLE') or define('CATEGORY_STYLE',$category['style']);
                                    }
                                    /**
                                     *
                                     */
                                    $category['root'] = (object)$this->getModel()->getRootCategoryDetail($category);
                                    
                                    Yii::$app->view->setCategory((object)$category);
                                    
                                    break;
                                    
                                case 1: // Article detail
                                    
                                    $this->_router['action'] = 'detail';
                                    
                                    $isDetail = true;
                                    
                                    $item = $this->getModel()->getItemDetail(__ITEM_ID__);
                                    
                                    
                                    
                                    /**
                                     *
                                     */
                                    
                                    //
                                    if(isset($item['temp_id']) && $item['temp_id']>0){
                                        defined('ITEM_TEMPLATE') or define('ITEM_TEMPLATE',$item['temp_id']);
                                    }
                                    //
                                    if(isset($item['style']) && $item['style']>0){
                                        defined('ITEM_STYLE') or define('ITEM_STYLE',$item['style']);
                                    }
                                    /**
                                     *
                                     */
                                    if(!empty($item)){
                                        $category = $this->getModel()->getItemCategory(__ITEM_ID__);
                                        
                                        $category['root'] = (object)$this->getModel()->getRootCategoryDetail($category);
                                        
                                        $item['category'] = (object)$category;
                                        
                                        Yii::$app->view->setCategory((object)$category);
                                        Yii::$app->view->setItem((object)$item);
                                    }
                                    
                                    
                                    break;
                                case 2: // Box detail
                                    $item = $category= $this->getModel()->getBoxDetail(__ITEM_ID__);
                                    
                                    /**
                                     *
                                     */
                                    
                                    /**
                                     *
                                     */
                                    
                                    Yii::$app->view->setCategory((object)$category);
                                    Yii::$app->view->setItem((object)$item);
                                    
                                    
                                    
                                    break;
                            }
                        }else {
                            if($this->suffix == '' 
                                
                                && isset($this->_router['controller']) 
                                && !in_array($this->_router['controller'], ['sitemap.html'])
                                && strrpos($this->_router['controller'], '.html')){
                                
                                
                                
                                 
                                
                                $get = $_GET;
                                
                                $this->_router['controller'] = str_replace(['.html', '.htm'], '', $this->_router['controller']);
                                
                                if($this->_router['controller'] == 'tim-sim-so-dep'){
                                    $this->_router['controller'] = 'tim-sim';
                                    if(isset($get['key'])){
                                        $get['sosim'] = $get['key'];
                                        unset($get['key']);
                                    }
                                }
                                
                                $url = '/' . $this->_router['controller'];
                                
                                if(!empty($get)){
                                    $url .= '?' . http_build_query($get);
                                }
                                                     
                                Yii::$app->cfg->setRedirect(true);
                                Yii::$app->cfg->setUrlRedirect($url);
                            }
                        }
                        
                        
                        
                        break;
                    default:
                        
                        
                        $this->_router[$k] = $v;
                        
                        break;
                }
                if($br) break;
            }
            
            // define variable after setup controller
            $class = '\\app\\controllers\\' . ucfirst($this->_router['controller']) . 'Controller';
            
            
            
            if(!$is_validate_url && isset($this->_router['action'])
                && !in_array($this->_router['controller'], array_merge($catchAllController, ['member', 'user', 'collaborator','sim','sim2', 'sim-api','cart','order']))
                && !in_array($this->_router['action'], ['captcha'])
                && !(class_exists($class)
                    && method_exists($class, "action" . ucfirst($this->_router['action'])))){
                        unset($this->_router['action']);
            }
            
            // Set url
            
            $url = '/' . implode('/', $this->_router);
             
//             view($url,1,1);
            
            $request->setUrl($url);
            
        }
        
        
        
        defined('__DETAIL_URL__') or define('__DETAIL_URL__', $detail_url);
        
        
        define('__CATEGORY_ID__', isset($category['id']) ? $category['id'] : -1);
        define('__CATEGORY_URL__', isset($category['url']) ? $category['url'] : '');
        define('__CATEGORY_URL_LINK__', isset($category['url_link']) ? $category['url_link'] : '');
        define('__CATEGORY_NAME__', isset($category['title']) ? $category['title'] : '');
        define('__IS_DETAIL__', $isDetail);
        
        // Add rule for action
        
        if(isset($this->_router['action'])){
            $this->addRules([
                "<controller:\w+>/<action:\w+>"=>"<controller>/<action>"
            ]);
            
            
            /**
             * uncoment if you want catch all url to controller/action
             */
            
            //             $i = 0; $rule = '<controller:\w+>/<action:\w+>';
            //             foreach ($this->_router as $k=>$v) {
            //                 if($i++ > 1){
            //                     $rule .= "/<$k>";
            //                     $this->addRules([
            //                         $rule =>"<controller>/<action>",
            //                     ]);
            
            //                 }
            //             }
            
            
            
        }
        
       
        
        
    }
    
}
