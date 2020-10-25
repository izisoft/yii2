<?php 

namespace izi\frontend;
use Yii;

use yii\bootstrap\NavBar;
use izi\bootstrap\Nav;
use yii\helpers\Html;

class Frontend extends \yii\base\Component
{
    
    private $_menu;
    
    public function getMenu(){ 
        if($this->_menu == null){
            $this->_menu = Yii::createObject('izi\\frontend\\models\\SiteMenu');
        }
        
        return $this->_menu;
    }
    
    
    private $_product;
    
    public function getProduct(){
        if($this->_product == null){
            $this->_product = Yii::createObject([
                'class'     => 'izi\\frontend\\Product',
                'frontend'  =>  $this
            ]);
        }
        
        return $this->_product;
    }
    
    private $_travel;
    
    public function getTravel(){
        if($this->_travel == null){
            $this->_travel = Yii::createObject([
                'class'     => 'izi\\frontend\\Travel',
                'frontend'  =>  $this
            ]);
        }
        
        return $this->_travel;
    }
    
    private $_advert;
    
    public function getAdvert(){
        if($this->_advert == null){
            $this->_advert = Yii::createObject('izi\\frontend\\models\\Advert');
        }
        
        return $this->_advert;
    }
    
    private $_redirect;
    
    public function getRedirect(){
        if($this->_redirect == null){
            $this->_redirect = Yii::createObject('izi\\frontend\\models\\Redirect');
        }
        
        return $this->_redirect;
    }
    
    private $_simonline;
    
    public function getSimonline(){
        if($this->_simonline == null){
            $this->_simonline = Yii::createObject('izi\\frontend\\sim\\Simonline');
        }
        
        return $this->_simonline;
    }
    
    /**
     * 
     * @var sitemap component 
     */
    private $_sitemap;
    
