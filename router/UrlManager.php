<?php
namespace izi\router;

use Yii;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * array [
     *  'module',
     *  'controller',
     *  'action'
     * ]
     */
    private $_router;

    /**
     * {@inheritDoc}
     * @see \yii\web\UrlManager::init()
     */

    public function init()
    {
        parent::init();

        /**
         * Defined global variable
         */
        foreach($this->getServerInfo() as $k=>$v){
            defined($k) or define($k,$v);
        }

        /**
         * Set default theme
         */
        Yii::$app->view->theme = new \izi\theme\Theme([
            'basePath'   =>  "@app/web",
            'viewPath'   =>  "@app/views",
        ]);

    }


    /**
     * Setup model class
     */
    private $_model ;

    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject(UrlModel::class);
        }
        return $this->_model;
    }


    /**
     * return all module in config file
     * array = [
     *      'admin',
     *      'api',
     *      ...
     * ]
     */

    private $_moduleNames;
    public function getModuleNames(){        

        if($this->_moduleNames == null){
            $this->_moduleNames = [];
            $modules = Yii::$app->getModules();
            if(!empty($modules)){
                foreach($modules as $moduleName => $r){
                    if(in_array($moduleName, ['gii', 'debug'])) continue;                    
                    if(is_array($r) && isset($r['modules'])){
                        foreach($r['modules'] as $md2 => $val){
                            $this->_moduleNames[] = "$moduleName/$md2";
                        }
                    }else{
                        $this->_moduleNames[] = $moduleName;
                    }
                    
                }
            }

        }
     
        return $this->_moduleNames;
    }

    /**
     * get server info
     */

    protected function getServerInfo(){

        $s = $_SERVER;  $ssl = false;

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
            'SITE_ADDRESS'=>$a['scheme'].'://'.$a['host'].$port,
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
     * init data from current domain
     * => get sid/website_id/store_id from domain
     */

    public function parseDomain($domain = __DOMAIN__)
    {
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


            $d = \izi\models\DomainPointer::findOne(['domain' => $domain]);

            if(!empty($d)){

                $s = $d->getS()->one();

                if(!empty($s)){
                    $config = [
                        'sid' => $s->id,
                        'code' => $s->code,
                        'is_hidden' => $d->is_hidden,
                        'module' => $d->module,
                        'store_id' => isset($d->store_id) ? $d->store_id : 0,
                        'store_group_id' => isset($d->store_group_id) ? $d->store_group_id : 0,
                        'store_website_id' => isset($d->store_website_id) ? $d->store_website_id : 0,
                    ];

                    Yii::$app->icache->store($config, $params);


                    return $config;
                }
            }

        }
    }


    /**
     * modify request
     */

    public function beforeRequest($request)
    {
        // Parse domain
        $s = $this->parseDomain();

        $DOMAIN_HIDDEN =  $domain_module = false; $domain_module_name = '';
        if(!empty($s)){

            define ('__SID__', $s['sid']);

            define ('__SITE_NAME__', $s['code']);

            define ('__TEMPLATE_DOMAIN_STATUS__', 1);

            if($s['module'] != "" && in_array($s['module'], $this->getModuleNames())){
                $this->_router['module'] = $domain_module_name = $s['module'];
                $domain_module = true;

            }

            $DOMAIN_HIDDEN = $s['is_hidden'];

        }else{
            define ('__SID__', -1);
        }

        //
        defined('DOMAIN_HIDDEN') or define('DOMAIN_HIDDEN', $DOMAIN_HIDDEN);
        defined('__DOMAIN_MODULE__') or define('__DOMAIN_MODULE__', $domain_module);
        defined('__DOMAIN_MODULE_NAME__') or define('__DOMAIN_MODULE_NAME__', $domain_module_name);

        // Parse router
        
        $router = [];

        $pattern = '/^('.str_replace('/','\\/',implode('|', $this->getModuleNames())).')\/?([\w\/\-\+]+)?/i';

        preg_match($pattern, trim(URL_PATH, DS), $m);

        if(!empty($m)){
            $this->_router['module'] = $m[1];
            if(isset($m[2])){
                $router = explode(DS, trim($m[2], DS));
            }
        }else{
            $router = explode(DS, trim(URL_PATH, DS));
        }
     

        if(!empty($router)){

            if(!empty($router)){

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
 
        if(isset($this->_router['module']) && $this->_router['module'] != ""){

            $this->addRules([
                '/'=>$this->_router['module'] . "/default/index",
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

            //  Setup language
            $this->setLanguage($this->_slug);
            // Setup template
            $this->setTemplate($this->_router);

        }else{
            $this->addRules([
                '/'=>Yii::$app->defaultRoute . "/index",
            ]);

            // set rule for frontend
            define('__IS_MODULE__',false);
            defined('__MODULE_NAME__') || define('__MODULE_NAME__', 'app-frontend');

            $this->parseFrontendRequest($request);

            $this->setLanguage($this->_slug);

            // Setup template
            $this->setTemplate($this->_router);

            if(!empty($this->_router)){
                $this->addRules([
                    $request->url => '/' . implode('/', $this->_router),
                ]);
            }

        }



        // Pause

    }

    /**
     *
     * {@inheritDoc}
     * @see \yii\web\UrlManager::parseRequest()
     */

    public function parseRequest($request)
    {
        $this->beforeRequest($request);

        $parentRequest = parent::parseRequest($request);

        return $parentRequest;
    }


    private $_slug;

    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     * frontend request
     */
    public function parseFrontendRequest($request)
    {

        $is_validate_url = false;

        $fp = dirname(Yii::$app->view->theme->getPath('')) . DIRECTORY_SEPARATOR . '/rule.custom.php';

        if(file_exists($fp)){

            $rule = require_once $fp;

            if(!empty($rule)){
                $this->addRules($rule);
            }

        }


        // parse slug

        $detail_url = '';

        $isDetail = false;
        if(!empty($this->_router)){
            foreach ($this->_router as $k=>$v) {

                $detail_url = $v;

                if($is_validate_url) break;

                $br = false;
                switch ($k) {
                    case 'controller':

                        $this->_slug = \izi\models\Slugs::find()->where(['sid' => __SID__, 'url' => $v])->asArray()->one();

                        if(empty($this->_slug)){
                            $lang = \izi\models\AdLanguages::find()->where(['or', ['code' => $v], ['hl' => $v]])->one();

                            if(!empty($lang)){
                                $this->_slug['route'] = 'index';
                                $this->_slug['url'] = $v;
                                $this->_slug['item_id'] = 0;
                                $this->_slug['item_type'] = 100;
                                $this->_slug['lang'] = $lang->code;
                            }
                        }

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

                                case 8: // new product
                                    $this->_router['action'] = 'detail';

                                    $isDetail = true;

                                    $item = Yii::$app->product->model->getItem(__ITEM_ID__)->toArray();


                                    /**
                                     *
                                     */
                                    if(!empty($item)){

                                        $item['id'] = $item['entity_id'];

                                        $item['name'] = $item['title']  = Yii::$app->product->getAttrValue(__ITEM_ID__, 'name');

                                        $item['url_link']  = Yii::$app->product->getAttrValue(__ITEM_ID__, 'url_link');

                                        $item['time'] = $item['created_at'];

                                        $item['created_by'] = 0;

                                        $item['code'] = $item['sku'];

                                        $item['price2'] = $item['price'] = Yii::$app->product->getPrice(__ITEM_ID__, 'price');

                                        $item['currency'] = max(1, Yii::$app->product->getPrice(__ITEM_ID__, 'currency'));

                                        $item['status'] = Yii::$app->product->getPrice(__ITEM_ID__, 'status');

                                        $category = Yii::$app->product->model->getItemCategory(__ITEM_ID__);

                                        $category['root'] = (object)$this->getModel()->getRootCategoryDetail($category);

                                        $item['category'] = (object)$category;

                                        Yii::$app->view->setCategory((object)$category);

                                        Yii::$app->view->setItem((object)$item);
                                    }

                                    break;
                            }
                        }else{

                        }

                        break;
                    default:


                        $this->_router[$k] = $v;

                        break;
                }

                if($br) break;
            }
        }

        /////////////////////////////////////

        defined('__DETAIL_URL__') or define('__DETAIL_URL__', $detail_url);

        define('__CATEGORY_ID__', isset($category['id']) ? $category['id'] : -1);
        define('__CATEGORY_URL__', isset($category['url']) ? $category['url'] : '');
        define('__CATEGORY_URL_LINK__', isset($category['url_link']) ? $category['url_link'] : '');
        define('__CATEGORY_NAME__', isset($category['title']) ? $category['title'] : '');
        define('__IS_DETAIL__', $isDetail);

    }



    /**
     *
     */

    public function getRootCategoryDetail($item = []){
        if(is_numeric($item)){
            $item = $this->getCategoryDetail($item);
        }

        if(!empty($item)){

            if(isset($item['parent_id']) && $item['parent_id'] == 0){
                return $item;
            }else{

                $item = static::find()
                ->from('{{%site_menu}}')
                ->where(['and',[
                    "parent_id" => 0,
                    'is_active'=>1 ,
                    'sid'=>__SID__
                ],
                    ['<', 'lft', $item['lft']],
                    ['>', 'rgt', $item['rgt']],
                ])->asArray()->one();

                return $this->populateData($item);

                //                 if(!empty($item)) {
                //                     if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                //                         $item += $content;
                //                         unset($item['bizrule']);
                //                     }
                //                     return $item;
                //                 }
            }

        }
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
            }

        }

        Yii::$app->setDevice($device);

        $temp = $this->getModel()->getTemplate();

        if(!empty($temp)){

            define('__TEMP_NAME__', ($temp['code'] != "" ? $temp['code'] : 'welcome'));

            define('__TID__', !empty($temp) ? $temp['id'] : 0);

            define('__TEMP_ID__', !empty($temp) ? $temp['id'] : 0);

            define('__HAS_MOBILE__', !empty($temp) && $temp['is_mobile'] == 1 ? true : false);

            define('__TCID__', !empty($temp) ? $temp['parent_id'] : 0);

            define ('MAIN_LAYOUT', isset($temp['layout']) && $temp['layout'] != "" ? $temp['layout'] : 'main');

            define ('TEMPLATE_VERSION', isset($temp['version']) && $temp['version'] != "" ? $temp['version'] : '');

            if(isset($temp['category']['code'])){
                define('__TCCODE__',$temp['category']['code']);
            }else{
                define('__TCCODE__', '');
            }

        }else{
            define ('MAIN_LAYOUT',  'main');
            define ('__HAS_MOBILE__',  false);
            define('__TEMP_NAME__', 'welcome');
        }


        // Store
        $store = Yii::$app->store->getDefaultStore();

        if(!empty($store)){
            define('STORE_WEBSITE_ID', $store['website_id']);
            define('STORE_GROUP_ID', $store['group_id']);
            define('STORE_ID', $store['store_id']);
        }

        define('__LIBS_DIR__', Yii::getAlias('@web/libs'));

        define('__LIBS_PATH__', Yii::getAlias('@app/web/libs'));


        if(__IS_MODULE__
            // && !in_array($this->_router['module'], $this->_frontendModules)
            ){
                // Setup template for module

        }else{

            // Setup template for frontend

            if(!empty($temp)){

                ///////////////////////////////////////////////////////////////////////////////////////////////////

                $appViews = $tempPath = '@app/themes/' . __TEMP_NAME__;

                $baseUrl = '@web/themes/' . __TEMP_NAME__;

                $basePath = '@app/themes/' . __TEMP_NAME__ . '/assets';

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

        defined('MODULE_LANG') or define("MODULE_LANG",$module_lang);
        defined('DEFAULT_LANG') or define("DEFAULT_LANG",SYSTEM_LANG);

        $language = SYSTEM_LANG;

        switch (__MODULE_NAME__) {

            default:

                $key = md5('module_config_frontend_language');

                $lang = Yii::$app->session->get($key, []);

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

        defined('ADMIN_LANG') or define("ADMIN_LANG",__LANG__);

        Yii::$app->language = Yii::$app->l->language;

        defined('__LANG2__') or define("__LANG2__", $language == 'en-US' ? SYSTEM_LANG : 'en-US');

    }


}
