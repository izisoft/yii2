<?php 
namespace izi\web;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
class View extends \yii\web\View

{
    private $_viewFiles = [];
    
    public $minifyCssFiles = [], $minifyJsFiles = [];
    
    public $hasAmp = false, $isAmp = false;
    
    
    public $specialPage = ['amp','api'];
    
    /**
     * set view category
     * 
     */
    private $_category;
    
    public function getCategory()
    { 
        return $this->_category;   
    }
    
    public function setCategory($value)
    {
        $this->_category = $value;
    }
    
    
    /**
     * set view contact info
     * 
     */
    private $_contact;
    
    public function getContact()
    { 
        return $this->_contact;   
    }
    
    public function setContact($value)
    {
        $this->_contact = $value;
    }
    
    /**
     * set view item
     *
     */
    private $_item;
    
    public function getItem()
    {
        return $this->_item;
    }
    
    public function setItem($value)
    {
        $this->_item = $value;
    }
    
    /**
     * set view item
     *
     */
    private $_template;
    
    public function getTemplate()
    {
        return $this->_template;
    }
    
    public function setTemplate($value)
    {
        $this->_template = $value;
    }
    
    private $_config;
    
    
    public function getConfig()
    {
        if($this->_config == null){
            
            //view(\app\models\SiteConfigs::getConfigs('SITE_CONFIGS', __LANG__ , __SID__, true, true), 'Confi'); 
            
            $this->_config = (object)\app\models\SiteConfigs::getConfigs('SITE_CONFIGS', __LANG__ , __SID__, true, true);
        }
        return $this->_config;
    }
    
    
    public function setSiteConfig($key, $value){
        
        $keys = explode('|', $key);
        
        switch (count($keys)){
            case 0:
                return;
                break;
            case 1:
                
                $this->getConfig()->{$keys[0]} = $value;
                
                break;
            case 2:
                if(isset($this->getConfig()->{$keys[0]}{$keys[1]}) && is_array($this->getConfig()->{$keys[0]}{$keys[1]})){
                    $this->getConfig(){$keys[0]}{$keys[1]} = $value;
                }else{
                    $this->getConfig()->{$keys[0]}->{$keys[1]} = $value;
                }
                break;
                
        }
        
    }
    
    
    protected function findViewFile($view, $context = null)
    
    {
        
        return parent::findViewFile($view, $context);
        
        
    }
    
    /**
     * 
     */
    public function renderPartial($_partial_, $_params_ = []){
        
        
        if(strpos($_partial_, '/') === false){ 
            $path = $this->theme->getViewPath('partials') ;
        }else{
            $path = \Yii::$app->viewPath . DIRECTORY_SEPARATOR . 'partials';
        }

        $partialFile = $path . DIRECTORY_SEPARATOR . trim($_partial_, DIRECTORY_SEPARATOR) . '.php';
 
        if(file_exists($partialFile)){ 
            return $this->renderPhpFile($partialFile, $_params_ );
        } 
    }
    
    public function renderPartialTemplate($partial, $renderDefault = false){
        $v = (new \yii\db\Query())->from(['a'=>'{{%ctemplate}}'])
        ->innerJoin(['b'=>'{{%ctemplate_to_shop}}'],'a.id=b.item_id')
        ->where(['b.shop_id'=>__SID__,'b.temp_id'=>__TID__,'a.type_code'=>$partial])->one();
        $state = false;
         
        
        if(!empty($v)){
            $partialFile = \Yii::$app->viewPath . '/partials'. ($this->isAmp ? '/amp/' : '/'). $v['name'] . '.php';
           
            if(file_exists($partialFile)){
                $state = true;
                switch ($v['status']){
                    case 1:
                        
                        return $this->renderPhpFile($partialFile);
                        break;
                    case 2: // Dev
                        if(\Yii::$app->user->can(ROOT_USER)){
                            return $this->renderPhpFile($partialFile);
                        }
                        break;
                    case 3: // Test
                        if(\Yii::$app->user->can([ROOT_USER,ADMIN_USER])){
                            return $this->renderPhpFile($partialFile);
                        }
                        break;                   
                }
                
            }
        }
        if(!$state && $renderDefault){
            return $this->renderPartial(strtolower($partial));
        }
        return false;
    }
    
    
    
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Yii::getAlias($viewFile);
        
