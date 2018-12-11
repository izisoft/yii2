<?php 
namespace izi\router;

use Yii;

class UrlManager extends \yii\web\UrlManager
{
    
    protected $_moduleName;
    
    protected $_moduleNames;
    
    protected $_router;
    
    private $_slug;
    
    private $_addRulers = [];
    
    private $_model ;
    
    public function getModel(){
        if($this->_model == null){ 
            $this->_model = Yii::createObject(DbRouter::class);
        }
        return $this->_model;
    }
    
    protected function preparseRequest($request = null){
        
        /**
         * Define $_server
         */
        
        foreach($this->getServerInfo() as $k=>$v){
            defined($k) or define($k,$v);
        }
            
        if(empty($request)){
            $request = Yii::$app->getRequest();
        }
        
        
        /**
         * define shop info
         *
         */
        
        $shop = $this->getModel()->getDomainInfo();
        $DOMAIN_INVISIBLED = false;
        if(!empty($shop)){
            
            define ('SHOP_STATUS',($shop['status']));
            define ('__SID__',(float)$shop['sid']);
            define ('__SITE_NAME__',$shop['code']);
            define ('__TEMPLATE_DOMAIN_STATUS__',$shop['state']);
            define ('DOMAIN_LAYOUT',$shop['layout']);
             
            if($shop['module'] != "" && in_array($shop['module'], $this->getModuleNames())){
                $this->_router['module'] = $shop['module'];
            }
            
            $DOMAIN_INVISIBLED = isset($shop['is_invisible']) && $shop['is_invisible'] == 1 ? true : false;
            
        }else{
            define ('SHOP_STATUS',0);
            define ('__SID__',-1);
        }
        
        //
        defined('DOMAIN_INVISIBLED') or define('DOMAIN_INVISIBLED', $DOMAIN_INVISIBLED);
        
        //
        
        $router = array_filter(explode('/', trim($request->url,'/')));
        
        if(!empty($router)){
            if(!isset($this->_router['module']) && in_array($router[0], $this->getModuleNames())){                
                $this->_router['module'] = array_shift($router);                
            }
            
            if(!empty($router)){
                foreach ($router as $v) {
                    
                    if(!(DOMAIN_LAYOUT != ""  && in_array(DOMAIN_LAYOUT, Yii::$app->view->specialPage))
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
        ]);
        
        
        if(isset($this->_router['module'])){
            
            
            
        }else{
            
            $this->addRules([
                '<alias:login>'=>"member/<alias>",
                '<alias:signup>'=>"member/<alias>",
                '<alias:\w+>'=>Yii::$app->defaultRoute . "/<alias>",
            ]);
            
            $isDetail = false;
            
            if(!empty($this->_router)){
                         
            // Default module 
                
                foreach ($this->_router as $k=>$v) {
                    $br = false;
                    switch ($k) {
                        case 'controller':
                            
                            if(!isset($this->_router['action']))    $this->_router['action'] = 'index';
                            
                            // Get slug info
                            $this->_slug = $this->getModel()->findUrl($v);
                            
                            if(!empty($this->_slug)){
                                
                                $this->_router['controller'] = $this->_slug['route'];
                                
                                defined('__DETAIL_URL__') or define('__DETAIL_URL__', $this->_slug['url']);
                                defined('__CONTROLLER__') or define('__CONTROLLER__', $this->_slug['route']);
                                define('__ITEM_ID__', $this->_slug['item_id']);
                                define('__ITEM_TYPE__', $this->_slug['item_type']);
                               
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
                                        
                                        $category = $this->getModel()->getItemCategory(__ITEM_ID__);
                                        
                                        $category['root'] = (object)$this->getModel()->getRootCategoryDetail($category);
                                        
                                        $item['category'] = (object)$category;
                                        
                                        Yii::$app->view->setCategory((object)$category);
                                        Yii::$app->view->setItem((object)$item);
                                        
                                        break;
                                    case 2: // Box detail
                                        $item = $this->getModel()->getBoxDetail(__ITEM_ID__);
                                        
                                        /**
                                         *
                                         */
                                         
                                        /**
                                         *
                                         */
                                        
                                        Yii::$app->view->setItem((object)$item);
                                        
                                        
                                        
                                        break;
                                }
                                
                            }
                            
                        break;
 
                    }
                    
                    if($br) break;
                    
                }
            
                $class = '\\frontend\\controllers\\' . ucfirst($this->_router['controller']) . 'Controller';
                 
                                 
                if(!(class_exists($class) && method_exists($class, "action" . ucfirst($this->_router['action'])))){
                    unset($this->_router['action']);
                }
                
                $request->url = '/' . implode('/', $this->_router);
             
            }
        
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
        
            $this->afterRouter();
            
            $this->setTemplate();
        }
        // ## END FRONTEND CONTROLLER ## //
        
        
         
    }
    /**
     * Return current module
     * @return @string
     */
    public function getModuleName($request = null){
        
        
        if($this->_moduleName == null){
            
            if(empty($request)){
                $request = Yii::$app->getRequest();
            }
            
            $router = array_filter(explode('/', trim($request->url,'/')));                        
            
            if(!empty($router)){
                if(in_array($router[0], $this->getModuleNames())){
                    $this->_moduleName = array_shift($router);
                }else{
                    $this->_moduleName = Yii::$app->defaultRoute;
                }
            }else{
                $this->_moduleName = Yii::$app->defaultRoute;
            }
            
        }
        return $this->_moduleName;
    }
    
    public function getModuleNames(){
        if($this->_moduleNames == null){ 
            $this->_moduleNames = array_keys(Yii::$app->getModules());
        }
        return $this->_moduleNames;
    }
    
    
    protected function parseUrl($request = null){
        var_dump(Yii::$app->id);
        //  var_dump($request->url); exit;
        switch ($this->getModuleName()){
            case Yii::$app->defaultRoute:
                
                var_dump(URL_PATH);
                
                $this->_router['controller'] = $this->_moduleName;
                
                $this->addRules([
                '<alias:\w+>'=>"{$this->_router['controller']}/<alias>",
                '<controller:\w+>/<action:w+>'=>"<controller>/<action>",  
                ]);
                break;
        }
    }
    
    public function parseRequest($request)
    {
        
        $this->preparseRequest($request);
        
//         $this->parseUrl($request);
        
        $modules = array_keys(Yii::$app->getModules());
        
        
        
        $parentRequest = parent::parseRequest($request);

        return $parentRequest;
    }
    
    
    protected function getServerInfo(){
        $s = $_SERVER;
        $ssl = (isset($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:
        
        (isset($s['HTTP_X_FORWARDED_PROTO']) && strtolower($s['HTTP_X_FORWARDED_PROTO']) == 'https' ? true : false);
        
        
        
        
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        
        $SERVER_PORT = isset($s['SERVER_PORT']) ? $s['SERVER_PORT'] : 80;
        
        $port = $SERVER_PORT;
        $port = in_array($SERVER_PORT , ['80','443']) ? '' : ':'.$port;
        
        
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
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
    
    
    
    private function afterRouter()
    {
        // Set language
        $this->setLanguage($this->_slug);
        
        $this->setSeoConfig(!empty($item = Yii::$app->view->getItem()) ? $item : Yii::$app->view->getCategory());
        
        $contact = \app\models\SiteConfigs::getConfigs('CONTACT', __LANG__, __SID__, true, true);
        
        Yii::$app->view->setSiteConfig('contact',$contact);
        
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
        
        $language = SYSTEM_LANG;
        
        if(!empty($slug) && $slug['lang'] != ""){
            $language = $slug['lang'];
        }else{
            /**
             * Check language
             */
            
            $lang = Yii::$app->l->getDefault();
            
            if(!empty($lang)){
                $language = $lang['code'];
            }else{
                $language = Yii::$app->l->initDefaultLanguage();
            }
            
            
        }
        
        
        defined('__LANG__') or define("__LANG__", $language);
        
        defined('__LANG2__') or define("__LANG2__", $language == 'en' ? SYSTEM_LANG : 'en');
    }
    
    
    
    protected function setSeoConfig($item = []){
        //
        $seo = \app\models\SiteConfigs::getConfigs('SEO', __LANG__, __SID__, false, true);
        
        
        
        // Default
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
            $page_seo = $sd;
            $seo = array_merge($sd,$seo);
            $seo['page_seo'] = $page_seo;
        }
        
        
        
        // Custom by page
        if(!empty($item)){
            
            $seo['title'] = isset($item->seo['title']) && $item->seo['title'] != "" ? $item->seo['title'] : $item->title;
            
            $seo['description'] = isset($item->seo['description']) && $item->seo['description'] != "" ?
            $item->seo['description'] : (isset($item->info) && $item->info != "" ? $item->info : $seo['title']);
            
            $seo['keyword'] = (isset($item->seo['focus_keyword']) && $item->seo['focus_keyword'] != "" ?
                $item->seo['focus_keyword'] . ',' : (isset($item->focus_keyword) && $item->focus_keyword != "" ?
                    $item->focus_keyword . ',' : '') ) . (isset($item->seo['keyword']) && $item->seo['keyword'] != "" ?
                        $item->seo['keyword'] : $seo['title']);
                    
                    if(isset($item->icon) && $item->icon != ""){
                        $seo['og_image'] = $item->icon;
                    }
        }
        
        // Set config
        
        if(isset($seo['title']) && isset($seo['before_title']) && $seo['before_title'] != ""){
            $seo['title'] = trim($seo['before_title']) . " " . $seo['title'];
            $seo['title'] = trim($seo['title']);
        }
        
        if(isset($seo['title']) && isset($seo['after_title']) && $seo['after_title'] != ""){
            $seo['title'] .= " " . trim($seo['after_title']);
            $seo['title'] = trim($seo['title']);
        }
        
        //$this->tag->setTitle($seo['title']);
        
        Yii::$app->view->setSiteConfig('seo',$seo);
        
    }
    
    protected  function setTemplate(){
        
        
        // 
        
        $device = 'desktop';
        
        if(Yii::$app->session->has('manual_set_device')){
            $device = Yii::$app->session->get('manual_set_device',$device);
        }else{
        
            $useragent=$_SERVER['HTTP_USER_AGENT'];
            
            if(preg_match('/(android|bb\d+|meego).+mobile|(android \d+)|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
                ||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
                    $device = 'mobile';
                    //                         $this->is_mobile = true;
            }
        }
        
        Yii::$app->setDevice($device);
        
        $temp = $this->getTemplate();
        
        
        if(!empty($temp)){
             
            define('__TEMP_NAME__', ($temp['name'] != "" ? $temp['name'] : 'welcome'));                        
            
            define('__TID__', !empty($temp) ? $temp['parent_id'] : 0);
            
            define('__HAS_MOBILE__', !empty($temp) && $temp['is_mobile'] == 1 ? true : false);
            
            define('__TCID__', !empty($temp) ? $temp['id'] : 0);
            
            define ('MAIN_LAYOUT', isset($temp['layout']) && $temp['layout'] != "" ? $temp['layout'] : 'main');
            
            $rsDir = Yii::getAlias('@web/themes/' . __TEMP_NAME__);
            if(__HAS_MOBILE__ && $device == 'mobile'){
                $rsDir .= "/$device";
            }
            
            define('__RSDIR__', $rsDir);
            
            define('__RSPATH__', __ROOT_PATH__  . __RSDIR__);
            
            define('__LIBS_DIR__', Yii::getAlias('@web/libs'));
            
            define('__LIBS_PATH__', __ROOT_PATH__ . Yii::getAlias('@web/libs'));
             
            
        }
        
        Yii::$app->view->setTemplate((object)$temp);
        
    }
    
    
    private function getTemplate(){
        
        $item = [];
        
        $params = [
            __METHOD__,
            __FILE__
        ];
        
        $cached = Yii::$app->icache->getCache($params);
        
        if(!YII_DEBUG && !empty($cached)){
            return $cached;
        }
        
        if(defined('CATEGORY_TEMPLATE') && CATEGORY_TEMPLATE>0){
            $item = DbRouter::findOne(["id" => CATEGORY_TEMPLATE]);
            if(!empty($item)) {
                $item = $item->toArray();
            }
        }
        
        if(empty($item)){
            
            $item = DbRouter::find()
            ->select(['a.*'])
            ->from(['a' => '{{%templates}}'])
            ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
            ->where(
            [
                'b.state'=>__TEMPLATE_DOMAIN_STATUS__,
                'b.sid'=>__SID__,
                'b.lang'=>__LANG__,
            ])
            ->asArray()
            ->one();   
                
                
                  
            if(empty($item)){
                
                $item = DbRouter::find()
                ->select(['a.*'])
                ->from(['a' => '{{%templates}}'])
                ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
                ->where(
                [
                    'b.state'=>__TEMPLATE_DOMAIN_STATUS__,
                    'b.sid'=>__SID__,
                    //'b.lang'=>__LANG__,
                ])
                ->asArray()
                ->one();
                    
                    
                if(empty($item) && __TEMPLATE_DOMAIN_STATUS__ > 1){
                    
                    $item = DbRouter::find()
                    ->select(['a.*'])
                    ->from(['a' => '{{%templates}}'])
                    ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
                    ->where(
                        [
                            'b.state'=>1,
                            'b.sid'=>__SID__,
                            'b.lang'=>__LANG__,
                        ])
                        ->asArray()
                        ->one();
                        
                        if(empty($item)){
                            
                            $item = DbRouter::find()
                            ->select(['a.*'])
                            ->from(['a' => '{{%templates}}'])
                            ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id")
                            ->where(
                                [
                                    'b.state'=>1,
                                    'b.sid'=>__SID__,
                                    //'b.lang'=>__LANG__,
                                ])
                                ->asArray()
                                ->one();
                                
                        }
                }
            }
                
                
        }
        
        Yii::$app->icache->store($item, $params);       
        
        return $item;
    }
    
}
