<?php 
namespace app\extentions\router;

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
            
            
            
            if(!empty($this->_router)){
                         
            // Default module 
                $this->_router['action'] = 'index';
                foreach ($this->_router as $k=>$v) {
                    $br = false;
                    switch ($k) {
                        case 'controller':
                            
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
                                            defined('PRIVATE_TEMPLATE') or define('PRIVATE_TEMPLATE',$category['temp_id']);
                                        }
                                        //
                                        if(isset($category['style']) && $category['style']>0){
                                            defined('CONTROLLER_STYLE') or define('CONTROLLER_STYLE',$category['style']);
                                        }
                                        /**
                                         *
                                         */
                                        $category['root'] = (object)$this->app->model->siteMenu->getRootCategoryDetail($category);
                                        
                                        $this->app->setCategory((object)$category);
                                    break;
                                    
                                    default:
                                        ;
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
        
        
        }
         
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
    
    
    
    
}