        if ($this->theme !== null) {
            $viewFile = $this->theme->applyTo($viewFile);
        }
        if (is_file($viewFile)) {
            $viewFile = FileHelper::localize($viewFile);
        } else {
            throw new \yii\base\ViewNotFoundException("The view file does not exist: $viewFile");
        }
        //
        
        if ($context !== null) {
            $this->context = ($context);
        }
        $oldContext = $this->context;
        $output = '';
        $this->_viewFiles[] = $viewFile;
        
        if ($this->beforeRender($viewFile, $params)) {
            Yii::trace("Rendering view file: $viewFile", __METHOD__);
            $ext = pathinfo($viewFile, PATHINFO_EXTENSION);
            if (isset($this->renderers[$ext])) {
                if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
                    $this->renderers[$ext] = Yii::createObject($this->renderers[$ext]);
                }
                /* @var $renderer */
                $renderer = $this->renderers[$ext];
                $output = $renderer->render($this, $viewFile, $params);
            } else {
                $output = $this->renderPhpFile($viewFile, $params);
            }
            $this->afterRender($viewFile, $params, $output);
        }
        $output = $this->compressionHtml($output);
        
        array_pop($this->_viewFiles);
        $this->context = $oldContext;
        //
        return $output;
    }
    
    
    public function compressionHtml($body) {
         
        
        if(YII_DEBUG){
            return $body;
        }
        //remove redundant (white-space) characters
        $replace = array(
            //remove javascript comment
            //'/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/'=>'',
            '#\'([^\n\']*?)/\*([^\n\']*)\'#' => "'\1/'+\'\'+'*\2'", // remove comments from ' strings
            '#\"([^\n\"]*?)/\*([^\n\"]*)\"#' => '"\1/"+\'\'+"*\2"', // remove comments from " strings
            '#/\*.*?\*/#s'            => "",      // strip C style comments
            '#[\r\n]+#'               => "\n",    // remove blank lines and \r's
            '#\n([ \t]*//.*?\n)*#s'   => "\n",    // strip line comments (whole line only)
            '#([^\\])//([^\'"\n]*)\n#s' => "\\1\n",
            // strip line comments
            // (that aren't possibly in strings or regex's)
            '#\n\s+#'                 => "\n",    // strip excess whitespace
            '#\s+\n#'                 => "\n",    // strip excess whitespace
            '#(//[^\n]*\n)#s'         => "\\1\n", // extra line feed after any comments left
            // (important given later replacements)
           // '#/([\'"])\+\'\'\+([\'"])\*#' => "/*", // restore comments in strings
            //''=>'',
            //remove html comment
            '/<!--(.*)-->/Uis'=>'',
            //remove tabs before and after HTML tags
            '/\>[^\S ]+/s'   => '>',
            '/[^\S ]+\</s'   => '<',
            //shorten multiple whitespace sequences; keep new-line characters because they matter in JS!!!
            '/([\t ])+/s'  => ' ',
            //remove leading and trailing spaces
            '/^([\t ])+/m' => '',
            '/([\t ])+$/m' => '',
            // remove JS line comments (simple only); do NOT remove lines containing URL (e.g. 'src="http://server.com/"')!!!
            '~//[a-zA-Z0-9 ]+$~m' => '',
            //remove empty lines (sequence of line-end and white-space characters)
            '/[\r\n]+([\t ]?[\r\n]+)+/s'  => "\n",
            //remove empty lines (between HTML tags); cannot remove just any line-end characters because in inline JS they can matter!
            '/\>[\r\n\t ]+\</s'    => '><',
            //remove "empty" lines containing only JS's block end character; join with next line (e.g. "}\n}\n</script>" --> "}}</script>"
            '/}[\r\n\t ]+/s'  => '}',
            '/}[\r\n\t ]+,[\r\n\t ]+/s'  => '},',
            //remove new-line after JS's function or condition start; join with next line
            '/\)[\r\n\t ]?{[\r\n\t ]+/s'  => '){',
            '/,[\r\n\t ]?{[\r\n\t ]+/s'  => ',{',
            //remove new-line after JS's line end (only most obvious and safe cases)
            '/\),[\r\n\t ]+/s'  => '),',
            //remove quotes from HTML attributes that does not contain spaces; keep quotes around URLs!
            //		'~([\r\n\t ])?([a-zA-Z0-9]+)="([a-zA-Z0-9_/\\-]+)"([\r\n\t ])?~s' => '$1$2=$3$4',
            //$1 and $4 insert first white-space character found before/after attribute
        );
        $body = preg_replace(array_keys($replace), array_values($replace), $body);
        
        //remove optional ending tags (see http://www.w3.org/TR/html5/syntax.html#syntax-tag-omission )
        $remove = array(
            '</option>', '</li>', '</dt>', '</dd>', '</tr>', '</th>', '</td>'
        );
        $body = str_ireplace($remove, '', $body);
        
        return ($body);
    }
    
    
	public function renderMetaTag(){
		$iparams = [
	        __CLASS__,
	        __FUNCTION__,
// 	        FULL_URL,
// 	        __SID__,
	        date('d'),
	    ];
	    
	    $options = Yii::$app->icache->getCache($iparams);
	     
	    
	    if(!!empty($options)){
	        
	    
	     
	    $options = [
	        [
	            'name'=>"robots",
	            'content'=>'noodp'
	        ], 
	        [
	            'name'=>"title",
	            'content'=>Html::encode(get_site_value('seo/title',1,true))
	        ],
	        [
	            'name'=>"keywords",
	            'content'=>Html::encode(get_site_value('seo/keyword',1,true))
	        ],
	        [
	            'name'=>"description",
	            'content'=> Html::encode(get_site_value('seo/description',1,true))
	        ],
	        
	        [
	            'property'=>"og:type",
	            'content'=>'website'
	        ],
	        
	        [
	            'property'=>"og:locale",
	            'content'=>Yii::$app->l->locale
	        ],
	        
	        
	        
	        [
	            'property'=>"og:site_name",
	            'content'=>Html::encode(get_site_value('seo/site_name',1,true))
	        ],
	        
	        
	        
	        [
	            'property'=>"og:title",
	            'content'=>Html::encode(get_site_value('seo/title',1,true))
	        ],
	        
	        
	        
	        [
	            'property'=>"og:description",
	            'content'=>Html::encode(get_site_value('seo/description',1,true))
	        ],

	        
	        [
	            'property'=>"og:url",
	            'content'=>URL_WITH_PATH
	        ],
	         [
	             'http-equiv'=>"Content-Language",
	             'content'=>Yii::$app->language
	         ],
	        
	    ];
	    
	    
	    
	    $og_image = isset(Yii::$app->config['seo']['og_image']) ? Yii::$app->config['seo']['og_image'] : '';
	    if($og_image != ""){
	        
	        $og_image = getImage(['src'=>$og_image,'w'=>600,'save'=>true,'output'=>'src','absolute'=>true],true);
	        
	        $images = [
	            [
	                'property'=>'og:image',
	                'content'=>str_replace('https://', 'http://', $og_image)
	             ],
	            [
	                'property'=>'og:image:secure_url',
	                'content'=>str_replace('http://', 'https://', $og_image)
	            ],
	            
	            [
	                'property'=>'og:image:type',
	                'content'=>getMimeType($og_image)
	            ],
	            
	            [
	                'property'=>'og:image:alt',
	                'content'=>\yii\helpers\Html::encode(get_site_value('seo/title',1,true))
	            ],
	            
	            [
	                'name'=>'twitter:image',
	                 'content'=>$og_image
	            ],
	            
	            ];
	        
	        
	        $dims = getRemoteImageDimension($og_image);
	        
	        if(!empty($dims)){
	            $images[] = ['property'=>'og:image:width','content'=>$dims[0]];
	            $images[] = ['property'=>'og:image:height','content'=>$dims[1]];
	        }
	        $options =  array_merge($images, $options);
	    }
	    
	    // Twitter 
	    $options[] = ['name'=>'twitter:card', 'content'=>'summary'];
	    $options[] = ['name'=>'twitter:description', 'content'=>Html::encode(get_site_value('seo/description',1,true))];
	    $options[] = ['name'=>'twitter:title', 'content'=>Html::encode(get_site_value('seo/title',1,true))];
	    $options[] = ['name'=>'twitter:creator', 'content'=>(get_site_value('seo/author',1,true) != "" ? Html::encode(get_site_value('seo/author',1,true)) : 'iziweb.vn')];
	    
	    
	    $options[] = ['name'=>'copyright','content'=>(get_site_value('seo/copyright',1,true) != "" ? Html::encode(get_site_value('seo/copyright',1,true)) : Html::encode(get_site_value('seo/site_name',1,true)))];
	    $options[] = ['name'=>'author','content'=>(get_site_value('seo/author',1,true) != "" ? Html::encode(get_site_value('seo/author',1,true)) : 'iziweb.vn')];
	    $options[] = ['name'=>'designer','content'=>(get_site_value('seo/author',1,true) != "" ? Html::encode(get_site_value('seo/author',1,true)) : 'iziweb.vn')];
	    $options[] = ['name'=>'revisit-after','1 days'];
	    
	    
	    // DC Metatags 
	    $options[] = ['name'=>'DC.title','lang'=>Yii::$app->l->locale,'content'=>Html::encode(get_site_value('seo/title',1,true))];	    
	    $options[] = ['name'=>'DC:description','lang'=>Yii::$app->l->locale,'content'=>Html::encode(get_site_value('seo/description',1,true))];	    
	    $options[] = ['name'=>'DC.identifier','content'=>ABSOLUTE_DOMAIN];
	    $options[] = ['name'=>'DC.subject','lang'=>Yii::$app->l->locale,'content'=>Html::encode(get_site_value('seo/keyword',1,true))];
	    $options[] = ['name'=>'DC.language','lang'=>Yii::$app->l->locale,'content'=>Yii::$app->language];
	    $options[] = ['name'=>'DC.source','content'=>ABSOLUTE_DOMAIN];
	    $options[] = ['name'=>'DC.publisher','content'=>(get_site_value('seo/author',1,true) != "" ? Html::encode(get_site_value('seo/author',1,true)) : 'iziweb.vn')];
	    $options[] = ['name'=>'DC.contributor','content'=>(get_site_value('seo/author',1,true) != "" ? Html::encode(get_site_value('seo/author',1,true)) : 'iziweb.vn')];
	    $options[] = ['name'=>'DC.coverage','content'=>'World'];
	    
	    // Other
	    $options[] = ['name'=>'distribution','content'=>'global'];
	    $options[] = ['name'=>'alexa','content'=>10];
	    $options[] = ['name'=>'pagerank','content'=>10];
	    $options[] = ['name'=>'serps','content'=>'1,2,3,10,11,12,13,ATF'];
	    $options[] = ['name'=>'seoconsultantsdirectory','content'=>5];
	    
	    Yii::$app->icache->store($options ,$iparams);
	    
	    }
	    
	    foreach ($options as $option) {
	        $this->registerMetaTag($option);
	    }
	    
	}
    
	public function beforeEndBody(){
		$baseUrl = rtrim(Yii::$app->homeUrl,'/');
	    $identity_field = isset($this->params['identity_field']) ? $this->params['identity_field'] : 'id';
	    $cBaseUrl = __IS_MODULE__ ? (__DOMAIN_MODULE__ ? $baseUrl : "$baseUrl/" . Yii::$app->controller->module->id)
	    : $baseUrl;
		
		$cfg = array(
	        '_csrf-frontend' => Yii::$app->request->csrfToken,
		    'is_admin'=>defined('__IS_ADMIN__') ? __IS_ADMIN__ : false,
	        'isLoged' => !(Yii::$app->user->isGuest),
	        'domain_admin'=>__DOMAIN_ADMIN__,
	        'baseUrl'=>$baseUrl,
	        'absoluteUrl'=>rtrim(\yii\helpers\Url::home(true)),
	        'adminUrl'=> __DOMAIN_ADMIN__ ? $baseUrl : "$baseUrl/admin",
	        'moduleUrl'=> __DOMAIN_ADMIN__ ? $baseUrl : "$baseUrl/" . Yii::$app->controller->module->id,
	        'cBaseUrl'=> $cBaseUrl ,
	        'controller_text'=>defined('__RCONTROLLER__') ? __RCONTROLLER__ : '',
		    'module'=>Yii::$app->controller->module->id,
		    'controller'=>Yii::$app->controller->id,
	        'controllerUrl'=>URL_WITH_PATH,
		    'action'=>Yii::$app->controller->action->id,
	        //'controller_action'=>Yii::$app->controller->action->id,
	        //'assets'=>Yii::getAlias('@admin'),
	        'libsDir'=>'/libs',
		    'rsDir'	=> defined('__RSDIR__') ? __RSDIR__ : $this->theme->getBaseUrl(),
	        //'wheight'=>'%f%screen.height%f%',
	        //'wwidth'=>'%f%screen.width%f%',
	        'get'=>(Yii::$app->request->get()),
	        //'request'=>afGetUrl(),
	        //'returnUrl'=>afGetUrl([],[$identity_field,'view','language','currency']),
	        'sid'=>__SID__,
	        'time'=>date("d/m/Y H:i"),
	        'lang'=>__IS_MODULE__ ? MODULE_LANG : __LANG__,
	        'language'=>Yii::$app->l->getItem(__LANG__,true),
	        //'hl'=>Yii::$app->language,
	        'locale'=>Yii::$app->l->locale,
	        'browser'=>Yii::$app->getBrowser(),
	        //'text'=>$this->get_text_auto_load(),
	        //'currency'=>Yii::$app->c->default,
	        //'currencies'=>Yii::$app->c->getUserCurrency(),
	        'facebook_app'=>(isset($this->config->other_setting['facebook_app']) ? $this->config->other_setting['facebook_app'] : [
	            'appId'=>1729388797358505,
	            'version'=>'v3.1'
	        ]),
	        'debug'=>YII_DEBUG,
		    'editor'    =>  [
		        'skin'  =>  'icy_orange',
		        
		    ]
	    );
		
		echo '<script type="text/javascript">var $cfg=' .json_encode($cfg).'</script>';
		$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/less.js/3.7.1/less.min.js');
	    $this->registerJsFile('https://apis.google.com/js/platform.js',['position' => \yii\web\View::POS_END, 'async'=>'async', 'defer'=>'defer']);
	    

	}
	
	
	public function registerCssFile($url, $options = [], $key = null)
	{
	    $url = Yii::getAlias($url);
	    $key = $key ?: $url;
	    
	    $depends = \yii\helpers\ArrayHelper::remove($options, 'depends', []);
	    
	    
	    
	    $file_info = pathinfo ($url);
	    
	    switch ($file_info['extension'])
	    {
	        case 'less':
	            $options['rel'] = 'stylesheet/less';
	            break;
	    }
	    
	    if (empty($depends)) {
	         
	        $this->cssFiles[$key] = Html::cssFile($url, $options);
	    } else {
	        
	        
	        
	        $this->getAssetManager()->bundles[$key] = Yii::createObject([
	            'class' => \yii\web\AssetBundle::className(),
	            'baseUrl' => '',
	            'css' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
	            'cssOptions' => $options,
	            'depends' => (array) $depends,
	        ]);
	        $this->registerAssetBundle($key);
	    }
	}
	
	public function registerMinifyCssFiles($url, $option = []){
	    if(!is_array($url)) $url = [$url];
	    foreach ($url as $file) {
	        $this->minifyCssFiles[] = $file;
	    }
	}
	
	public function registerMinifyJsFiles($url, $option = []){
	    if(!is_array($url)) $url = [$url];
	    foreach ($url as $file) {
	        $this->minifyJsFiles[] = $file;
	    }
	}
	
}