    public function getSitemap(){
        if($this->_sitemap == null){
            $this->_sitemap = Yii::createObject('izi\\frontend\\Sitemap');
        }
        
        return $this->_sitemap;
    }
    
    
    /**
     * Get articles
     * @param unknown $item_id
     * @param array $params
     */
    
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class' =>  'izi\\frontend\\models\\Articles',
                'frontend' => $this,
                'box'  =>  Yii::$app->box
            ]);
        }
        
        return $this->_model;
    }
    
    public function renderNavBar($params){
        return $this->bsNavBar($params);
    }
    public function navBar($params){
        return $this->bsNavBar($params);
    }
    
    
    public function normalNavBar($params){
         
        $position = isset($params['position']) ? $params['position'] : false;
        $heading = isset($params['heading']) ? $params['heading'] : [];
        
        $maxLevel = isset($params['maxLevel']) && $params['maxLevel'] > 0 && $params['maxLevel'] < 8 ? $params['maxLevel'] : 8;
        
        $cLevel = 0; // Current level
        
        $l1 = $this->getMenu()->getList([
            'position'=>$position,
            
        ]);
        
        if(empty($l1)){
        
            switch ($position) {
                case 'main':
                    $position = 'primary_menu';
                break;
                
                case 'bottom':
                    $position = 'footer_menu';
                    break;
            }
            
            $l1 = $this->getMenu()->getMenuLocation($position);
        }
         
        
        $menuItems = [];
        
        if(isset($params['ul']['before'])){
            $menuItems[] = $params['ul']['before'];
        }
        
        if(!empty($l1)){
            
            
            foreach ($l1 as $v1){
                $cLevel = 1;
                $items = [];
                
                $options = isset($params['li']['options']) ? $params['li']['options'] : [];
                
                if($cLevel < $maxLevel && !empty($l2 = $this->getMenu()->getList([
                    'parent_id'=>$v1['id'],
                    
                ]))){
                   
                    $items2 = [];
                    
                    if(isset($params['li']['optionsHasChild'])){
                        $options = $params['li']['optionsHasChild'];
                    }
                    
                    foreach ($l2 as $v2){
                        $cLevel = 2;
                        
                        if($cLevel < $maxLevel && !empty($l3 = $this->getMenu()->getList([
                            'parent_id'=>$v2['id'],
                            
                        ]))){
                            
                            
                            $items3 = [];
                            
                            foreach ($l3 as $v3){
                                $cLevel = 3;
                                if($cLevel < $maxLevel && !empty($l4 = $this->getMenu()->getList([
                                    'parent_id'=>$v3['id'],
                                    
                                ]))){
                                    
                                    
                                    $items4 = [];
                                    foreach ($l4 as $v4){
                                        $cLevel = 4;
                                        if($cLevel < $maxLevel && !empty($l5 = $this->getMenu()->getList([
                                            'parent_id'=>$v4['id'],
                                            
                                        ]))){
                                           
                                            $items5 = [];
                                            foreach ($l5 as $v5){
                                                $cLevel = 5;
                                                //                                                 if($cLevel < $maxLevel && !empty($l5 = $this->getMenu()->getList([
                                                //                                                     'parent_id'=>$v4['id'],
                                                
                                                //                                                 ]))){
                                                
                                                //                                                 }
                                                
                                                $items4[] = ['label'=>uh($v5['title']), 'url'=>$v5['url_link'], 'items'=>$items5];
                                            }
                                        }
                                        
                                        $items3[] = ['label'=>uh($v4['title']), 'url'=>$v4['url_link'], 'items'=>$items4];
                                    }
                                }
                                
                                $label = uh($v3['title']);
                                $encode = true;
                                $level = 3;
                                
                                if(isset($heading[$level])){
                                    
                                    if(is_array($heading[$level])){
                                        $hd = $heading[$level]['tag'];
                                        $headingOptions = isset($heading[$level]['options']) ? $heading[$level]['options'] : [];
                                    }else{
                                        $hd = $heading[$level];
                                        $headingOptions = [];
                                    }
                                    
                                    $label = Html::tag($hd, $label , $headingOptions);
                                    
                                    $encode = false;
                                    
                                }
                                
                                
                                
                                $items2[] = ['label'=>$label, 'options'=>isset($params['li']['li']['li']['options']) ? $params['li']['li']['li']['options'] : [], 'url'=>$v3['url_link'], 'items'=>$items3];
                            }
                        }
                        
                        
                        $label = uh($v2['title']);
                        $encode = true;
                        $level = 1;
                        
                        if(isset($heading[$level])){
                            
                            if(is_array($heading[$level])){
                                $hd = $heading[$level]['tag'];
                                $headingOptions = isset($heading[$level]['options']) ? $heading[$level]['options'] : [];
                            }else{
                                $hd = $heading[$level];
                                $headingOptions = [];
                            }
                            
                            $label = Html::tag($hd, $label , $headingOptions);
                            
                            $encode = false;
                            
                            
                        }
                        
                        if(isset($params['li']['li']['a']['before'])){
                            $encode = false;
                            $label = $params['li']['li']['a']['before'] . $label;
                        }
                        
                        
                        $items[] = ['label'=>$label, 'encode'=>$encode,'options'=>isset($params['li']['li']['options']) ? $params['li']['li']['options'] : [], 'url'=>$v2['url_link'], 'items'=>$items2];
                    }
                }
                
                $label = uh($v1['title']);
                $encode = true;
                $level = 0;
                if(isset($heading[$level])){
                    
                    
                    if(is_array($heading[$level])){
                        $hd = $heading[$level]['tag'];
                        $headingOptions = isset($heading[$level]['options']) ? $heading[$level]['options'] : [];
                        
                    }else{
                        $hd = $heading[$level];
                        $headingOptions = [];
                    }
                    
                    if(isset($params['li1Class'])){
                        
                    }
                    
                    $label = Html::tag($hd, $label , $headingOptions);
                    
                    $encode = false;
                    
                }
                
                $menuItems[] = [
                    'label'=>$label,
                    'encode'=>$encode,
                    'options'=>$options,
                    'url'=>$v1['url_link'],
                    'items'=>$items,
                    'dropDownOptions'=>isset($params['li']['ul']['options']) ? $params['li']['ul']['options'] : [],
                    'submenuOptions'    => isset($params['li']['ul']['options']) ? $params['li']['ul']['options'] : [],
                    //'params'  =>  [
                        'afterHtml' => isset($params['li']['ul']['afterHtml']) ? $params['li']['ul']['afterHtml'] :  '',
                        'beforeHtml' => isset($params['li']['ul']['beforeHtml']) ? $params['li']['ul']['beforeHtml'] :  '',
                    //],
                ];
                
            }
        }
        
        if(isset($params['ul']['after'])){
            $menuItems[] = $params['ul']['after'];
        }
        
        $params['options'] = isset($params['ul']['options']) ? $params['ul']['options'] : [];
        $params['submenuOptions'] = isset($params['li']['ul']['options']) ? $params['li']['ul']['options'] : [];
         
        
        //$tag = isset($params['beforeHtml']) ? $params['beforeHtml'] : '';
        $tag = $this->renderDropdownItems($menuItems, $params);
        //$tag .= isset($params['afterHtml']) ? $params['afterHtml'] : '';
        echo $tag;
         
    }
    
    protected function renderDropdownItems($items, $params = [], $level = 0){
        
        $lines = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $lines[] = $item;
                continue;
            }
            if (isset($item['visible']) && !$item['visible']) {
                continue;
            }
            if (!array_key_exists('label', $item)) {
                throw new \yii\base\InvalidConfigException("The 'label' option is required.");
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : true;
            $label = $encodeLabel ? \yii\bootstrap\Html::encode($item['label']) : $item['label'];
            $itemOptions = \yii\helpers\ArrayHelper::getValue($item, 'options', []);
            $linkOptions = \yii\helpers\ArrayHelper::getValue($item, 'linkOptions', []);
            $linkOptions['tabindex'] = '-1';
            $url = array_key_exists('url', $item) ? $item['url'] : null;
            
           
            if (empty($item['items'])) {
                if ($url === null) {
                    $content = $label;
                    \yii\bootstrap\Html::addCssClass($itemOptions, ['widget' => 'dropdown-header']);
                } else {
                    $content = \yii\bootstrap\Html::a($label, $url, $linkOptions);
                }
            }else {
                $submenuOptions = isset($params['submenuOptions']) ? $params['submenuOptions'] : [];
                if (isset($item['submenuOptions'])) {
                    $submenuOptions = array_merge($submenuOptions, $item['submenuOptions']);
                }
                $params2 = $params;
                $params2['options'] = $submenuOptions;
                
                $params2['beforeHtml'] = isset($item['beforeHtml']) ? $item['beforeHtml'] : '';
                $params2['afterHtml'] = isset($item['afterHtml']) ? $item['afterHtml'] : '';
                
                $content = \yii\bootstrap\Html::a($label, $url === null ? '#' : $url, $linkOptions)
                . $this->renderDropdownItems($item['items'], $params2, $level + 1);
                \yii\bootstrap\Html::addCssClass($itemOptions, ['widget' => 'dropdown-submenu']);
            }
            
           
            $lines[] = \yii\bootstrap\Html::tag('li', $content, $itemOptions);
            
            
        }
        
        
        $tag = '';
        
        $tag .= isset($params['beforeHtml']) ? $params['beforeHtml'] : '';
        
        $tag .= \yii\bootstrap\Html::tag('ul', implode("\n", $lines), isset($params['options']) ? $params['options'] : []);
 
        $tag .= isset($params['afterHtml']) ? $params['afterHtml'] : '';
                
        
        return $tag;
    }
    
    
    /**
     * 
     * @param array $params
     * @return string
     */
    
    public function showLogo($params = []){
        $divClass = isset($params['divClass']) ? $params['divClass'] : 'logo';
        $divId = isset($params['divId']) ? $params['divId'] : 'logo';
        $aClass = isset($params['aClass']) ? $params['aClass'] : randString(8);
        $aId = isset($params['aId']) ? $params['aId'] : randString(8);
        $w = isset($params['w']) ? $params['w'] : 0;
        $h = isset($params['h']) ? $params['h'] : 0;
        $default = isset($params['default']) ? $params['default'] : '';
        $sitename = isset(Yii::$app->cfg->contact['name']) ? Yii::$app->cfg->contact['name'] : DOMAIN;
        
        $logo = isset(Yii::$app->cfg->app['logo']['image']) ? Yii::$app->cfg->app['logo']['image']  : $default;
        
        $html = '<div class="'.$divClass.'" id="'.$divId.'">
        <a href="'.Yii::$app->homeUrl.'" title="'.$sitename.'" class="'.$aClass.'" id="'.$aId.'">
        '.getImage([
            'src'=>$logo != "" ? str_replace(['http://','https://'],[SCHEME . '://' ,SCHEME . "://"],$logo) : '',
            'w'=>$w, 'h'=>$h,
            'attrs'=>[
                'title'=>$sitename,
                'alt'=>'Logo ' . $sitename
            ]
        ]).'</a></div>';
        return $html;
    }
    
    public function showSlogan($params = []){
        $divClass = isset($params['divClass']) ? $params['divClass'] : 'slogan';
        $divId = isset($params['divId']) ? $params['divId'] : 'slogan';
        $aClass = isset($params['aClass']) ? $params['aClass'] : randString(8);
        $aId = isset($params['aId']) ? $params['aId'] : randString(8);
        $w = isset($params['w']) ? $params['w'] : 0;
        $h = isset($params['h']) ? $params['h'] : 0;
        $sitename = isset(Yii::$app->cfg->contact['name']) ? Yii::$app->cfg->contact['name'] : DOMAIN;
        
        $slogan = isset(Yii::$app->cfg->app['slogan']['text']) ? Yii::$app->cfg->app['slogan']['text']  : '';
        
        $html = '<div class="'.$divClass.'" id="'.$divId.'">'.uh($slogan,2).'</div>';
      return $html;
    }
    
    
    /**
     * bootstrap navbar
     * @param array $params
     */
    public function bsNavBar($params){
        
        NavBar::begin([
            'brandLabel' => isset($params['brandLabel']) ? $params['brandLabel'] : Yii::$app->name,
            'brandUrl' => isset($params['brandUrl']) ? $params['brandUrl'] : Yii::$app->homeUrl,
            'options' => isset($params['options']) ? $params['options'] : [
                'class' => 'navbar navbar-default',
            ],
            'innerContainerOptions'=>isset($params['innerContainerOptions']) ? $params['innerContainerOptions'] : [],
        ]);
        
        $position = isset($params['position']) ? $params['position'] : false;
        $heading = isset($params['heading']) ? $params['heading'] : [];
        
        $maxLevel = isset($params['maxLevel']) && $params['maxLevel'] > 0 && $params['maxLevel'] < 8 ? $params['maxLevel'] : 8;
        
        $cLevel = 0; // Current level
        
//         $primary_menu = Yii::$app->frontend->getMenu()->getMenuLocation('primary_menu');
        
        
        $l1 = $this->getMenu()->getMenuLocation([
            'position'=>$position,
            
        ]);
        $menuItems = [];
        
        if(!empty($l1)){
            
            $cLevel = 1;
            foreach ($l1 as $v1){
                $items = [];            
                $options = isset($params['li']['options']) ? $params['li']['options'] : [];
                if($cLevel < $maxLevel && !empty($l2 = $this->getMenu()->getList([
                    'parent_id'=>$v1['id'],
                    
                ]))){
                    $cLevel = 2;
                    $items2 = [];
                    
                    if(isset($params['li']['optionsHasChild'])){
                        $options = $params['li']['optionsHasChild'];
                    }
                    
                    foreach ($l2 as $v2){
                        
                        if($cLevel < $maxLevel && !empty($l3 = $this->getMenu()->getList([
                            'parent_id'=>$v2['id'],
                            
                        ]))){
                            
                            $cLevel = 3;
                            $items3 = [];
                            
                            foreach ($l3 as $v3){
                                if($cLevel < $maxLevel && !empty($l4 = $this->getMenu()->getList([
                                    'parent_id'=>$v3['id'],
                                    
                                ]))){
                                    
                                    $cLevel = 4;
                                    $items4 = [];
                                    foreach ($l4 as $v4){
                                        if($cLevel < $maxLevel && !empty($l5 = $this->getMenu()->getList([
                                            'parent_id'=>$v4['id'],
                                            
                                        ]))){
                                            $cLevel = 5;
                                            $items5 = [];
                                            foreach ($l5 as $v5){
//                                                 if($cLevel < $maxLevel && !empty($l5 = $this->getMenu()->getList([
//                                                     'parent_id'=>$v4['id'],
                                                    
//                                                 ]))){
                                                    
//                                                 }
                                                
                                                $items4[] = ['label'=>uh($v5['title']), 'url'=>$v5['url_link'], 'items'=>$items5];
                                            }
                                        }
                                        
                                        $items3[] = ['label'=>uh($v4['title']), 'url'=>$v4['url_link'], 'items'=>$items4];
                                    }
                                }
                                
                                $label = uh($v3['title']);
                                $encode = true;
                                $level = 3;
                                
                                if(isset($heading[$level])){
                                    
                                    if(is_array($heading[$level])){
                                        $hd = $heading[$level]['tag'];
                                        $headingOptions = isset($heading[$level]['options']) ? $heading[$level]['options'] : [];
                                    }else{
                                        $hd = $heading[$level];
                                        $headingOptions = [];
                                    }
                                    
                                    $label = Html::tag($hd, $label , $headingOptions);
                                    
                                    $encode = false;
                                    
                                }
                                
                                $items2[] = ['label'=>$label, 'options'=>isset($params['li']['li']['li']['options']) ? $params['li']['li']['li']['options'] : [], 'url'=>$v3['url_link'], 'items'=>$items3];
                            }
                        }
                        
                        
                        $label = uh($v2['title']);
                        $encode = true;
                        $level = 1;
                        
                        if(isset($heading[$level])){
                                                        
                            if(is_array($heading[$level])){
                                $hd = $heading[$level]['tag'];
                                $headingOptions = isset($heading[$level]['options']) ? $heading[$level]['options'] : [];                                
                            }else{
                                $hd = $heading[$level];
                                $headingOptions = [];
                            }                            
                            
                            $label = Html::tag($hd, $label , $headingOptions);
                            
                            $encode = false;
                            
                        } 
                        $items[] = ['label'=>$label, 'encode'=>$encode,'options'=>isset($params['li']['li']['options']) ? $params['li']['li']['options'] : [], 'url'=>$v2['url_link'], 'items'=>$items2];
                    }
                }
                
                $label = uh($v1['title']);
                $encode = true;
                $level = 0;
                if(isset($heading[$level])){
                     
                    
                    if(is_array($heading[$level])){
                        $hd = $heading[$level]['tag'];                      
                        $headingOptions = isset($heading[$level]['options']) ? $heading[$level]['options'] : [];
                        
                    }else{
                        $hd = $heading[$level];
                        $headingOptions = [];
                    }
                    
                    if(isset($params['li1Class'])){
                        
                    }
                                                            
                    $label = Html::tag($hd, $label , $headingOptions);
                     
                    $encode = false;
             
                } 
                
                $menuItems[] = [
                    'label'=>$label, 
                    'encode'=>$encode,
                    'options'=>$options, 
                    'url'=>$v1['url_link'], 
                    'items'=>$items,
                    'dropDownOptions'=>isset($params['li']['ul']['options']) ? $params['li']['ul']['options'] : [], 
                    'params'  =>  [
                        'afterHtml' => isset($params['li']['ul']['afterHtml']) ? $params['li']['ul']['afterHtml'] :  '',
                        'beforeHtml' => isset($params['li']['ul']['beforeHtml']) ? $params['li']['ul']['beforeHtml'] :  '',
                    ],
                ];
                
            }
        }
        
        if(isset($params['ul']['after'])){
            $menuItems[] = $params['ul']['after'];
        }
        
//         if (Yii::$app->user->isGuest) {
//             $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
//             $menuItems[] = ['label' => 'Login', 'url' => ['/news/login']];
//         } else {
//             $menuItems[] = '<li>'
//                 . Html::beginForm(['/site/logout'], 'post')
//                 . Html::submitButton(
//                     'Logout (' . Yii::$app->user->identity->username . ')',
//                     ['class' => 'btn btn-link logout']
//                     )
//                     . Html::endForm()
//                     . '</li>';
//         }
        echo Nav::widget([
            'options' =>isset($params['ul']['options']) ? $params['ul']['options'] :  ['class' => 'navbar-nav navbar-default'],
            'params'  =>  [
                'afterHtml' => isset($params['ul']['afterHtml']) ? $params['ul']['afterHtml'] :  '',
                'beforeHtml' => isset($params['ul']['beforeHtml']) ? $params['ul']['beforeHtml'] :  '',
             ],
            'items' => $menuItems,
            'dropDownCaret'=>isset($params['dropDownCaret']) ? $params['dropDownCaret'] : null,
            
        ]);
        NavBar::end();
    }
    
    /**
     * 
     */
     
    
    public function getBox($code){ 
        return Yii::$app->box->getBox($code);
    }
    
    public function getBoxCode($code, $params = [])
    {
        $box = Yii::$app->box->getBox($code, $params);
        
        $r = [];
        
        if(!empty($box)){
         
            $r['box'] = $box;
            
            $items = $this->getArticles(array_merge([
                'box'=>$box,
                'category_id'=>0
                
            ],$params));
            
            if(!empty($items)){
                foreach ($items as $k=>$v){
                    $r[$k] = $v;
                }
            }
            
            return $r;
        }
    }
    
    /**
	* box manager for frontend		
	*/
	
	public function getBoxs($params){
		
	    if(!is_array($params)){
	        $type = $params;
	        $params = [];
	    }
	     
	    $params['is_hidden'] = 0;$params['is_active'] = 1;
	    $module = isset($params['module']) ? $params['module'] : 'index';
	    $list_submenu = isset($params['list_submenu']) && $params['list_submenu'] == true ? true : false;
	    $limitSub= isset($params['limitSub']) ? $params['limitSub'] : 0;
	    
	    $listBox = Yii::$app->box->getModel()->getBoxModule($module, $params);
	    
	    $r = [];
	    
	    if(!empty($listBox)){
	        foreach ($listBox as $box){
	            
// 	            $items =
// 	            $this->getArticles(array_merge([
// 	                'box'=>$box,
// 	                'category_id'=>0
	                
// 	            ],$params));
	            
	            $items = $this->getPost(array_merge([
	                'box'=>$box,
	                'category_id'=>0
	                
	            ],$params));
	            
	            
	            if($list_submenu  && $box['menu_id'] > 0){
	                $items['list_submenu'] = $this->getMenu()->getList([
	                    'parent_id'=>$box['menu_id'],
	                    'limit'=>$limitSub
	                ]);
	            }
	            
	            $r[$box['code']] = $items;
	        }
	    }
	    
	    return $r;
	}
	
	public function renderSchemeJsonLD(){

       /// view2($contact,true);

        $html = ''; $jsonLD = [];
        $logoImage = isset(Yii::$app->cfg->app['logo']['image']) ? getAbsoluteUrl(Yii::$app->cfg->app['logo']['image'])  : '';
        $social = isset(Yii::$app->view->config->other_setting['social']) ? (Yii::$app->view->config->other_setting['social']) : [];
        $sameAs = [];
        if(is_array($social) && !empty($social)){

            foreach ($social as $k=>$v){
                if($v != ""){
                    $sameAs[] = $v;
                }
            }
        }
        // Common json
        // 1. Webpage
        
        $brd = Yii::$app->frontend->menu->getBreadcrumbs();
         
        $webpage = [
            "@context" => "http://schema.org",
            "@type" => "WebPage",
            //"name" => "A name. I use same as title tag",
            //"url" => "http://example.com",
            //"description" => "Description. I just use the same description as meta data",
            "name" => isset(Yii::$app->cfg->seo['real_title']) ? Yii::$app->cfg->seo['real_title'] : '' ,
            "description" => isset(Yii::$app->cfg->seo['real_description']) ? Yii::$app->cfg->seo['real_description'] : '' ,
            "url" => ABSOLUTE_DOMAIN,           
        ];
        $breadcrumb = [
            "@type" => "BreadcrumbList",
            'itemListElement' => []
        ];
        if(!empty($brd)){
            foreach ($brd as $k=>$v){
                $breadcrumb['itemListElement'][] = [
                    "@type" => "ListItem",
                    "position" => $k+1,
                    "item" => [
                        "@type" => "WebSite",
                        "@id" => isset($v['url_link']) ? getAbsoluteUrl($v['url_link']) : ABSOLUTE_DOMAIN,
                        "name" => uh($v['title'])

                    ]
                ];
            }
            $webpage['breadcrumb'] = $breadcrumb;
        }
		
        $contact = isset(Yii::$app->cfg->contact) ? Yii::$app->cfg->contact
        : (isset(Yii::$app->view->contact) ? Yii::$app->view->contact : null);
        
        $contactName = isset(Yii::$app->cfg->contact['short_name']) ? Yii::$app->cfg->contact['short_name'] 
        : ( isset($contact['short_name']) ? $contact['short_name'] : '');
		
        $publisher = [
            "@type" => "Organization",
            "name" => $contactName,
            'url' => ABSOLUTE_DOMAIN,
            "logo" => [
                "@type" => "imageObject",
                "url" => $logoImage
            ]
        ];
        if(__IS_DETAIL__ && !empty(Yii::$app->view->item)){
            $detailImg = '';
            
            $item = (array)Yii::$app->view->item;
            
            if(isset($item['icon'])){
            $img = getImageInfo(getAbsoluteUrl($item['icon']));


            $detailImg = isset($img[1]) && $img[1]> 0 ? ([
                "@type" => "imageObject",
                "url" => getAbsoluteUrl($item['icon']),
                "height" => $img[1],
                "width" => $img[0]
            ]) : getAbsoluteUrl($item['icon']);
            }
            
            if(!isset($item['info'])){
                $item['info'] = '';
            }

            $authName =  isset($item['post_by_name']) && $item['post_by_name'] != "" ? $item['post_by_name'] :
                         (isset($item['created_by']) ?  Yii::$app->user->getNameByUser($item['created_by']) : null);

            $mainEntity = [
                "@type" => "Article",
                "@id" => getAbsoluteUrl($item['url_link']),
                "author" =>$authName != null ? $authName : $contactName,
                "datePublished" =>date('c',strtotime($item['time'])),
                "dateModified" => date('c',strtotime($item['updated_at'])),
                "mainEntityOfPage" => getAbsoluteUrl(__CATEGORY_URL__),
                "headline" => uh($item['title']),
                "alternativeHeadline" => uh(isset($item['info']) ? strip_tags($item['info']) : ''),
                "name" => uh($item['title']),
                "image" => $detailImg,
                "publisher" => $publisher
            ];
            $webpage['mainEntity'] = $mainEntity;
        }
        // End webpage
        //$html .= '<script type="application/ld+json">' .(json_encode($webpage)) .'</script>';
        $jsonLD[] = $webpage;
        // WEBSITE
        $website = [
            "@context" => "http://schema.org",
            "@type" => "WebSite",
            "name" => $contactName,
            "url" => ABSOLUTE_DOMAIN,
            "sameAs" => $sameAs,
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => ABSOLUTE_DOMAIN . '/search?q={search_term_string}',
                "query-input" => "required name=search_term_string"
            ]
        ] ;
        // END WEBSITE
        //$html .= '<script type="application/ld+json">' .(json_encode($website)) .'</script>';
        $jsonLD[] = $website;

        $address = [
            "@type" => "PostalAddress",
            "addressCountry" => isset($contact['addressCountry']) ? $contact['addressCountry'] : "Việt Nam",
            "addressLocality"=>isset($contact['addressLocality']) ? $contact['addressLocality'] : "Hà Nội",
            "addressRegion" => isset($contact['addressRegion']) ? $contact['addressRegion'] : "Thanh Xuân",
            "postalCode" => isset($contact['postalCode']) ? $contact['postalCode'] : 100000,
            "streetAddress" => isset($contact['streetAddress']) ?
            $contact['streetAddress'] :
            (isset($contact['address']) ? $contact['address'] : '')
        ];

        $Organization = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            "@id" => ABSOLUTE_DOMAIN,
            "url" => ABSOLUTE_DOMAIN,
            "name" => $contactName,
            "description" => isset(Yii::$app->cfg->contact['description']) ? Yii::$app->cfg->contact['description'] : (isset($contact['description']) ? $contact['description'] : ''),
            "sameAs" => $sameAs,
            "logo" => ($logoImage),
            "address" => $address
        ];
        if(isset($contact['hotline']) && $contact['hotline'] != ''){
            $Organization['contactPoint'] = [
                "@type"=> "ContactPoint",
                "telephone"=> parsePhoneWithCountryCode($contact['hotline']),
                "contactType"=> "customer service"
            ];
        }
        // END $Organization
        //$html .= '<script type="application/ld+json">' .(json_encode($Organization)) .'</script>';
        $jsonLD[] = $Organization;

        
        $breadcrumb["@context"] = "http://schema.org";

        if(!empty($breadcrumb['itemListElement'])){
            $jsonLD[] = $breadcrumb;
        }
        $geo = [];
        if(isset($contact['latitude']) && $contact['latitude'] != "" && $contact['longitude'] != ""){
            $geo = [
                "@type" => "GeoCoordinates",
                "latitude" => $contact['latitude'],
                "longitude" => $contact['longitude']
            ];
        }
        // Json by page
        if(__IS_DETAIL__ && !empty(Yii::$app->view->item)){
            $text = '';
            if(isset($item['ctab'])){
                foreach($item['ctab'] as $d=>$t){
                    $text .= '<div class="box-details">'.uh($t['text'],2).'</div>';
                }}
                $authName =  isset($item['post_by_name']) && $item['post_by_name'] != "" ? $item['post_by_name'] : Yii::$app->user->getNameByUser($item['created_by']);

                $article = [
                    "@context" => "http://schema.org",
                    "@type" => "Article",
                    "headline" => uh($item['title']),
                    "alternativeHeadline" => uh(isset($item['info']) ? strip_tags($item['info']) : ''),
                    "name" => uh($item['title']),
                    "author" => [
                        "@type" => "Person",
                        "name" =>$authName != null ? $authName : $contactName,
                    ],
                    "datePublished" => date('c',strtotime($item['time'])),
                    "dateModified" => date('c',strtotime($item['updated_at'])),
                    "image" => isset($item['icon']) ? getAbsoluteUrl($item['icon']) : '',
                    "articleSection" => __CATEGORY_NAME__,
                    "description" => uh(isset($item['info']) ? strip_tags($item['info']) : ''),
                    "articleBody" => \yii\helpers\Html::encode(strip_tags($text)),
                    "url" => getAbsoluteUrl($item['url_link']),
                    "publisher" => $publisher,
                    "mainEntityOfPage" => [
                        "@type" => "WebPage",
                        "@id" => getAbsoluteUrl(__CATEGORY_URL__)
                    ],
                    //"aggregateRating" => $aggregateRating
                ];

                /**
                 * Vote product
                 *                 
                 */
                $rating = Yii::$app->vote->rating->getRating($item['id']);
                
                if($rating['avg']>0){
                    $aggregateRating = [
                        "@type" => "AggregateRating",
                        "ratingValue" => '"'. $rating['avg'] .'/' . $rating['max'] . '"',
                        "ratingCount" => $rating['total'],
                        "bestRating"=>$rating['max'],
                        'worstRating'=>$rating['min']
                    ];
                    $article['aggregateRating'] = $aggregateRating;
                }

                if($text == ""){
                    unset($article['articleBody']);
                }
                //$html .= '<script type="application/ld+json">' .(json_encode($article)) .'</script>';
                $jsonLD[] = $article;
                switch (Yii::$app->controller->id){
                    case 'index':

                        break;
                    case 'news':
                        $authName =  isset($item['post_by_name']) && $item['post_by_name'] != "" ? $item['post_by_name'] : Yii::$app->user->getNameByUser($item['created_by']);

                        $newsarticle = [
                        "@context" => "http://schema.org",
                        "@type" => "NewsArticle",
                        "name" => uh(strip_tags($item['title'])),
                        "headline" => uh($item['title']),
                        "alternativeHeadline" => uh(strip_tags($item['info'])),
                        "dateline" => $contactName,
                        "image" => [
                        getAbsoluteUrl($item['icon'])
                        ],
                        "datePublished" => date('c',strtotime($item['time'])),
                        "dateModified" => date('c',strtotime($item['updated_at'])),
                        "description" => uh(strip_tags($item['info'])),
                        "articleBody" => \yii\helpers\Html::encode(strip_tags($text)),
                        "url" => getAbsoluteUrl($item['url_link']),
                        "author" => [
                        "@type" => "Person",
                        "name" =>$authName != null ? $authName : $contactName,
                        ],
                        "publisher" => $publisher,
                        "mainEntityOfPage" => [
                        "@type" => "WebPage",
                        "@id" => getAbsoluteUrl(__CATEGORY_URL__)
                        ]
                        ];
                        //$html .= '<script type="application/ld+json">' .(json_encode($newsarticle)) .'</script>';
                        $jsonLD[] = $newsarticle;
                        break;
                    case 'products': case 'product':
                        $authName =  isset($item['post_by_name']) && $item['post_by_name'] != "" ? $item['post_by_name'] : Yii::$app->user->getNameByUser($item['created_by']);
                        
                        $product = [
                            "@context" => "http://schema.org",
                            "@type" => "Product",
                            "@id"   =>  "http://schema.org/productID",
                            "name" => uh(strip_tags($item['title'])),
                            "image" => isset($item['icon']) ? getAbsoluteUrl($item['icon']) : '',
                            "description"=>isset($item['info']) ? uh(strip_tags($item['info'])) : '',
                            "sku"=>$item['code'],
                            "productID"=>$item['code'],
                            "model" => isset(Yii::$app->view->category->title) ? uh(Yii::$app->view->category->title) : '',
                            "url"=>getAbsoluteUrl($item['url_link']),
                            
                            "offers" => [
                                "@type"=>"Offer",
                                "availability"=>"http://schema.org/InStock",
                                "price"=>round($item['price2'] , Yii::$app->currencies->getPrecision($item['currency'])),
                                "priceCurrency"=>Yii::$app->currencies->getCode($item['currency']),
                                "priceValidUntil" => date("Y-m-d", strtotime($item['updated_at']) + 365 * 5 * 86400),
                                "url"   =>  getAbsoluteUrl($item['url_link']), 
                                "seller"=>[
                                    "@type"=>"Organization",
                                    "name"=>$contact['name'],
//                                     "priceValidUntil"   =>  date("Y-m-d", strtotime($item['updated_at'])),
                                    'url'   =>  ABSOLUTE_DOMAIN,
                                ]
                            ]
                        ];
                        
                        $b = \app\modules\admin\models\Content::getItemProducer($item['id']);
                        if(!empty($b)){
                            $product['brand'] = $b['title'];
                        }
                        if($item['status']>0){
                            $product["itemCondition"] = readProductSchemaStatus($item['status']);
                        }else{
                            $product["itemCondition"] = readProductSchemaStatus(1);
                        }
                        $product['offers']["itemCondition"] = $product["itemCondition"];
                        
                        
                        $mpn = true;
                        
                        if(isset($item['barcode'])){
                            switch (strlen($item['barcode'])){
                                case 8:
                                    $product["gtin8"] = ($item['barcode']);
                                    $mpn = false;
                                    break;
                                case 12:
                                    $product["gtin12"] = ($item['barcode']);
                                    $mpn = false;
                                    break;
                                case 13:
                                    $product["gtin13"] = ($item['barcode']);
                                    $mpn = false;
                                    break;
                                case 14:
                                    $product["gtin14"] = ($item['barcode']);
                                    $mpn = false;
                                    break;
                                    
                            }
                        }
                        
                        
                        if($mpn && isset($item['mpn']) && $item['mpn'] != ""){
                            $product["mpn"] = readProductStatus($item['mpn']);
                        }
                        
                        
                        $jsonLD[] = $product;
                        break;
                }

        }



        // LocalBusiness
        $LocalBusiness = [
            "@context" => "http://schema.org",
            "@type" => "LocalBusiness",
            "address" => $address,
            "description" => isset($contact['description']) ? $contact['description'] : '',
            "name" => isset($contactName) && $contactName != "" ?
            $contactName : (isset($contact['name']) ? $contact['name'] : ''),
            "telephone"=> isset($contact['hotline']) ? parsePhoneWithCountryCode($contact['hotline']) : '',
            "url" => ABSOLUTE_DOMAIN,
            "image" => $logoImage,
            "logo" => $logoImage,
            //	"priceRange"=>isset($item['seo']['priceRange']) ? $item['seo']['priceRange'] : '0',
            "sameAs"=>$sameAs,
            "openingHours" => "Mo-Su",
            "aggregateRating" =>	[
                "@type" => "AggregateRating",
                "ratingValue" => '5/5' ,
                "ratingCount" => date('z') + (date('Y') - 2018) * 365,

            ]
        ];
        if(isset($item['seo']['priceRange']) && $item['seo']['priceRange'] != ""){
            $LocalBusiness['priceRange'] = $item['seo']['priceRange'];
        }
        if(!empty($geo)){
            $LocalBusiness['geo'] = $geo;
        }

        $jsonLD [] = $LocalBusiness;

        echo '<script type="application/ld+json">' .(json_encode($jsonLD, YII_DEBUG ? JSON_PRETTY_PRINT : null)) .'</script>';
    }

	public function registerCssJs(){
		echo get_site_value('seo/googleanalystics');
		$css = \app\models\SiteConfigs::getConfigs('CSS',null, __SID__ , true, true);
	    if(isset($css['code']) && trim($css['code'])!=""){
	        Yii::$app->view->registerCss(uh($css['code'],2),['type'=>'text/less']);
	    }
	}
    
	
	public function getPost($params)
	{
	    $entity_type = isset($params['entity_type']) ? $params['entity_type'] : (isset($params['type']) ? $params['type'] : Yii::$app->controller->id);
	    
	    $box_code = isset($params['box_code']) ? $params['box_code'] : '';
	    $box_params = isset($params['box_params']) && is_array($params['box_params']) ? $params['box_params'] : [];
	    $box_id = isset($params['box_id']) && $params['box_id'] > 0 ? $params['box_id'] : 0;
	    
	    $box = isset($params['box']) && is_array($params['box']) ? $params['box'] : [];
	    
	    if(empty($box) && strlen($box_code) > 0 ){
	        $box = $this->box->getBox($box_code, $box_params);
	    }elseif($box_id>0){
	        
	        //$box = $box2 = $this->box->getItem($box_id);
	        
	    }
	    
	    $vb = $params['box'] = $box;
	    
	    if(!empty($vb)){
	        
	        /**
	         * Thuộc tính type theo box có mức độ ưu tiên cao hơn
	         */
	        if(isset($vb['form']) && $vb['form'] != ""){
	            $entity_type = $vb['form'];
	        }
	        /**
	         * Kiển tra box có gán menu
	         */
	        if($vb['menu_id'] > 0){
	            $m = $this->menu->getItem($vb['menu_id']);
	            if(!empty($m)){
	                $entity_type = $m['type'];
	                //$action_detail = isset($m['action_detail']) && $m['action_detail'] != "" ? $m['action_detail'] : $action_detail;
	                $params['category_id'] = $vb['menu_id'];
	            }
	        }
	        
	        if($vb['limit'] > 0){
	            $params['limit'] = $vb['limit'];
	        }
	    }
	    

	    $params['entity_type'] = $entity_type;
	    
	    if(isset(Yii::$app->cfg->setting['form'][$entity_type])){
	        $version = Yii::$app->cfg->setting['form'][$entity_type]['version'];
	        
	        $method_name = "get" . ucfirst($entity_type) . ucfirst($version);
	        
	        if(method_exists($this, $method_name)){
	            return $this->$method_name($params);
	        }else{
	            return $this->getArticles($params);
	        }
	        
	    }else{
	        return $this->getArticles($params);
	    }
	}
	
	
	public function getProductsV2($param) {
	    $param['paging'] = true;
	    return Yii::$app->product->model->getListProduct($param);
	}
	
	public function getArticle($item_id , $params = [])
	{
	    return $this->getModel()->getItem($item_id, $params);
	}
	
	
	public function getArticles($params)
	{
	     
	    return $this->getModel()->getItems($params);
	}
	
	public function getTourPrices($item_id, $params = [])
	{
	    return $this->getModel()->getTourPrices($item_id, $params);
	}
	
	
	/**
	 * 123456  =>  123,456 VNĐ
	 *         =>  $123,456
	 * @param number $price
	 * @param number $currency
	 * @param boolean $showSymbol
	 * @return unknown|string|mixed|unknown|string
	 */
	
	public function showPrice($price = 0,$currency = -1, $params = []){
	    $text_translate = 2;
	    
	    /**
	     * 
	     */
	    
	    if(!is_array($params)){
	        $showSymbol = $params;
	        $params = [];
	    }else{	        
	        
	        $showSymbol = isset($params['showSymbol']) ? $params['showSymbol'] : true;
	    }
	    
	    
	    if(is_array($price)){
	        
	        $text_translate = isset($price['text_contact']) ? $price['text_contact'] : $text_translate;
	        $price = isset($price['price']) ? $price['price'] : 0;
	    }
	    $currency = $currency == -1 ? (Yii::$app->c->getDefaultCurrency()) : (Yii::$app->c->getCurrency($currency));
	    if(!is_numeric($price)) $price = cprice($price);
	    
	    $allow_zero = isset($params['allow_zero']) && $params['allow_zero'] == true ? true : false;
	    
	    if(!$allow_zero && !($price != 0)){
	        
	        /**
	         * 
	         */
	        
	        if(isset($params['text_zero'])){
	            return $params['text_zero'];
	        }
	        
	        //f[products][prices][zero][vi-VN]
	        $controller_code = is_array($price) && isset($price['controller_code']) ? $price['controller_code'] :
	        (defined('CONTROLLER_CODE') ? CONTROLLER_CODE : false);
	        //view(Yii::$app->config[$controller_code]);
	        if(isset(Yii::$app->config[$controller_code]['prices']['zero'][__LANG__]) && Yii::$app->config[$controller_code]['prices']['zero'][__LANG__] != ""){
	            return uh(Yii::$app->config[$controller_code]['prices']['zero'][__LANG__]);
	        }
	        
	        
	        
	        return is_numeric($text_translate) ? '' : Yii::$app->t->translate($text_translate);
	    }
	    
	    $display_type = isset($params['display_type']) ? $params['display_type'] : $currency['display_type'];
	    
	    switch ($display_type){
	        
	        case 1: $pre = ''; $after = $currency['symbol']; break;
	        case 2: $pre = ''; $after = $currency['code']; break;
	        case 3: $pre = $currency['symbol']; $after = ''; break;
	        case 4: $pre = $currency['code']; $after = ''; break;
	        case 5: $pre = ''; $after = $currency['symbol2']; break;
	        case 6: $pre = $currency['symbol2']; $after = ''; break;
	        case 7: $pre = ''; $after = ' ' . $currency['symbol2']; break;
	        case 8: $pre = ''; $after = ' ' . $currency['code']; break;
	    }
	    //         if(isset($currency['display']) && $currency['display'] == -1){
	    //             $pre = $symbol;
	    //             $after = '';
	    //         }else{
	    //             $pre = '';
	    //             $after = $symbol;
	    //         }
	    if(!$showSymbol) {
	        $pre = $after = '';
	    }
		 
	    $decimal = $currency['decimal_number'];
	    
	    $int = (int) $price;
	    
	    if($int == $price){
	        $decimal = 0;
	    }
	    
// 	    if($pre == '$'){
// 	        $pre = " $pre";
// 	    }	    
	    
	    return $pre . number_format($price, $decimal) . $after;
	}
	
	
	/**
	*	Get breadcrumb
	*/
	
	public function showBreadCrumbList($params = []){
        
        $version = isset($params['version']) ? $params['version'] : 3;
        
        switch ($version){
            case 4: return $this->showBreadCrumbsV4($params); break;
        }
        
        $l = $this->getMenu()->getBreadcrumbs();
        $li_append = isset($params['li_append']) ? $params['li_append'] : [];
        
        $li_prepend = isset($params['li_prepend']) ? $params['li_prepend'] : [];
        
        if(!empty($l)){
            $l = array_merge($li_prepend, $l ,$li_append);
        }else{
            $l = array_merge($li_prepend, $li_append);
        }
        
        $olClass = isset($params['olClass']) ? $params['olClass'] : 'breadcrumbs';
        
        $liClass = isset($params['liClass']) ? $params['liClass'] : '';
        
        $arrow  =   isset($params['arrow']) ? $params['arrow'] : '';
        
        $aTemplate = isset($params['aTemplate']) ? $params['aTemplate'] : '<a href="{{%LINK}}" >{{%TITLE}}</a>';
        
        $show_heading1 = isset($params['show_heading1']) ? $params['show_heading1'] : false;
        
        $containerClass = isset($params['containerClass']) ? $params['containerClass'] : '';
        
        $html = '<div class="breadcrumblist">'.(trim($containerClass) != "" ? '<div class="'.$containerClass.'">' : '').'<ol class="'.$olClass.'" >';
        if(!empty($l)){
            $i = 1;
            if(isset($params['show_home_page']) && $params['show_home_page'] === true){
                $html .= '<li class="'.$liClass.'">'.str_replace([
                    '{{%LINK}}', '{{%TITLE}}'
                ], [
                    Yii::$app->homeUrl, '<i class="fa fa-home"></i> '
                    . Yii::$app->t->translate('label_home')
                ], $aTemplate).'</li>';
                $html .= $arrow;                
            }
            
            foreach ($l as $k=>$v){
                
                $title = uh($v['title']);
                
                if($show_heading1 == true && $k == count($l)-1){ 
                    $title = "<h1 class=\"br-heading1\">$title</h1>";
                }
                
                $html .= '<li class="'.$liClass.'">'.str_replace([
                    '{{%LINK}}', '{{%TITLE}}'
                ], [
                    (isset($v['url_link']) ? getAbsoluteUrl($v['url_link']) : getAbsoluteUrl($this->getUrl($v['url']))),
                    $title
                ], $aTemplate).'</li>';
                
                if($k<count($l)-1){
                    $html .= $arrow;
                }
            }
        }
        $html .= '</ol>'.(trim($containerClass) != "" ? '</div>' : '').'</div>';
        
         
        
        return $html;
    }
    
    public function showBreadCrumbsV4($params = []){
        $l = $this->getMenu()->getReverseMenu();
   
        
        $li_append = isset($params['li_append']) ? $params['li_append'] : [];
        
        $li_prepend = isset($params['li_prepend']) ? $params['li_prepend'] : [];
        
        if(!empty($l)){
            $l = array_merge($li_prepend, $l ,$li_append);
        }else{
            $l = array_merge($li_prepend, $li_append);
        }
        
        $olClass = isset($params['olClass']) ? $params['olClass'] : 'breadcrumb';
        
        $liClass = isset($params['liClass']) ? $params['liClass'] : 'breadcrumb-item';
        
        $arrow  =   isset($params['arrow']) ? $params['arrow'] : '';
        
        $aTemplate = isset($params['aTemplate']) ? $params['aTemplate'] : '<a href="{{%LINK}}" >{{%TITLE}}</a>';
        
        $containerClass = isset($params['containerClass']) ? $params['containerClass'] : '';
        
        $html = '<nav aria-label="breadcrumb" class="breadcrumblist">'.(trim($containerClass) != "" ? '<div class="'.$containerClass.'">' : '').
        '<ol class="'.$olClass.'" >';
        if(!empty($l)){
            $i = 1;
            if(isset($params['show_home_page']) && $params['show_home_page'] === true){
                $html .= '<li class="'.$liClass.'">'.str_replace([
                    '{{%LINK}}', '{{%TITLE}}'
                ], [
                    Yii::$app->homeUrl, Yii::$app->t->translate('label_home')
                ], $aTemplate).'</li>';
                $html .= $arrow;
            }
            
            foreach ($l as $k=>$v){
                if($k<count($l)-1){
                    $html .= '<li class="'.$liClass.'">'.str_replace([
                        '{{%LINK}}', '{{%TITLE}}'
                    ], [
                        (isset($v['url_link']) ? getAbsoluteUrl($v['url_link']) : getAbsoluteUrl($this->getUrl($v['url']))),
                        uh($v['title'])
                    ], $aTemplate).'</li>';
                    
                    
                    $html .= $arrow;
                }else{
                    $html .= '<li class="breadcrumb-item active" aria-current="page">'.uh($v['title']).'</li>';
                }
            }
        }
        $html .= '</ol>'.(trim($containerClass) != "" ? '</div>' : '').'</nav>';
        return $html;
    }
    
    
	
	
	
    
    public function renderLocalSelect($o = []){
        
        $input_local_id = isset($o['input_local_id']) && is_array($o['input_local_id']) ? $o['input_local_id'] : [];
        $input_address = isset($o['input_address']) && is_array($o['input_address']) ? $o['input_address'] : [];
        
        
        $local_id = isset($o['local_id']) ? $o['local_id'] : 0;
        
        if($local_id == 0){
            $location = Yii::$app->geoip->lookupLocation();
             
            
            if(!empty($location)){
                $country = Yii::$app->local->model->findCountryByIso2($location->countryCode);
                if(!empty($country)){
                    $local_id = $country->id;
                    $city = Yii::$app->local->model->findCityByName($location->city , $country->id);
                    if(!empty($city)){
                        $local_id = $city->id;
                    }
                }
            }
        }
        
        $local = Yii::$app->local->parseCountry($local_id,234);
        $label = isset($o['label']) ? $o['label'] : 'Vị trí địa lý';
        $group_class = isset($o['group_class']) ? $o['group_class'] : '';
        $country_class = isset($o['country_class']) ? $o['country_class'] : '';
        $ajax_action = isset($o['ajax_action']) ? $o['ajax_action'] : 'ajax';
        $select_class = isset($o['select_class']) ? $o['select_class'] : 'col-lg-2 col-sm-3 col-xs-6';
        $fieldset_class = isset($o['fieldset_class']) ? $o['fieldset_class'] : 'mgb10';
        
        $html = '';
        $respon = randString(8);
        $rs_address = randString(8);
        $target = randString(8);$target4= randString(8);
        
        $html .= '<fieldset class="f12px '.$fieldset_class.'">
    <legend><b>'.$label.'</b></legend><div class="'.$group_class.'">';
        $html .= '<div class="'.$select_class.' mgb5 mgt5 '.$country_class.'">
<select data-allow_single_deselect="1" data-placeholder="Quốc gia" title="Quốc gia" data-first-loaded="1" data-first-changed="0"
class="form-control chosen-select '.$respon.'"
data-selected="'.$local['country']['id'].'"
data-target-selected="'.$local['province']['id'].'"
data-respon=".'.$respon.'"
data-target=".'.$target.'"
data-target2=".'.$target4.'"
data-role="v2-show-local"
data-ajax-action="'.$ajax_action.'"
onchange="call_ajax_function(this);log(this.value);"
data-current_level="0"
data-action="v2-load-local-country"><option value="0">-  chọn quốc gia  -</option>';
        foreach (Yii::$app->local->getCountries() as $k=>$v){
            $html .= '<option '.($local['country']['id'] == $v['id'] ? 'selected' : '').' value="'.$v['id'].'">'.Yii::$app->t->translate($v['lang_code']).' - '.Yii::$app->t->translate($v['lang_code'],__LANG2__).'</option>';
        }
        $html .= '</select></div>';
        
        $respon2 = randString(8);
        $target2 = randString(8);
        $html .= '<div class="'.$select_class.'  mgb5 mgt5">
<label class="d-md-none local-label">Chọn tỉnh / thành phố</label>
<select data-allow_single_deselect="1" data-placeholder="Tỉnh / Thành phố" title="Tỉnh / Thành phố" data-first-loaded="1" data-first-changed="0"
data-selected="'.$local['province']['id'].'"
data-target-selected="'.$local['district']['id'].'"
data-target=".'.$target2.'"
data-target2=".'.$target4.'"
data-role="v2-show-local"
data-ajax-action="'.$ajax_action.'"
onchange="call_ajax_function(this)"
data-current_level="1"
data-action="v2-load-local-country"
class="form-control chosen-select '.$target.'"><option value="0">-  chọn tỉnh / thành phố  -</option>';
        if($local['province']['id']>0){
            $html .= '<option selected value="'.$local['province']['id'].'">'.Yii::$app->local->showLocalName($local['province']['title'],$local['province']['type_id']).'</option>';
        }else{
            $html .= '<option></option>';
        }
        $html .= '</select></div>';
        
        $respon3 = randString(8);
        $target3 = randString(8);
        $html .= '<div class="'.$select_class.'  mgb5 mgt5">
<label class="d-md-none local-label">Chọn quận / huyện</label>
<select data-allow_single_deselect="1" data-placeholder="Quận / Huyện" title="Quận / Huyện" data-first-loaded="1" data-first-changed="0" data-disable_search_threshold="10"
data-selected="'.$local['district']['id'].'"
data-target-selected="'.$local['ward']['id'].'"
data-target=".'.$target3.'"
data-target2=".'.$target4.'"
data-ajax-action="'.$ajax_action.'"
onchange="call_ajax_function(this)"
data-action="v2-load-local-country"
data-current_level="2"
class="form-control chosen-select '.$target2.'"><option value="0">-  chọn quận / huyện  -</option>';
        if($local['district']['id']>0){
            $html .= '<option selected value="'.$local['district']['id'].'">'.Yii::$app->local->showLocalName($local['district']['title'],$local['district']['type_id']).'</option>';
        }else{
            $html .= '<option></option>';
        }
        $html .= '</select></div>';
        
        $html .= '<div class="'.$select_class.'  mgb5 mgt5">
<label class="d-md-none local-label">Chọn xã / phường</label>
<select data-allow_single_deselect="1" data-placeholder="Phường / Xã" title="Phường / Xã" data-first-loaded="1" data-first-changed="0" data-disable_search_threshold="10"
data-selected="'.$local['ward']['id'].'"
data-target-selected="'.$local['ward']['id'].'"
data-target2=".'.$target4.'"
onchange="call_ajax_function(this)"
data-action="v2-load-local-country"
data-ajax-action="'.$ajax_action.'"
data-current_level="3"
class="form-control chosen-select '.$target3.'"><option value="0">-  chọn xã / phường  -</option>';
        if($local['ward']['id']>0){
            $html .= '<option selected value="'.$local['ward']['id'].'">'.Yii::$app->local->showLocalName($local['ward']['title'],$local['ward']['type_id']).'</option>';
        }else{
            $html .= '<option></option>';
        }
        $html .= '</select></div>';
        if(!empty($input_local_id)){
            $name = isset($input_local_id['name']) ? $input_local_id['name'] : 'f[local_id]';
            $value = isset($input_local_id['value']) ? $input_local_id['value'] : $local_id;
            
            $html .= '<input onchange="call_ajax_function(this);" data-action="local_change_input_local_id" type="hidden" name="'.$name.'" value="'.$value.'" class="'.$target4.'"/>';
        }
        
        if(!empty($input_address)){
            $label = isset($input_address['label']) ? $input_address['label'] : '';
            $display_country = isset($input_address['display_country']) && !$input_address['display_country'] ? false : true;
            $name = isset($input_address['name']) ? $input_address['name'] : 'f[address]';
            $html .= $label != "" ? '<label class="col-sm-12 aleft control-label">'.$label.'</label>' : "";
            $value = isset($input_address['value']) ? $input_address['value'] : '';
            $input_value = isset($input_address['input_value']) ? $input_address['input_value'] : '';
            //view($display_country);
            $full_address = Yii::$app->local->showFullLocal($local_id, $input_value, ['display_country'=>$display_country]);
            $full_address_class = isset($o['full_address_class']) ? $o['full_address_class'] : '';
            $street_address_class = isset($o['street_address_class']) ? $o['street_address_class'] : '';
            
            $data = isset($o['data']) ? $o['data'] : '';
            
            $lang_us = isset($o['lang_us']) &&  !$o['lang_us'] ? false : true;
            
            $flag_us = isset($o['flag_us']) ? $o['flag_us'] : 'flag us';
            $flag_vn = isset($o['flag_vn']) ? $o['flag_vn'] : 'flag vn';
            
            $streets = isset($o['streets']) ? $o['streets'] : (isset($data['streets']) ? $data['streets'] : '');
            
            $html .= '<div class="col-sm-12">
                
<div class="input-group">
<div class="input-group-prepend">
    <span class="input-group-text input-group-addon-vn" title="Tiếng Việt"><i class="'.$flag_vn.'"></i></span>
  </div>
 
  <input type="text" name="'.$input_address['input_name'].'"
onblur="call_ajax_function(this)"
data-action="address_street_change"
data-target=".'.$rs_address.'"
data-local_id="'.$local_id.'"
class="form-control input-address-street-name ex_'.$target4.' '.$street_address_class.'"
placeholder="'.$label.'" value="'.$input_value.'">
    
<input type="hidden" value="'.$input_value.'" name="biz[streets]['.ROOT_LANG.']"/>
    
    
'.($lang_us ? '<div class="input-group-prepend"><span class="input-group-addon input-group-addon-us input-group-text" style="border-left: 0; border-right: 0;" title="Tiếng Anh"><i class="'.$flag_us.'"></i></span></div>
  <input type="text" value="'.(isset($streets['en-US']) ? $streets['en-US'] : '').'" class="form-control input-group-addon-us" name="biz[streets][en-US]" placeholder="Địa chỉ ghi bằng tiếng Anh" />
' : '') .'
    
    
</div>
    
    
    
<input type="hidden" class="input-address-full-name '.$rs_address.'" name="'.$name.'" value="'.$full_address.'"/>
<label class="control-label '.$full_address_class.'">Địa chỉ đầy đủ: <span class="green fulladdress_preview">'.$full_address.'</span></label>
</div>';
            
        }
        $html .= '<input type="hidden" class="auto_play_script_function" value="jQuery(\'.'.$respon.'\').change();"/>';
        $html .= '</div></fieldset>';
        
        
        
        echo $html;
    }
    
	
	
	
	
	
    
    public function renderModal($o = []){
        $modal = '';
        $ajax_action = isset($o['ajax_action']) ? $o['ajax_action'] : 'ajax';
        $action = isset($o['action']) ? $o['action'] : '';
        $class = isset($o['class']) ? $o['class'] : '';
        
        $center = isset($o['center']) ? $o['center'] : false;
        
        if($center){
            $class .= ' modal-dialog-centered';
        }
        
        $title = isset($o['title']) ? $o['title'] : '';
        $name = isset($o['name']) ? $o['name'] : 'mymodal';
        $body= '<div class="modal-body inline-block w100">' . (isset($o['body']) ? $o['body'] : '') .'</div>' ;
        $r = randString(12);
        $footer = isset($o['footer']) ? $o['footer'] : '';
        
        if(isset($_POST) && !empty($_POST)){
            if($action == ""){
                $action = 'quick-submit-' . post('action');
            }
            $_POST['action'] = $action;
            foreach ($_POST as $key=>$value){
                if(!is_array($value) ){
                    $footer .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
                }
                
            }
            
            $footer .= '<textarea name="request_post" class="hide">'.json_encode($_POST).'</textarea>';
            $footer .= '<input type="hidden" name="modal" value=".'.($r = randString(12)).'"/>';
        }
        
        $footer .= '</div></div></form></div>';
        $header = isset($o['header']) ? $o['header'] : '
<div class="modal-header">
<h5 class="modal-title">'.$title.'</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
 
</div>
    
';
        $form_class = isset($o['form_class']) ? $o['form_class'] :
        (isset($o['formClass']) ? $o['formClass'] : 'form-horizontal');
        
        
        $header = '<div class="modal fade in '.$name . " $r " .'" id="'.$name.'" tabindex="-1" role="dialog" aria-labelledby="'.$name.'Label">
<form data-action="'.$ajax_action.'" name="ajaxForm" action="/'.$ajax_action.'" class="ajaxForm '.$form_class.' f12px" method="post" onsubmit="return ajaxSubmitForm(this);">
<input type="hidden" name="_csrf-frontend" value="'.Yii::$app->request->csrfToken.'" />
<div class="modal-dialog '.$class.'" role="document"><div class="modal-content">
    
' . $header;
        
        return $header .'<div class="clear"></div>' . $body  .'<div class="clear"></div>' . $footer;
       
    }
	
	
	
    public function getTextRespon($o = []){
 
        
        $id = is_array($o) && isset($o['id']) ? $o['id'] : 0;
        
        $c = is_array($o) && isset($o['c']) ? $o['c'] : 0;
        
        
        $sid = is_array($o) && isset($o['sid']) ? $o['sid'] : __SID__;
        $category_id = is_array($o) && isset($o['category_id']) ? $o['category_id'] : 0;
        $lang = is_array($o) && isset($o['lang']) ? $o['lang'] : __LANG__;
        
        $default = is_array($o) && isset($o['default']) && $o['default'] == true ? true : false;
        $code = is_array($o) && isset($o['code']) ? $o['code'] : false;
        $list = is_array($o) && isset($o['list']) && $o['list'] == true ? true : false;
        $show = is_array($o) && isset($o['show']) && $o['show'] == false ? false : true;
        if(is_numeric($o) && $o > 0){
            $id = $o;
        }elseif (is_array($o)){
            
        }else {
            $code = $o;
        }
        
        $query = (new \yii\db\Query())->from(['a'=>'{{%form_design}}'])->where(['a.is_active'=>1]);
        
        if($lang !== false){
            $query->andWhere(['a.lang'=>$lang]);
        }
        
        if($show == false){
            $query->select(['a.*']);
        }else{
            $query->select(['a.value']);
        }
        if($code !== false){
            $query->innerJoin(['b'=>'{{%form_design_category}}'],'a.category_id=b.id');
            $query->andWhere(['b.code'=>$code]);
        }
        if($id>0) $query->andWhere(['a.id'=>$id]);
        if($category_id>0) $query->andWhere(['a.category_id'=>$category_id]);
        if($default){
            $query->andWhere(['a.state'=>2]);
        }else{
            $query->andWhere(['and', 'a.sid=' . $sid,['>','a.state',-2]]);
        }
        $query->orderBy(['a.title'=>SORT_ASC]);
        if($show) {
            $l = $query->scalar();
        }
        if($list){
            $l = $query->all();
        }else{
            $l = $query->one();
        }
        
        if($lang != null && empty($l) && is_array($o)){
            $o['lang2'] = $lang;
            $o['lang'] = false;
            $o['c'] = ++$c;
//             view($query->createCommand()->getRawSql(),1,1);
            if($c > 4){
                return;
//                 view($query->createCommand()->getRawSql(),'Count: ' . $c,1);
            }
            return $this->getTextRespon($o);
        }
        
        
        if(empty($l) && is_array($o) && !$default){
            $o['default'] = true;
            
            if(isset($o['lang2'])){
                $o['lang'] = $o['lang2'];
                unset($o['lang2']);
            }
            
            return $this->getTextRespon($o);
        }
        return $l;
    }
    
    
    /**
     * render adv slider
     */
   
    private $_sliderIndex = 0;
    public function renderSlider($params)
    {
        $this->_sliderIndex++;
        
        $code = isset($params['code']) ? $params['code'] : null;
        
        $category_id = isset($params['category_id']) ? $params['category_id'] : 0;
        
        $default_category_id = isset($params['default_category_id']) ? $params['default_category_id'] : -2;
        
        $id = isset($params['containerId']) ? $params['containerId'] : randString(6);         
        
        $type = isset($params['type']) ? $params['type'] : 'swiper';
        
        $itemClass = isset($params['itemClass']) ? $params['itemClass'] : '';
        
        $imgClass = isset($params['imgClass']) ? $params['imgClass'] : '';
        
        $aClass = isset($params['aClass']) ? $params['aClass'] : '';
        
        $containerClass = isset($params['containerClass']) ? $params['containerClass'] : randString(6);
        
        $wraperClass = isset($params['wraperClass']) ? $params['wraperClass'] : '';
        
        $paging = isset($params['paging']) && !$params['paging'] ? false : true;
        
        $arrows = isset($params['arrows']) && $params['arrows'] ? true : false;
        
        $pagingHtml = $arrowsHtml = '';
        
        $selector = $swiper = isset($params['selector']) ? $params['selector'] : randString(16);
        
        
        $l = isset($params['items']) && !empty($params['items']) ? $params['items'] :
        
        $this->getAdvert()->getItems([
            'code'=>$code,
            'category_id'=>$category_id,
            'default_category_id'=>$default_category_id,
        ]);
        
        if(empty($l)) return;
        
         
        
        switch ($type) {
           
            
            
            case 'swiper':
                $containerClass .= ' swiper-container ' . $swiper;
                $wraperClass .= ' swiper-wrapper';
                $itemClass .= ' swiper-slide';
                
                $jsOptions = '';
                
                if(isset($params['options']) && !empty($params['options'])){
                    foreach ($params['options'] as $key=>$value)
                    {
                        if(is_array($value)){
                            $jsOptions .= "$key:" . json_encode($value) . ',';
                        }else{
                            $jsOptions .= "$key: $value,";
                        }
                    }
                }
                
                if(isset($params['autoplay']) ){
                
                    $delay = is_numeric($params['autoplay']) ? $params['autoplay'] : 3500;
                    
                $jsOptions .= '
autoplay: {
        delay: '.$delay.',
        disableOnInteraction: false,
      },
';
                
                }
                
                if($paging){
                    $pagingHtml = '<div class="swiper-pagination"></div>';
                    $jsOptions .= '
pagination: {
        el: \'.swiper-pagination\',
        dynamicBullets: true,
},
';
                }
                if($arrows){
                    $arrowsHtml = '<div class="swiper-button-next"></div><div class="swiper-button-prev"></div>';
                    $jsOptions .= '
navigation: {
        nextEl: \'.swiper-button-next\',
        prevEl: \'.swiper-button-prev\',
      },
';
                }
                
                
                $jsOptions .= '
loop: '.(isset($params['loop']) && !$params['loop'] ? 'false' : 'true').'
';
                
                $view = Yii::$app->view;
                
                \izi\assets\SwiperAsset::register($view);
                
                
                if(!empty($l)){
                
                $view->registerJs(
<<<JS
 

var swiper_{$this->_sliderIndex} = new Swiper('.${swiper}', {
      ${jsOptions}
    });
JS
);
                
                $view->registerCss(
                    '
.swiper-container {
      width: 100%;
      height: 100%;
    }
    .swiper-slide {
      text-align: center;
      font-size: 18px;
      background: #fff;
      /* Center slide text vertically */
      display: -webkit-box;
      display: -ms-flexbox;
      display: -webkit-flex;
      display: flex;
      -webkit-box-pack: center;
      -ms-flex-pack: center;
      -webkit-justify-content: center;
      justify-content: center;
      -webkit-box-align: center;
      -ms-flex-align: center;
      -webkit-align-items: center;
      align-items: center;
    }
'
                    );
                
                }
                break;
        }
        
        $html = '';
         
        
        
        
        if(!empty($l)){
            $html .= '<div id="'.$id.'" class="'.strtolower($code).'_container '.$containerClass.'">';
            $html .= '<div class="adv-wraper '.$wraperClass.'">';
            
             
            foreach ($l as $v){                               
                
                $html .= '<div class="adv-item '.$itemClass.'"><div class="item-container">';
                
                if($showHref = (isset($v['link']) && !in_array($v['link'], ['', '#']))){
                    $html .= '<a href="'.$v['link'].'" target="'.(isset($v['target']) ? $v['target'] : '_self').'" class="'.$aClass.'">';
                }
                
                $html .= getImage([
                    'src'   =>  $v['image'],
                    'w'     =>  1600,                    
                    'attrs' =>  [
                        'title' =>  isset($v['title']) ? $v['title'] : '',
                        'alt'   =>  isset($v['title']) ? $v['title'] : '',
                        'data-lang'=>isset($v['lang']) ? $v['lang'] : __LANG__,
                        'class' =>  $imgClass,
                    ]
                ]);
                
                if($showHref) $html .= '</a>';
                
                if(isset($params['afterItem'])){
                    
                    $text = str_replace([
                        '{{%ITEM_LINK}}',
                        '{{%ITEM_TITLE}}'
                    ], [
                        $v['link'],
                        isset($v['title']) ? $v['title'] : ''
                    ], $params['afterItem']['template']);
                    
                    $html .= $text;
                }
                
                $html .= '</div></div>';
                
                
                
                
            }
            $html .= '</div>';
            
            // Paging
            $html .= $pagingHtml;
            // Arrow
            $html .= $arrowsHtml;
            
            $html .= '</div>';
            
            
            
        }
        
        if(isset($params['return'])){
            switch ($params['return']) {
                case 'html':
                    return $html;
                break;
              
            }
        }
        
        echo $html;
    }
	
    
    public function renderBoxTitle($box)
    {
        $html = '';
        
        if(!(isset($box['options']['title']['hidden']) && $box['options']['title']['hidden'])){        
            $html .= htmlspecialchars(str_replace('&quot;', '"', uh($box['title'])));             
            if(isset($box['options']['title']['heading']) && $box['options']['title']['heading'] !=""){  
                $heading = '<'. trim($box['options']['title']['heading']);
                if(isset($box['options']['title']['attrs']) && !empty($box['options']['title']['attrs'])){  
                    foreach ($box['options']['title']['attrs'] as $key=>$value){
                        $heading .= " $key=\"$value\"";
                    }
                }
                $heading .= '>';
                $html = $heading . $html . '</'.$box['options']['title']['heading'] . '>';
            }
            
        }

        
        echo $html;
    }
    
    
    public function renderSingleMenu($code, $params = [])
    {
        $html = '';
        //
        $menus = $this->getMenu()->getMenuLocation($code);
        
        if(!empty($menus)){
            
            $html .= '<ul';
            
            if(isset($params['ul']['options']) && !empty($params['ul']['options'])){
                foreach ($params['ul']['options'] as $k1=>$v1){
                    $html .= " $k1=\"$v1\" ";
                }
            }
            
            $html .= '>';
            
            foreach($menus as $menu){
                $html .= '<li';
                
                if(isset($params['li']['options']) && !empty($params['li']['options'])){
                    foreach ($params['li']['options'] as $k1=>$v1){
                        $html .= " $k1=\"$v1\" ";
                    }
                }
                
                $html .= '>';
                
                $html .= '<a href="'.$menu['url_link'].'"';
                
                if(isset($params['li']['a']['options']) && !empty($params['li']['a']['options'])){
                    foreach ($params['li']['a']['options'] as $k1=>$v1){
                        $html .= " $k1=\"$v1\" ";
                    }
                }
                if(isset($menu['target']) && !empty($menu['target'])){
                     
                    $html .= " target=\"${menu['target']}\" ";
                     
                }
                
                
                $html .= '>';
                
                $html .= uh($menu['title']);
                
                $html .= '</a>';
                $html .= '</li>';
            }
            
            $html .= '</ul>';
        }
        
        echo $html;
    }
    
    
    
    public function renderSimpleMenu($code, $params = [])
    {
        //
        $maxLevel = isset($params['maxLevel']) ? $params['maxLevel'] : 5;
        
        
        $html = '';
        //
        $menus = $this->getMenu()->getMenuLocation($code);
         
        
        if(!empty($menus)){
            
            $html .= '<ul';
            
            if(isset($params['ul']['options']) && !empty($params['ul']['options'])){
                foreach ($params['ul']['options'] as $k1=>$v1){
                    $html .= " $k1=\"$v1\" ";
                }
            }
            
            $html .= '>';
            
            foreach($menus as $menu){
                
                $cLevel = 1;
                
                $html .= '<li';
                
                if(isset($params['li']['options']) && !empty($params['li']['options'])){
                    foreach ($params['li']['options'] as $k1=>$v1){
                        $html .= " $k1=\"$v1\" ";
                    }
                }
                
                $html .= '>';
                
                $html .= '<a title="'.uh($menu['title']).'" href="'.$menu['url_link'].'"';
                
                if(isset($params['li']['a']['options']) && !empty($params['li']['a']['options'])){
                    foreach ($params['li']['a']['options'] as $k1=>$v1){
                        $html .= " $k1=\"$v1\" ";
                    }
                }
                if(isset($menu['target']) && !empty($menu['target'])){
                    
                    $html .= " target=\"${menu['target']}\" ";
                    
                }
                
                
                $html .= '>';
                
                $html .= uh($menu['title']);
                
                $html .= '</a>';
                
                // Level2
                if($cLevel < $maxLevel){
                    
                    $children = [];
                    
                    $autoChild = false;
                    
                    
                    if(isset($menu['children']) && !empty($menu['children'])){
                        $children = $menu['children'];
                        $autoChild = false;
                    }elseif(isset($menu['auto_show_children']) && $menu['auto_show_children'] == 'on'){
                        $children = $this->getMenu()->getList([
                            'parent_id'=>$menu['id'],
                            
                        ]);
                        
                        $autoChild = true;
                    }
                    //
                    if(!empty($children)){
                        $html .= '<ul';
                        
                        if(isset($params['ul']['ul']['options']) && !empty($params['ul']['ul']['options'])){
                            foreach ($params['ul']['ul']['options'] as $k1=>$v1){
                                $html .= " $k1=\"$v1\" ";
                            }
                        }
                        
                        $html .= '>';
                        
                        foreach($children as $v2){
                            
                            $cLevel = 2;
                            
                            $html .= '<li';
                            
                            if(isset($params['li']['li']['options']) && !empty($params['li']['li']['options'])){
                                foreach ($params['li']['li']['options'] as $k1=>$v1){
                                    $html .= " $k1=\"$v1\" ";
                                }
                            }
                            
                            $html .= '>';
                            
                            $html .= '<a  title="'.uh($v2['title']).'"  href="'.$v2['url_link'].'"';
                            
                            if(isset($params['li']['li']['a']['options']) && !empty($params['li']['li']['a']['options'])){
                                foreach ($params['li']['li']['a']['options'] as $k1=>$v1){
                                    $html .= " $k1=\"$v1\" ";
                                }
                            }
                            if(isset($v2['target']) && !empty($v2['target'])){
                                
                                $html .= " target=\"${v2['target']}\" ";
                                
                            }
                            
                            
                            $html .= '>';
                            
                            $html .= uh($v2['title']);
                            
                            $html .= '</a>';
                            
                            // Level 3
                            if($cLevel < $maxLevel){
                                
                                $children = [];
                                
                                $autoChild2 = false;
                                
                                if(isset($v2['children']) && !empty($v2['children'])){
                                    $children = $menu['children'];
                                }elseif((isset($v2['auto_show_children']) && $v2['auto_show_children'] == 'on') || $autoChild){
                                    $children = $this->getMenu()->getList([
                                        'parent_id'=>$v2['id'],
                                        
                                    ]);
                                    $autoChild2 = true;
                                }
                                //
                                if(!empty($children)){
                                    $html .= '<ul';
                                    
                                    if(isset($params['ul']['ul']['ul']['options']) && !empty($params['ul']['ul']['ul']['options'])){
                                        foreach ($params['ul']['ul']['ul']['options'] as $k1=>$v1){
                                            $html .= " $k1=\"$v1\" ";
                                        }
                                    }
                                    
                                    $html .= '>';
                                    
                                    foreach($children as $v3){
                                        
                                        $cLevel = 3;
                                        
                                        $html .= '<li';
                                        
                                        if(isset($params['li']['li']['li']['options']) && !empty($params['li']['li']['li']['options'])){
                                            foreach ($params['li']['li']['li']['options'] as $k1=>$v1){
                                                $html .= " $k1=\"$v1\" ";
                                            }
                                        }
                                        
                                        $html .= '>';
                                        
                                        $html .= '<a href="'.$v3['url_link'].'"';
                                        
                                        if(isset($params['li']['li']['li']['a']['options']) && !empty($params['li']['li']['li']['a']['options'])){
                                            foreach ($params['li']['li']['li']['a']['options'] as $k1=>$v1){
                                                $html .= " $k1=\"$v1\" ";
                                            }
                                        }
                                        if(isset($v3['target']) && !empty($v3['target'])){
                                            
                                            $html .= " target=\"${v3['target']}\" ";
                                            
                                        }
                                        
                                        
                                        $html .= '>';
                                        
                                        $html .= uh($v3['title']);
                                        
                                        $html .= '</a>';
                                        
                                        // Level 3
                                        
                                        
                                        $html .= '</li>';
                                    }
                                    
                                    $html .= '</ul>';
                                }
                            }
                            
                            $html .= '</li>';
                        }
                        
                        $html .= '</ul>';
                    }
                }
                
                
                $html .= '</li>';
            }
            
            $html .= '</ul>';
        }
        
        echo $html;
    }
    
    
    
    /**
     * 
     */
    public function renderSpecialText($text)
    { 
        //
        //{{%LIST_TOP_TOUR_BY_CATEGORY}}
        //
        $findme = '{{%LIST_TOP_TOUR_BY_CATEGORY}}';
        if(strpos($text, $findme) !== false){ 
            $partern = "$findme";
             
            //
            if(($category_id = Yii::$app->view->category->id) > 0){
                
                $l = Yii::$app->frontend->getArticles([
                    'p'=>1,
                    'type'=>'tours',
                    'category_id'=>$category_id,

                    'key'=>'limit-top-tour-level2',
                    'box_params' => [
                        'required'  =>  true,
                        'default'   =>  [
                            'title' =>  'Limit tour top level 2',
                            'limit' =>  6,
                        ]
                    ],
                    'count'=>true,
                ]);
                
                $l['category_id'] = $category_id;
                
                $l['show_heading'] = false;
                
                $l['output'] = 'text';
                 
                
                $text = str_replace($partern, Yii::$app->view->renderPartial('tours/top_tour', $l) , $text);
            }
            
            
        }
        
        
        //
        //{{%LIST_TOP_TOUR_BY_CATEGORY}}
        //
        $findme = '{{%LIST_TOUR_BY_CATEGORY}}';
      
        
        if(strpos($text, $findme) !== false){
            $partern = "$findme";
                        
            
            //
            if(($category_id = Yii::$app->view->category->id) > 0){
                
//                 $l = Yii::$app->frontend->getArticles([
//                     'p'=>1,
//                     'type'=>'tours',
//                     'category_id'=>$category_id,
                    
//                     'key'=>'limit-list-tour-level2',
//                     'box_params' => [
//                         'required'  =>  true,
//                         'default'   =>  [
//                             'title' =>  'Limit our tour level 2',
//                             'limit' =>  12,
//                         ]
//                     ],
//                     'count'=>true,
//                 ]);
                 
                
                $l['category_id'] = $category_id;
                
                $l['show_heading'] = false;
                
                $l['output'] = 'text';
                
                $l['column'] = 3;
                
                $text = str_replace($partern, Yii::$app->view->renderPartial('tours/our_trip', $l) , $text);
            }
            
            
        }
        
        
        
        //
        //{{%LIST_NEWS_SLIDER_BY_CATEGORY2}}
        //
        $findme = '{{%LIST_NEWS_SLIDER_BY_CATEGORY2}}';
        
        
        if(strpos($text, $findme) !== false){
            $partern = "$findme";
            
            //view(Yii::$app->view->category->menu_ex);
            //
            if(isset(Yii::$app->view->category->menu_ex) && ($category_id = Yii::$app->view->category->menu_ex)){
                
              
                
                $l['category_id'] = $category_id;
                
                $l['show_heading'] = false;
                
                $l['output'] = 'text';
                
                $l['column'] = 3;
                
                $text = str_replace($partern, Yii::$app->view->renderPartial('tours/news_slider', $l) , $text);
            }
            
            
        }
        
        
        echo uh($text,2);
    }
    
     
    
    public function getPaging($params){
        $bootstrap = isset($params['bootstrap']) ? $params['bootstrap']: 4;
        switch ($bootstrap){
            case 3: return $this->getPagingBootstrap3($params); break;
            default: return $this->getPagingBootstrap4($params); break;
        }
    }
    
    
    
    
    public function getPagingBootstrap3($o = []){
        //view(cu(['/tin-tuc/tin-tuc-moi-truong/tin-moi-truong']));
        $total_pages = isset($o['total_pages']) ? $o['total_pages'] : 1;
        $regex = isset($o['regex']) ? $o['regex'] : null;
        $p = isset($o['p']) ? $o['p'] : 1;
        $prev = $p>1 ? $p-1 : 1;
        $next = $p < $total_pages ? $p+1 : $total_pages;
        $html = '';
        if($total_pages>1){
            
            $limit_page = isset($o['limit_page']) && $o['limit_page']> 0 ? $o['limit_page'] : $total_pages;
            $part = intval($limit_page/2);
            $start_page = $p - $part;
            $start_page = $start_page > 0 ? $start_page : 1;
            $end_page = $p + $part;
            $end_page = $end_page < $total_pages ? $end_page: $total_pages;
            $end_page = $end_page < $limit_page ? $limit_page : $end_page;
            
            $html .= '<div class="clear"></div><div class="page_break page_break_cus1 ">
			<div class="btn-group" role="group" aria-label="First group">';
            $html .= '<a '.($p == 1 ? 'disabled' : '').' href="'.build_query(['p'=>$prev],['regex'=>$regex,'igrones'=>['view']]).'" class="btn btn-default"><i class="fa fa-long-arrow-left"></i></a>';
            for($i = $start_page;$i< $end_page+1; $i++){
                $html .= '<a '.($p == $i ? 'disabled' : '').' href="'.build_query(['p'=>$i],['regex'=>$regex,'igrones'=>['view']]).'" class="btn btn-default">'.$i.'</a>';
            }
            $html .= '<a '.($p == $total_pages ? 'disabled' : '').' href="'.build_query(['p'=>$next],['regex'=>$regex,'igrones'=>['view']]).'" class="btn btn-default"><i class="fa fa-long-arrow-right"></i></a>';
            $html .= '</div></div>';
        }
        return $html;
    }
    
    public function getPagingBootstrap4($params = []){

        $size = isset($params['size']) ? ($params['size']) : '';
        
        $path = isset($params['path']) ? $params['path'] : null;
        
        $root_link = isset($params['root_link']) ? $params['root_link'] : null;
        
        $alignment = isset($params['alignment']) ? ($params['alignment']) : 'justify-content-center';
        
        $class = isset($params['class']) ? ($params['class']) : '';
        
        $p = isset($params['p']) && $params['p']>1 ? $params['p'] : 1;
        
        $total_pages = isset($params['total_pages']) && $params['total_pages'] > 1 ? $params['total_pages'] : 1;
        
        $break_page = isset($params['break_page']) && $params['break_page'] > 1 ? $params['break_page'] : $total_pages;
        
        $start_page = 1; $end_page = $break_page;
        
        $middle = (int)($break_page/2);
        
        if($break_page < $total_pages){
            $start_page = $p - $middle ;
            
            $start_page = $start_page > 0 ? $start_page : 1;
            
            $end_page = $start_page + $break_page - 1;
            
            if($end_page > $total_pages){
                $end_page = $total_pages;
                $start_page = $end_page - $break_page+ 1;
                $start_page = $start_page > 0 ? $start_page : 1;
            }
        }
        
        $end_page = $end_page < $total_pages ? $end_page : $total_pages;
        
        $html = '';
        
        
        $show_first = true;
        if($start_page > 1){
            
        }else{
            $show_first = false;
        }
        
        
        $show_previous_page = $show_next_page = false;
        
        if($total_pages < $break_page){
            $show_previous_page = true;
            $show_next_page = true;
            $previous_label = Yii::$app->t->translate('label_short_previous_page');
            $next_label = Yii::$app->t->translate('label_short_next_page');
        }
        
        $html .= '
            
<nav aria-label="Page navigation" class="page-navigation '.$class.'">
  <ul class="pagination '.$alignment.' '.$size.'">';
        
        /**
         * First page
         */
        if($show_first){
            $html .= '<li class="page-item '.($p == 1 ? 'disabled' : '').'" title="'.Yii::$app->t->translate('label_page').' 1">
      <a class="page-link" href="'.build_query(['p'=>1],['path'=>$path]).'" aria-label="Previous">
        1
      </a>
          
    </li>';
        }
        
        
        
        
        if($start_page > 2 && $show_first){
            
            $html .= '<li class="page-item disabled">
      <a class="page-link" aria-label="Break">
        ...
      </a>
                
    </li>';
        }
        
        
        if($show_previous_page){
            
            $previous_page = $p - 1;
            
            $previous_page = $previous_page > 1 ? $previous_page : 1;
            
            $html .= '<li class="page-item '.($p == $previous_page ? 'disabled' : '').'" title="'.Yii::$app->t->translate('label_page').' '.$previous_page.'">
      <a class="page-link" href="'.build_query(['p'=>$previous_page],['path'=>$path]).'" aria-label="Previous">
        '.$previous_label.'
      </a>
            
    </li>';
        }
        
        
        
        for($page = $start_page; $page < $end_page + 1; $page++){
            
            $html .= '<li class="page-item '.($page == $p ? 'disabled' : '').'" title="'.Yii::$app->t->translate('label_page').' '.$page.'">
        <a class="page-link" href="'.build_query(['p'=>$page], ['path'=>$path]).'">'.number_format($page).'</a></li>';
        }
        
        
        if($show_next_page){
            
            $next_page = $p + 1;
            
            $next_page = $next_page > $total_pages ? $total_pages : $next_page;
            
            $html .= '<li class="page-item '.($p == $next_page ? 'disabled' : '').'" title="'.Yii::$app->t->translate('label_page').' '.$next_page.'">
      <a class="page-link" href="'.build_query(['p'=>$next_page],['path'=>$path]).'" aria-label="Previous">
        '.$next_label.'
      </a>
            
    </li>';
        }
        /**
         * Last page
         */
        if($end_page < $total_pages && $end_page < $total_pages - 2){
            
            
            $html .= '<li class="page-item disabled">
      <a class="page-link" aria-label="Break">
        ...
      </a>
                
    </li>';
        }
        
        
        
        
        if($end_page < $total_pages){
            $html .= '<li class="page-item '.($p == $total_pages ? 'disabled' : '').'" title="'.Yii::$app->t->translate('label_page').' '.$total_pages.'">
      <a class="page-link" href="'.build_query(['p'=>$total_pages],['path'=>$path]).'" aria-label="Next">
        '.$total_pages.'
      </a>
    </li>';
        }
        
        $html .= '</ul>
</nav>
            
';
        
        return $html;
    }
    
    
    
    
    
    public function showTextDetail($text = '',$id = 0, $o = []){
        $regex = [
            'http://' => SCHEME . '://',
            'https://' => SCHEME . '://',
            '"//' => '"' . SCHEME . '://',
//             '{LICH_KHOI_HANH}' => '',
//             '{LICH_KHAI_GIANG}' =>  $this->getLichKhaiGiang2(),
//             '{{LICH_KHOI_HANH_TOUR}}' => $this->getLichKhoiHanhTour($id,$o),
//             '{{CHI_TIET_TOUR}}' => $this->getChiTietTour($id,$o),
        ];
        
        
        return str_replace(array_keys($regex), array_values($regex) , $text);
    }
    
    public function showItemInfo($params = []){
        $updated_at = isset($params['updated_at']) ? $params['updated_at'] : false;
        $time = isset($params['time']) ? $params['time'] : false;
        $viewed = isset($params['viewed']) ? $params['viewed'] : 0;
        $comment = isset($params['comment']) ? $params['comment'] : 0;
        $post_by = isset($params['post_by']) ? $params['post_by'] : false;
        $short_info = isset($params['short_info']) && $params['short_info'] == false ? false : true;
        $url = isset($params['url']) ? getAbsoluteUrl($params['url']) : getAbsoluteUrl(Yii::$app->izi->getUrl( __DETAIL_URL__));
        
        $html = '<div class="entry-meta sitem-infomation">';
        $html .= $updated_at !== false ? '<span class="entry-date fa fa-history f14px">
		<time content="'.date('c',strtotime($updated_at)).'">'.count_time_post($updated_at).'</time></span>' : '';
        
        $html .= $post_by !== false ? '<span class="entry-view fa fa-user f14px"> '.uh($post_by).'</span>' : '';
        $html .= $viewed > 0 ? '<span class="entry-view fa fa-eye f14px"> '.number_format($viewed).' '.($short_info ? 'lượt xem' : '').'</span>' : '';
        //         $html .= isset(Yii::$app->view->info['short_name']) ?
        //         '<span class="hide" itemprop="publisher"
        // itemscope itemtype="http://schema.org/Organization">
        // <span>'.Yii::$app->view->info['short_name'] .'</span>
        
        // </span>' : '';
        $html .= $comment>0 ? '<span class="entry-comment fa fa-comments-o f14px"><a href="#">'.number_format($comment).' '.($short_info ? 'bình luận' : '').'</a></span>' : '';
        $html .= '</div>';
        return $html;
    }
    
    
    
    public function getUrl($url){
        if($url == ""){
            return ABSOLUTE_DOMAIN;
        }
        return \izi\models\Slug::getUrl($url);
    }
    
    
    /**
     * Old function getmenu
     */
    
    public function getMenuItem($o=[]){
        $key = isset($o['key']) ? $o['key'] : false;
        $maxLevel = isset($o['maxLevel']) && $o['maxLevel'] > 0 && $o['maxLevel'] < 8 ? $o['maxLevel'] : 8;
        $attrs = isset($o['attribute']) ? $o['attribute'] : (isset($o['attrs']) ? $o['attrs'] : []);
        $showIconClass = isset($o['showIconClass']) && $o['showIconClass'] == false ? false : true;
        $showIconClass2 = isset($o['showIconClass2']) && $o['showIconClass2'] == false ? false : true;
        $a1Class = isset($o['a1Class']) ? $o['a1Class'] : '';
        $listItem = isset($o['listItem']) ? $o['listItem'] :
        \app\models\SiteMenu::getList([
            'key'=>$key
        ]);
        $m = ''; $cLevel = 0;
        //
        $htag = isset($o['htag']) ? $o['htag'] : [];
        
        //
        if($cLevel < $maxLevel && !empty($listItem)){
            $m .= '<ul ';
            if(!empty($attrs)){
                foreach($attrs as $a=>$t){
                    $m .= $a .'="'.$t.'" ';
                }
            }			$m .= '>';
            $cLevel = 1;
            $m .= isset($o['firstItem']) ? $o['firstItem'] : '';
            foreach ($listItem as $k=>$v){
                // Check child
                $cLevel = 1;
                $l1 = \app\models\SiteMenu::getList([
                    'parent_id'=>$v['id']
                ]);
                
                $li1Class = !empty($l1) ? (isset($o['li1WithChildClass']) ? $o['li1WithChildClass'] : '') : (isset($o['li1NotChildClass']) ? $o['li1NotChildClass'] : '');
                //$liHasChild =
                $liActive = isset($o['activeClass']) && isset($o['activeClass']['li']) && in_array($v['url'],Yii::$app->request->get()) ? $o['activeClass']['li'] : '';
                $aActive = isset($o['activeClass']) && isset($o['activeClass']['a']) && in_array($v['url'],Yii::$app->request->get()) ? $o['activeClass']['a'] : '';
                $m .= '<li data-id="'.$v['id'].'" data-child="'.count($l1).'" class="li-child li-child-'.$k.' li-level-'.$cLevel.' '. $liActive.' '.(isset($o['li1Class']) ? $o['li1Class'] : '').' '.$li1Class.'">';
                if(isset($v['url_link'])){
                    $link = $v['url_link'];
                }else{
                    $link = $v['type'] == 'link' ? $v['link_target'] : cu([DS.$v['url']]);
                }
                $m .= '<a '.(isset($v['rel']) ? ' rel="'.$v['rel'].'"' : '').' '.(isset($v['target']) ? ' target="'.$v['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'  class="'.$aActive.' '.$a1Class.'">';
                
                if($showIconClass && isset($v['icon_class']) && $v['icon_class'] != ""){
                    $m .= '<i class="'.$v['icon_class'].'"></i> ';
                }
                
                $m .= isset($htag[0]) && $htag[0] != "" ? '<' . $htag[0] .'>' : '';
                $m .= uh($v['title']);
                $m .= isset($htag[0]) && $htag[0] != "" ?'</' . $htag[0] .'>' : '';
                $m .= '</a>';
                if($cLevel < $maxLevel && !empty($l1)){
                    $cLevel = 2;
                    
                    $m .= (isset($o['preUl2']) ? $o['preUl2'] : '');
                    
                    $m .= '<ul ';
                    if(isset($o['ul2Attr']) && !empty($o['ul2Attr'])){
                        foreach ($o['ul2Attr'] as $a=>$t){
                            $m .= $a .'="'.$t.'" ';
                        }
                    }
                    $m .= '>';
                    foreach ($l1 as $k1=>$v1){
                        $cLevel = 2;
                        $l2 = \app\models\SiteMenu::getList([
                            'parent_id'=>$v1['id']
                        ]);
                        //$link = $v1['type'] == 'link' ? $v1['link_target'] : cu([DS.$v1['url']]);
                        if(isset($v1['url_link'])){
                            $link = $v1['url_link'];
                        }else{
                            $link = $v1['type'] == 'link' ? $v1['link_target'] : cu([DS.$v1['url']]);
                        }
                        
                        $m .= '<li data-id="'.$v1['id'].'" data-child="'.count($l2).'" class="li-child li-child-'.$k1.' li-level-'.$cLevel.' '.(isset($o['li2Class']) ? $o['li2Class'] : '').'">';
                        $m .= '<a '.(isset($v1['rel']) ? ' rel="'.$v1['rel'].'"' : '').' '.(isset($v1['target']) ? ' target="'.$v1['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'>';
                        $m .= isset($o['a2Pre']) ? $o['a2Pre'] : '';
                        
                        if($showIconClass2 && isset($v1['icon_class']) && $v1['icon_class'] != ""){
                            $m .= '<i class="'.$v1['icon_class'].'"></i> ';
                        }
                        
                        $m .= uh($v1['title']);
                        $m .= isset($o['a2After']) ? $o['a2After'] : '';
                        //$m .= $eTag[0];
                        $m .= '</a>';
                        
                        if($cLevel < $maxLevel && !empty($l2)){
                            $cLevel = 3;
                            
                            $m .= '<ul >';
                            foreach ($l2 as $k2=>$v2){
                                $cLevel = 3;
                                $l3 = \app\models\SiteMenu::getList([
                                    'parent_id'=>$v2['id']
                                ]);
                                //$link = $v2['type'] == 'link' ? $v2['link_target'] : cu([DS.$v2['url']]);
                                if(isset($v2['url_link'])){
                                    $link = $v2['url_link'];
                                }else{
                                    $link = $v2['type'] == 'link' ? $v2['link_target'] : cu([DS.$v2['url']]);
                                }
                                $m .= '<li data-id="'.$v2['id'].'" data-child="'.count($l3).'" class="li-child li-child-'.$k2.' li-level-'.$cLevel.'">';
                                $m .= '<a '.(isset($v2['rel']) ? ' rel="'.$v2['rel'].'"' : '').' '.(isset($v2['target']) ? ' target="'.$v2['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'>';
                                //$m .= $hTag[0];
                                $m .= uh($v2['title']);
                                //$m .= $eTag[0];
                                $m .= '</a>';
                                
                                if($cLevel < $maxLevel && !empty($l3)){
                                    $cLevel = 4;
                                    
                                    $m .= '<ul >';
                                    foreach ($l3 as $k3=>$v3){
                                        $cLevel = 4;
                                        $l4 = \app\models\SiteMenu::getList([
                                            'parent_id'=>$v3['id']
                                        ]);
                                        //$link = $v3['type'] == 'link' ? $v3['link_target'] : cu([DS.$v3['url']]);
                                        if(isset($v['url_link'])){
                                            $link = $v3['url_link'];
                                        }else{
                                            $link = $v3['type'] == 'link' ? $v3['link_target'] : cu([DS.$v3['url']]);
                                        }
                                        $m .= '<li data-id="'.$v3['id'].'" data-child="'.count($l4).'" class="li-child li-child-'.$k3.' li-level-'.$cLevel.'">';
                                        $m .= '<a '.(isset($v3['rel']) ? ' rel="'.$v3['rel'].'"' : '').' '.(isset($v3['target']) ? ' target="'.$v3['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'>';
                                        //$m .= $hTag[0];
                                        $m .= uh($v3['title']);
                                        //$m .= $eTag[0];
                                        $m .= '</a>';
                                        
                                        
                                        $m.= '</li>';
                                    }
                                    $m .= '</ul>';
                                }
                                
                                
                                $m.= '</li>';
                            }
                            $m .= '</ul>';
                        }
                        
                        $m.= '</li>';
                    }
                    $m .= '</ul>';
                    //
                    $m .= (isset($o['afterUl2']) ? $o['afterUl2'] : '');
                }
                
                $m.= '</li>';
                // after ul1
            }
            if(isset($o['afterUl1'])){
                $m.= $o['afterUl1'];
            }
            $m .= '</ul>';
        }
        return $m;
    }
    
    
    /**
     * Update 05/08/2020
     */
    
    public function migrate($params)
    {
        if(!is_array($params['path'])){
            $params['path'] = [$params['path']];
        }
        foreach($params['path'] as $path){
            $data = json_decode(@file_get_contents($path), 1);
    
//             view(file_exists($path) , $path);
//             view($data, $path);
            
            if(!empty($data)){
                $model = new $data['model'];
                $model->migrate($data);
            }
        }
    }
    
    
    
	
}