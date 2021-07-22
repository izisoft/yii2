<?php
namespace izi\router;

use Yii;

class BaseUrl extends \yii\web\UrlManager
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
 
        $this->setup($this->parseDomain());
    }

    /**
     * 
     */
    public function setup(array $param)
    {
        $DOMAIN_HIDDEN =  $domain_module = false; $domain_module_name = '';
        if(!empty($param)){
            
            define ('__SID__', $param['sid']);
            
            define ('__SITE_NAME__', $param['code']);
            
            define ('__TEMPLATE_DOMAIN_STATUS__', 1);
            
            if($param['module'] != "" && in_array($param['module'], $this->getModuleNames())){
                $this->_router['module'] = $domain_module_name = $param['module'];
            }
            
            $DOMAIN_HIDDEN = $param['is_hidden'];
            
        }else{
            define ('__SID__', -1);
        }
        
        //
        defined('DOMAIN_HIDDEN') or define('DOMAIN_HIDDEN', $DOMAIN_HIDDEN);
        defined('__DOMAIN_MODULE__') or define('__DOMAIN_MODULE__', $domain_module);
        defined('__DOMAIN_MODULE_NAME__') or define('__DOMAIN_MODULE_NAME__', $domain_module_name);
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

        /**
         * replace url domain.com:8080 => domain.com
         */
         
        $a['host'] = strtolower(preg_replace('/:\d+$/i', '', $a['host']));

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
 

            $d = \izi\models\DomainPointer::find()->where(['domain' => $domain])->one();

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
                '/'.$this->_router['module'].'/<alias:login|logout|forgot>'=> $this->_router['module'] . '/default/<alias>',
            ]);
            // custom rule

            $fp = Yii::getAlias(implode('/', [
                "@module",
                $this->_router['module'],
                'config',
                'rule.php'
            ]));
            
//             view($fp,__FILE__,1);
            
            if(file_exists($fp)){
                $this->addRules(require $fp);
            }

            
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
            
            $moduleClass = str_replace('/','\\', "\\app\\modules\\{$this->_router['module']}\\Module");

            //  Setup language
            $this->setLanguage($this->_slug);
            // Setup template
            $this->setTemplate($this->_router);

            if(method_exists($moduleClass, 'parseRequest')){

                $moduleClass::parseRequest($request, $this);

            }else{

                if(method_exists($this, $method_name)){
                    $this->$method_name($request);
                }
            }

            

        }else{
            
//             $this->addRules([
//                 '/'=>Yii::$app->defaultRoute . "/index",
//             ]);

            // set rule for frontend
            define('__IS_MODULE__',false);
            defined('__MODULE_NAME__') || define('__MODULE_NAME__', 'app-frontend');

         

            $this->setLanguage($this->_slug);

         
 

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
//         $this->beforeRequest($request);

       
        $parentRequest = parent::parseRequest($request);

        return $parentRequest;
    }

   

    public function getRouter()
    {
        return $this->_router;
    }

    public function setRouter($key, $value)
    {
        $this->_router[$key] = $value;
        return $this->_router;
    }

    public function unsetRouter($key)
    {
        if(isset($this->_router[$key])) unset($this->_router[$key]);
        return $this->_router;
    }
}
