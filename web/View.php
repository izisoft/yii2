<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\AssetBundle;
use yii\helpers\FileHelper;
use yii\web\JqueryAsset;

/**
 * View represents a view object in the MVC pattern.
 *
 * View provides a set of methods (e.g. [[render()]]) for rendering purpose.
 *
 * View is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->view`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ```php
 * 'view' => [
 *     'theme' => 'app\themes\MyTheme',
 *     'renderers' => [
 *         // you may add Smarty or Twig renderer here
 *     ]
 *     // ...
 * ]
 * ```
 *
 * For more details and usage information on View, see the [guide article on views](guide:structure-views).
 *
 * @property \yii\web\AssetManager $assetManager The asset manager. Defaults to the "assetManager" application
 * component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends \yii\base\View
{
    /**
     * izi config
     */
    private $_viewFiles = [];
    
    public $minifyCssFiles = [], $minifyJsFiles = [];
    
    public $hasAmp = false, $isAmp = false;    
    
    public $specialPage = ['amp','api'];
    
    
    /**
     * end izi config ( izi function goto end of file)
     */
    
    /**
     * @event Event an event that is triggered by [[beginBody()]].
     */
    const EVENT_BEGIN_BODY = 'beginBody';
    /**
     * @event Event an event that is triggered by [[endBody()]].
     */
    const EVENT_END_BODY = 'endBody';
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is in the head section.
     */
    const POS_HEAD = 1;
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the beginning of the body section.
     */
    const POS_BEGIN = 2;
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the end of the body section.
     */
    const POS_END = 3;
    /**
     * The location of registered JavaScript code block.
     * This means the JavaScript code block will be enclosed within `jQuery(document).ready()`.
     */
    const POS_READY = 4;
    /**
     * The location of registered JavaScript code block.
     * This means the JavaScript code block will be enclosed within `jQuery(window).load()`.
     */
    const POS_LOAD = 5;
    /**
     * This is internally used as the placeholder for receiving the content registered for the head section.
     */
    const PH_HEAD = '<![CDATA[YII-BLOCK-HEAD]]>';
    /**
     * This is internally used as the placeholder for receiving the content registered for the beginning of the body section.
     */
    const PH_BODY_BEGIN = '<![CDATA[YII-BLOCK-BODY-BEGIN]]>';
    /**
     * This is internally used as the placeholder for receiving the content registered for the end of the body section.
     */
    const PH_BODY_END = '<![CDATA[YII-BLOCK-BODY-END]]>';

    /**
     * @var AssetBundle[] list of the registered asset bundles. The keys are the bundle names, and the values
     * are the registered [[AssetBundle]] objects.
     * @see registerAssetBundle()
     */
    public $assetBundles = [];
    /**
     * @var string the page title
     */
    public $title;
    /**
     * @var array the registered meta tags.
     * @see registerMetaTag()
     */
    public $metaTags = [];
    /**
     * @var array the registered link tags.
     * @see registerLinkTag()
     */
    public $linkTags = [];
    /**
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css = [];
    /**
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles = [];
    /**
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js = [];
    /**
     * @var array the registered JS files.
     * @see registerJsFile()
     */
    public $jsFiles = [];

    private $_assetManager;


    /**
     * Marks the position of an HTML head section.
     */
    public function head()
    {
        echo self::PH_HEAD;
    }

    /**
     * Marks the beginning of an HTML body section.
     */
    public function beginBody()
    {
        echo self::PH_BODY_BEGIN;
        $this->trigger(self::EVENT_BEGIN_BODY);
    }

    /**
     * Marks the ending of an HTML body section.
     */
    public function endBody()
    {
		// register js before call enbody
		$this->beforeEndBody();
		
        $this->trigger(self::EVENT_END_BODY);
        echo self::PH_BODY_END;

        foreach (array_keys($this->assetBundles) as $bundle) {
            $this->registerAssetFiles($bundle);
        }
    }

    /**
     * Marks the ending of an HTML page.
     * @param bool $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     */
    public function endPage($ajaxMode = false)
    {
        $this->trigger(self::EVENT_END_PAGE);

        $content = ob_get_clean();

        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
        ]);

        
        if(defined('__IS_DETAIL__')){
            Yii::$app->statics->visited->updateVisited();
        }
        
        
        $this->clear();
    }

    /**
     * Renders a view in response to an AJAX request.
     *
     * This method is similar to [[render()]] except that it will surround the view being rendered
     * with the calls of [[beginPage()]], [[head()]], [[beginBody()]], [[endBody()]] and [[endPage()]].
     * By doing so, the method is able to inject into the rendering result with JS/CSS scripts and files
     * that are registered with the view.
     *
     * @param string $view the view name. Please refer to [[render()]] on how to specify this parameter.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @param object $context the context that the view should use for rendering the view. If null,
     * existing [[context]] will be used.
     * @return string the rendering result
     * @see render()
     */
    public function renderAjax($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);

        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $this->renderFile($viewFile, $params, $context);
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
    }

    /**
     * Registers the asset manager being used by this view object.
     * @return \yii\web\AssetManager the asset manager. Defaults to the "assetManager" application component.
     */
    public function getAssetManager()
    {
        return $this->_assetManager ?: Yii::$app->getAssetManager();
    }

    /**
     * Sets the asset manager.
     * @param \yii\web\AssetManager $value the asset manager
     */
    public function setAssetManager($value)
    {
        $this->_assetManager = $value;
    }

    /**
     * Clears up the registered meta tags, link tags, css/js scripts and files.
     */
    public function clear()
    {
        $this->metaTags = [];
        $this->linkTags = [];
        $this->css = [];
        $this->cssFiles = [];
        $this->js = [];
        $this->jsFiles = [];
        $this->assetBundles = [];
    }

    /**
     * Registers all files provided by an asset bundle including depending bundles files.
     * Removes a bundle from [[assetBundles]] once files are registered.
     * @param string $name name of the bundle to register
     */
    protected function registerAssetFiles($name)
    {
        if (!isset($this->assetBundles[$name])) {
            return;
        }
        $bundle = $this->assetBundles[$name];
        if ($bundle) {
            foreach ($bundle->depends as $dep) {
                $this->registerAssetFiles($dep);
            }
            $bundle->registerAssetFiles($this);
        }
        unset($this->assetBundles[$name]);
    }

    /**
     * Registers the named asset bundle.
     * All dependent asset bundles will be registered.
     * @param string $name the class name of the asset bundle (without the leading backslash)
     * @param int|null $position if set, this forces a minimum position for javascript files.
     * This will adjust depending assets javascript file position or fail if requirement can not be met.
     * If this is null, asset bundles position settings will not be changed.
     * See [[registerJsFile]] for more details on javascript position.
     * @return AssetBundle the registered asset bundle instance
     * @throws InvalidConfigException if the asset bundle does not exist or a circular dependency is detected
     */
    public function registerAssetBundle($name, $position = null)
    {
        if (!isset($this->assetBundles[$name])) {
            $am = $this->getAssetManager();
            
            
            
            $bundle = $am->getBundle($name);
             
            
            $this->assetBundles[$name] = false;
           
            
            // register dependencies
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
            $this->assetBundles[$name] = $bundle;
        } elseif ($this->assetBundles[$name] === false) {
            throw new InvalidConfigException("A circular dependency is detected for bundle '$name'.");
        } else {
            $bundle = $this->assetBundles[$name];
        }

        if ($position !== null) {
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            if ($pos === null) {
                $bundle->jsOptions['position'] = $pos = $position;
            } elseif ($pos > $position) {
                throw new InvalidConfigException("An asset bundle that depends on '$name' has a higher javascript file position configured than '$name'.");
            }
            // update position for all dependencies
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
        }
 
		
        return $bundle;
    }

    /**
     * Registers a meta tag.
     *
     * For example, a description meta tag can be added like the following:
     *
     * ```php
     * $view->registerMetaTag([
     *     'name' => 'description',
     *     'content' => 'This website is about funny raccoons.'
     * ]);
     * ```
     *
     * will result in the meta tag `<meta name="description" content="This website is about funny raccoons.">`.
     *
     * @param array $options the HTML attributes for the meta tag.
     * @param string $key the key that identifies the meta tag. If two meta tags are registered
     * with the same key, the latter will overwrite the former. If this is null, the new meta tag
     * will be appended to the existing ones.
     */
    public function registerMetaTag($options, $key = null)
    {
        if ($key === null) {
            $this->metaTags[] = Html::tag('meta', '', $options);
        } else {
            $this->metaTags[$key] = Html::tag('meta', '', $options);
        }
    }

    /**
     * Registers CSRF meta tags.
     * They are rendered dynamically to retrieve a new CSRF token for each request.
     *
     * ```php
     * $view->registerCsrfMetaTags();
     * ```
     *
     * The above code will result in `<meta name="csrf-param" content="[yii\web\Request::$csrfParam]">`
     * and `<meta name="csrf-token" content="tTNpWKpdy-bx8ZmIq9R72...K1y8IP3XGkzZA==">` added to the page.
     *
     * Note: Hidden CSRF input of ActiveForm will be automatically refreshed by calling `window.yii.refreshCsrfToken()`
     * from `yii.js`.
     *
     * @since 2.0.13
     */
    public function registerCsrfMetaTags()
    {
        $this->metaTags['csrf_meta_tags'] = $this->renderDynamic('return yii\helpers\Html::csrfMetaTags();');
    }

    /**
     * Registers a link tag.
     *
     * For example, a link tag for a custom [favicon](http://www.w3.org/2005/10/howto-favicon)
     * can be added like the following:
     *
     * ```php
     * $view->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/myicon.png']);
     * ```
     *
     * which will result in the following HTML: `<link rel="icon" type="image/png" href="/myicon.png">`.
     *
     * **Note:** To register link tags for CSS stylesheets, use [[registerCssFile()]] instead, which
     * has more options for this kind of link tag.
     *
     * @param array $options the HTML attributes for the link tag.
     * @param string $key the key that identifies the link tag. If two link tags are registered
     * with the same key, the latter will overwrite the former. If this is null, the new link tag
     * will be appended to the existing ones.
     */
    public function registerLinkTag($options, $key = null)
    {
        if ($key === null) {
            $this->linkTags[] = Html::tag('link', '', $options);
        } else {
            $this->linkTags[$key] = Html::tag('link', '', $options);
        }
    }

    /**
     * Registers a CSS code block.
     * @param string $css the content of the CSS code block to be registered
     * @param array $options the HTML attributes for the `<style>`-tag.
     * @param string $key the key that identifies the CSS code block. If null, it will use
     * $css as the key. If two CSS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerCss($css, $options = [], $key = null)
    {
        $key = $key ?: md5($css);
        $this->css[$key] = Html::style($css, $options);
    }

    /**
     * Registers a CSS file.
     *
     * This method should be used for simple registration of CSS files. If you want to use features of
     * [[AssetManager]] like appending timestamps to the URL and file publishing options, use [[AssetBundle]]
     * and [[registerAssetBundle()]] instead.
     *
     * @param string $url the CSS file to be registered.
     * @param array $options the HTML attributes for the link tag. Please refer to [[Html::cssFile()]] for
     * the supported options. The following options are specially handled and are not treated as HTML attributes:
     *
     * - `depends`: array, specifies the names of the asset bundles that this CSS file depends on.
     *
     * @param string $key the key that identifies the CSS script file. If null, it will use
     * $url as the key. If two CSS files are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;

        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
             
            $this->cssFiles[$key] = Html::cssFile($url, $options);
        } else {
            
            
            
            $this->getAssetManager()->bundles[$key] = Yii::createObject([
                'class' => AssetBundle::className(),
                'baseUrl' => '',
                'css' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'cssOptions' => $options,
                'depends' => (array) $depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Registers a JS code block.
     * @param string $js the JS code block to be registered
     * @param int $position the position at which the JS script tag should be inserted
     * in a page. The possible values are:
     *
     * - [[POS_HEAD]]: in the head section
     * - [[POS_BEGIN]]: at the beginning of the body section
     * - [[POS_END]]: at the end of the body section
     * - [[POS_LOAD]]: enclosed within jQuery(window).load().
     *   Note that by using this position, the method will automatically register the jQuery js file.
     * - [[POS_READY]]: enclosed within jQuery(document).ready(). This is the default value.
     *   Note that by using this position, the method will automatically register the jQuery js file.
     *
     * @param string $key the key that identifies the JS code block. If null, it will use
     * $js as the key. If two JS code blocks are registered with the same key, the latter
     * will overwrite the former.
     */
    public function registerJs($js, $position = self::POS_READY, $key = null)
    {
        $key = $key ?: md5($js);
        $this->js[$position][$key] = $js;
        if ($position === self::POS_READY || $position === self::POS_LOAD) {
            JqueryAsset::register($this);
        }
    }

    /**
     * Registers a JS file.
     *
     * This method should be used for simple registration of JS files. If you want to use features of
     * [[AssetManager]] like appending timestamps to the URL and file publishing options, use [[AssetBundle]]
     * and [[registerAssetBundle()]] instead.
     *
     * @param string $url the JS file to be registered.
     * @param array $options the HTML attributes for the script tag. The following options are specially handled
     * and are not treated as HTML attributes:
     *
     * - `depends`: array, specifies the names of the asset bundles that this JS file depends on.
     * - `position`: specifies where the JS script tag should be inserted in a page. The possible values are:
     *     * [[POS_HEAD]]: in the head section
     *     * [[POS_BEGIN]]: at the beginning of the body section
     *     * [[POS_END]]: at the end of the body section. This is the default value.
     *
     * Please refer to [[Html::jsFile()]] for other supported options.
     *
     * @param string $key the key that identifies the JS script file. If null, it will use
     * $url as the key. If two JS files are registered with the same key at the same position, the latter
     * will overwrite the former. Note that position option takes precedence, thus files registered with the same key,
     * but different position option will not override each other.
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;

        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            $this->jsFiles[$position][$key] = Html::jsFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = Yii::createObject([
                'class' => AssetBundle::className(),
                'baseUrl' => '',
                'js' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'jsOptions' => $options,
                'depends' => (array) $depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Registers a JS code block defining a variable. The name of variable will be
     * used as key, preventing duplicated variable names.
     *
     * @param string $name Name of the variable
     * @param array|string $value Value of the variable
     * @param int $position the position in a page at which the JavaScript variable should be inserted.
     * The possible values are:
     *
     * - [[POS_HEAD]]: in the head section. This is the default value.
     * - [[POS_BEGIN]]: at the beginning of the body section.
     * - [[POS_END]]: at the end of the body section.
     * - [[POS_LOAD]]: enclosed within jQuery(window).load().
     *   Note that by using this position, the method will automatically register the jQuery js file.
     * - [[POS_READY]]: enclosed within jQuery(document).ready().
     *   Note that by using this position, the method will automatically register the jQuery js file.
     *
     * @since 2.0.14
     */
    public function registerJsVar($name, $value, $position = self::POS_HEAD)
    {
        $js = sprintf('var %s = %s;', $name, \yii\helpers\Json::htmlEncode($value));
        $this->registerJs($js, $position, $name);
    }

    /**
     * Renders the content to be inserted in the head section.
     * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderHeadHtml()
    {
        $lines = [];
        if (!empty($this->metaTags)) {
            $lines[] = implode("\n", $this->metaTags);
        }

        if (!empty($this->linkTags)) {
            $lines[] = implode("\n", $this->linkTags);
        }
        if (!empty($this->cssFiles)) {
            $lines[] = implode("\n", $this->cssFiles);
        }
        if (!empty($this->css)) {
            $lines[] = implode("\n", $this->css);
        }
        if (!empty($this->jsFiles[self::POS_HEAD])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_HEAD]);
        }
        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]));
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the beginning of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderBodyBeginHtml()
    {
        $lines = [];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]));
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @param bool $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     * @return string the rendered content
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
        $lines = [];

        if (!empty($this->jsFiles[self::POS_END])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_END]);
        }

        if ($ajaxMode) {
            $scripts = [];
            if (!empty($this->js[self::POS_END])) {
                $scripts[] = implode("\n", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $scripts[] = implode("\n", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $scripts[] = implode("\n", $this->js[self::POS_LOAD]);
            }
            if (!empty($scripts)) {
                $lines[] = Html::script(implode("\n", $scripts));
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = Html::script(implode("\n", $this->js[self::POS_END]));
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(function ($) {\n" . implode("\n", $this->js[self::POS_READY]) . "\n});";
                $lines[] = Html::script($js);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).on('load', function () {\n" . implode("\n", $this->js[self::POS_LOAD]) . "\n});";
                $lines[] = Html::script($js);
            }
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }
    
    
    /**
     * izi function
     */
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
        $this->_item = \izi\db\ActiveRecord::populateData($value);
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
                    $this->getConfig()->{$keys[0]}{$keys[1]} = $value;
                }else{
                    $this->getConfig()->{$keys[0]}->{$keys[1]} = $value;
                }
                break;
                
        }
        
    }
    
    
    private $_viewPath;
    
    public function getViewPath()
    {
        if($this->_viewPath == null){
            $this->_viewPath = isset(Yii::$app->controller->module->viewPath) ? Yii::$app->controller->module->viewPath : Yii::$app->viewPath;
        }
        
        return $this->_viewPath;
    }
    
    /**
     *
     */
    public function renderPartial($_partial_, $_params_ = []){
// 		view($this->viewPath);
		/**
		Chưa thiết lập cho trang amp
		*/
		
		// Kiểm tra file partial của theme trước
        $path = dirname($this->theme->getBasePath()) . DIRECTORY_SEPARATOR . 'partials';				
		
		$partialFile = $path . DIRECTORY_SEPARATOR . trim($_partial_, DIRECTORY_SEPARATOR) . '.php';
		 
		
		if(file_exists($partialFile)){
			// Nếu file theo theme tồn tại -> render
			
		    if(isset($_params_['output']) && $_params_['output'] == 'text'){
		        return $this->renderPhpFile($partialFile, $_params_ );
		    }
		    
            echo $this->renderPhpFile($partialFile, $_params_ );
        }else{
			// Nếu file theo theme ko tồn tại -> Kiểm tra & render file partial của views (mặc định)
            $path = $this->getViewPath() . DIRECTORY_SEPARATOR . 'partials';
			$partialFile = $path . DIRECTORY_SEPARATOR . trim($_partial_, DIRECTORY_SEPARATOR) . '.php';
			 
			
			 
			if(file_exists($partialFile)){
			     
			    if(isset($_params_['output']) && $_params_['output'] == 'text'){
			        return $this->renderPhpFile($partialFile, $_params_ );
			    }
			    
				echo $this->renderPhpFile($partialFile, $_params_ );
			}
		}
        
    }
	
	/**
     * render partial block
     */
    public function renderPartialBlock($_partial_, $_params_ = [], $special_folder = 'block'){
				 		
		// Check file exist
		$path = \Yii::$app->viewPath . DIRECTORY_SEPARATOR . "partials/$special_folder";
		$partialFile = $path . DIRECTORY_SEPARATOR . trim($_partial_, DIRECTORY_SEPARATOR) . '/index.php';

	 
		
		if(file_exists($partialFile)){
			echo $this->renderPhpFile($partialFile, $_params_ );
		}
        
    }
	
	
    
    public function renderPartialTemplate($partial, $renderDefault = false){
		
		/**
		Lấy nội dung từ cache (lưu trong session)
		*/
		$params = [
			__METHOD__,
			__DOMAIN__,
			$partial,
			date('H'),
		];
		
		$v = Yii::$app->icache->getCache($params);
		
		$cacheKey = Yii::$app->icache->getKey($params);
		
		/**
		Lấy nội dung từ CSDL
		*/
		if(!Yii::$app->session->has($cacheKey)){
		
			$v = (new \yii\db\Query())->from(['a'=>'{{%ctemplate}}'])
			->innerJoin(['b'=>'{{%ctemplate_to_shop}}'],'a.id=b.item_id')
			->where(['b.shop_id'=>__SID__,'b.temp_id'=>__TID__,'a.type_code'=>$partial])->one();			
			Yii::$app->icache->store($v, $params);			 
		}
        $state = false;
        
		$code = strtolower($partial);
		 
        /**
		Kiểm tra file tồn tại & render
		*/
        if(!empty($v)){
			// get partial file path
            $partialFile = \Yii::$app->viewPath . "/partials/$code". ($this->isAmp ? '/amp/' : '/'). $v['name'] . '/index.php';
            
            if(file_exists($partialFile)){
                $state = true;
                switch ($v['status']){
                    case 1:
                        // Prod
                        echo $this->renderPhpFile($partialFile);
                        break;
                    case 2: // Dev
                        if(\Yii::$app->user->can(ROOT_USER)){
                            echo $this->renderPhpFile($partialFile);
                        }
                        break;
                    case 3: // Test
                        if(\Yii::$app->user->can([ROOT_USER,ADMIN_USER])){
                            echo $this->renderPhpFile($partialFile);
                        }
                        break;
                }
                
            }
        }
		
		/**
		Nếu không tìm thấy mẫu nào trong CSDL -> load trang partial mặc định (phụ thuộc vào theme)
		*/
        if(!$state && $renderDefault){
            echo $this->renderPartial(strtolower($partial));
        }         
    }
    
    
    /**
     * Overwrite render fundtion
     * {@inheritDoc}
     * @see \yii\base\View::renderFile()
     */
    public function renderFile($viewFile, $params = [], $context = null)
    {        
        return $this->compressionHtml(parent::renderFile($viewFile, $params, $context));                 
    }
    
    /**
	*	Nén nội dung html in ra output
    */
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
            //'#/([\'"])\+\'\'\+([\'"])\*#' => "/*", // restore comments in strings
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
    
	// Render các thẻ meta
    
    public function renderMetaTag(){
        $iparams = [
            __CLASS__,
            __FUNCTION__,
            	        FULL_URL,
        // 	        __SID__,
            date('d'),
        ];
        
        $options = YII_DEBUG ? [] : Yii::$app->icache->getCache($iparams);
        
        
        $seo = Yii::$app->cfg->seo;
        
        if(isset($seo['meta'])){
            echo uh($seo['meta'],2);
        }
        
        if(!!empty($options)){
            
            
            
            $options = [
                [
                    'name'=>"robots",
                    'content'=>'noodp'
                ],
                [
                    'name'=>"title",
                    'content'=>Html::encode(isset($seo['title']) ? $seo['title'] : '')
                ],
                [
                    'name'=>"keywords",
                    'content'=>Html::encode(isset($seo['keyword']) ? $seo['keyword'] : '')
                ],
                [
                    'name'=>"description",
                    'content'=> Html::encode(isset($seo['description']) ? $seo['description'] : '')
                ],
                
                [
                    'property'=>"og:type",
                    'content'=>'website'
                ],
                
                [
                    'property'=>"og:locale",
                    'content'=>str_replace('-', '_', Yii::$app->l->code)
                ],
                
                
                
                [
                    'property'=>"og:site_name",
                    'content'=>Html::encode(isset($seo['site_name']) ? $seo['site_name'] : '')
                ],
                
                
                
                [
                    'property'=>"og:title",
                    'content'=>Html::encode(isset($seo['title']) ? $seo['title'] : '')
                ],
                
                
                
                [
                    'property'=>"og:description",
                    'content'=>Html::encode(isset($seo['description']) ? $seo['description'] : '')
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
            
            
            
            $og_image = get_site_value('@seo/og_image',1,true);
            
             
            
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
                        'content'=>\yii\helpers\Html::encode(isset($seo['title']) ? $seo['title'] : '')
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
            // Facebook
            // fb:app_id
            
            if(isset(Yii::$app->cfg->social->facebook) && $facebook = Yii::$app->cfg->social->facebook){
                if(isset($facebook['app']['id'])){
                    $options[] = ['name'=>'fb:app_id', 'content'=>$facebook['app']['id']];
                }
            }
            
            // Twitter
            $options[] = ['name'=>'twitter:card', 'content'=>'summary'];
            $options[] = ['name'=>'twitter:description', 'content'=>Html::encode(get_site_value('@seo/description',1,true))];
            $options[] = ['name'=>'twitter:title', 'content'=>Html::encode(isset($seo['title']) ? $seo['title'] : '')];
            $options[] = ['name'=>'twitter:creator', 'content'=>(get_site_value('@seo/author',1,true) != "" ? Html::encode(get_site_value('@seo/author',1,true)) : 'iziweb.vn')];
            
            
            $options[] = ['name'=>'copyright','content'=>(get_site_value('@seo/copyright',1,true) != "" ? Html::encode(get_site_value('@seo/copyright',1,true)) : Html::encode(get_site_value('@seo/site_name',1,true)))];
            $options[] = ['name'=>'author','content'=>(get_site_value('@seo/author',1,true) != "" ? Html::encode(get_site_value('@seo/author',1,true)) : 'iziweb.vn')];
            $options[] = ['name'=>'designer','content'=>(get_site_value('@seo/author',1,true) != "" ? Html::encode(get_site_value('@seo/author',1,true)) : 'iziweb.vn')];
            $options[] = ['name'=>'revisit-after' ,'content' => '1 days'];
            
            
            // DC Metatags
            $options[] = ['name'=>'DC.title','lang'=>Yii::$app->l->locale,'content'=>Html::encode(isset($seo['title']) ? $seo['title'] : '')];
            $options[] = ['name'=>'DC:description','lang'=>Yii::$app->l->locale,'content'=>Html::encode(get_site_value('@seo/description',1,true))];
            $options[] = ['name'=>'DC.identifier','content'=>ABSOLUTE_DOMAIN];
            $options[] = ['name'=>'DC.subject','lang'=>Yii::$app->l->locale,'content'=>Html::encode(get_site_value('@seo/keyword',1,true))];
            $options[] = ['name'=>'DC.language','lang'=>Yii::$app->l->locale,'content'=>Yii::$app->language];
            $options[] = ['name'=>'DC.source','content'=>ABSOLUTE_DOMAIN];
            $options[] = ['name'=>'DC.publisher','content'=>(get_site_value('@seo/author',1,true) != "" ? Html::encode(get_site_value('@seo/author',1,true)) : 'iziweb.vn')];
            $options[] = ['name'=>'DC.contributor','content'=>(get_site_value('@seo/author',1,true) != "" ? Html::encode(get_site_value('@seo/author',1,true)) : 'iziweb.vn')];
            $options[] = ['name'=>'DC.coverage','content'=>'World'];
            
            // Other
            $options[] = ['name'=>'distribution','content'=>'global'];
            $options[] = ['name'=>'alexa','content'=>10];
            $options[] = ['name'=>'pagerank','content'=>10];
            $options[] = ['name'=>'serps','content'=>'1,2,3,10,11,12,13,ATF'];
            $options[] = ['name'=>'seoconsultantsdirectory','content'=>5];
            
            $options[] = ['name'=>'robots','content'=>"index,follow"];
            
            Yii::$app->icache->store($options ,$iparams);
            
        }
        
        foreach ($options as $option) {
            $this->registerMetaTag($option);
        }
        
        // Register hreflang
        $this->registerLinkTag([
            'rel'	=>	'alternate',
            'href'	=>	URL_WITH_PATH,
            'hreflang'	=>	"x-default",
        ]);
        
        $this->registerLinkTag([
            'rel'	=>	'alternate',
            'href'	=>	URL_WITH_PATH,
            'hreflang'	=>	Yii::$app->language,
        ]);
        
        
        // register favicon tag
        $favicon = isset(Yii::$app->cfg->app['favicon']['image']) ? Yii::$app->cfg->app['favicon']['image']  : '';
        
        if($favicon != ""){
            $src	=	getImage(['src'=>$favicon ,'h'=>30,'save'=>true,'output'=>'src','absolute'=>true],true);
            $this->registerLinkTag([
                'rel'	=>	"shortcut icon",
                'href'	=>	$src,
                'type'	=>	"image/x-icon",
                ]);
        }
        
		// register link tag
		$logo = isset(Yii::$app->cfg->app['logo']['image']) ? Yii::$app->cfg->app['logo']['image']  : '';
		if($logo !=""){
			$imgs = [
				[
					'rel'	=>	"apple-touch-icon-precomposed",
					'w'		=>	144,
					'h'		=>	144,
				],
				[
					'rel'	=>	"apple-touch-icon-precomposed",
					'w'		=>	114,
					'h'		=>	114,
				],
				[
					'rel'	=>	"apple-touch-icon-precomposed",
					'w'		=>	72,
					'h'		=>	72,
				],
				[
					'rel'	=>	"apple-touch-icon-precomposed",
					'w'		=>	57,
					'h'		=>	57,
				],
				[
					'rel'	=>	"nokia-touch-icon",
					'w'		=>	57,
					'h'		=>	57,
				],
				[
					'rel'	=>	"apple-touch-icon-precomposed",
					'w'		=>	1,
					'h'		=>	1,
				],
				
			];
			
			foreach($imgs as $img){
				$src 	= 	isset($img['src']) ? $img['src'] : $logo;
				$src	=	getImage(['src'=>$src ,'h'=>$img['h'],'save'=>true,'output'=>'src','absolute'=>true],true);
				$this->registerLinkTag([
					'rel'	=>	$img['rel'],
					'href'	=>	$src,
					'sizes'	=>	"${img['w']}x${img['h']}",
				]);
			}
		}
		
		
    }
    
	
	// Event trước khi gọi hàm endBody();
	
    protected function beforeEndBody(){
		
		$moduleName = Yii::$app->controller->module->id;
		
        $baseUrl = rtrim(SITE_ADDRESS,'/');
        
//         $baseUrl = rtrim(\yii\helpers\Url::home(true),'/');
        
        $identity_field = isset($this->params['identity_field']) ? $this->params['identity_field'] : 'id';
        $cBaseUrl = __IS_MODULE__ ? (__DOMAIN_MODULE__ ? $baseUrl : "$baseUrl/" . $moduleName)
        : $baseUrl;
        
        defined('__DOMAIN_ADMIN__') || define('__DOMAIN_ADMIN__', false);
        
        //$social = Yii::$app->cfg->social;
        
        //if(isset($social->google)){
        //    unset($social->google);
        //}
		
        $cfg = array(
            '_csrf-frontend' => Yii::$app->request->csrfToken,
            'is_module'=>defined('__IS_MODULE__') ? __IS_MODULE__ : false,
			'is_admin'=>defined('__IS_ADMIN__') ? __IS_ADMIN__ : false,
            'isLoged' => !(Yii::$app->user->isGuest),
            'domain_module'=>__DOMAIN_MODULE__,
			'domain_admin'=>__DOMAIN_ADMIN__,
            'baseUrl'=>$baseUrl,
            'absoluteUrl'=>rtrim(\yii\helpers\Url::home(true)),
            'adminUrl'=> __DOMAIN_ADMIN__ ? $baseUrl : "$baseUrl/admin",
            'moduleUrl'=> __DOMAIN_MODULE__ ? $baseUrl : "$baseUrl/$moduleName",
            'cBaseUrl'=> $cBaseUrl ,
            'controller_text'=>defined('__RCONTROLLER__') ? __RCONTROLLER__ : '',
            'module'=>$moduleName,
            'controller'=>Yii::$app->controller->id,
            'controllerUrl'=>URL_WITH_PATH,
            'action'=>Yii::$app->controller->action->id,
            //'controller_action'=>Yii::$app->controller->action->id,
            //'assets'=>Yii::getAlias('@admin'),
            'libsDir'=>'/libs',
            //'rsDir'	=> defined('__RSDIR__') ? __RSDIR__ : $this->theme->getBaseUrl(),
            //'wheight'=>'%f%screen.height%f%',
            //'wwidth'=>'%f%screen.width%f%',
            'get'=>(Yii::$app->request->get()),
            //'request'=>afGetUrl(),
            //'returnUrl'=>afGetUrl([],[$identity_field,'view','language','currency']),
            'sid'=>__SID__,
			'template'=>defined('__TEMP_NAME__') ? __TEMP_NAME__ : null,
            'time'=>date("d/m/Y H:i"),
            'lang'=>__IS_MODULE__ ? MODULE_LANG : __LANG__,
            'language'=>Yii::$app->l->getItem(__LANG__,true),
            //'hl'=>Yii::$app->language,
            'locale'=>Yii::$app->l->locale,
            'browser'=>Yii::$app->getBrowser(),
            //'text'=>$this->get_text_auto_load(),
            //'currency'=>Yii::$app->c->default,
            //'currencies'=>Yii::$app->c->getUserCurrency(),
            //'facebook_app'=>(isset($this->config->other_setting['facebook_app']) ? $this->config->other_setting['facebook_app'] : [
            //    'appId'=>1729388797358505,
            //    'version'=>'v3.1'
            //]),
			//'social'=>$social,
            'debug'=>YII_DEBUG,
            'editor'    =>  [
                'skin'  =>  'icy_orange',
                
            ]
            
        );
        
        echo '<script type="text/javascript">var $cfg=' .json_encode($cfg, YII_DEBUG ? JSON_PRETTY_PRINT : JSON_UNESCAPED_UNICODE).';</script>';
//         $this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js');
        
        
        
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
    
    
    public function renderFooterScript(){
        
        // Popup
        $params = [
            __SID__,
            'index',
            'popup',
            date('h'),
        ];
        
        $key = md5(json_encode($params));
        
        $cache = Yii::$app->icache->getCache($params);
        $s = true;
        
        if(isset($_COOKIE[$key]) && $_COOKIE[$key]>0 && $cache > 0 && time() - $cache < 600)
        {
            $s = false;
            
        }
        
        if($s){
            switch(Yii::$app->controller->id){
                case 'index': case 'site':
                    
                    $l = Yii::$app->frontend->getAdvert()->getItems([
                    'code'=>'ADV_POPUP',
                    'category_id'=>0,
//                     'default_category_id'=>$default_category_id,
                    ]);
                    
                    if(!empty($l)){
                        echo '<div id="'.($r = randString(12)).'" class="modal2 popup-modal"><div class="popup-container">';
                        foreach ($l as $v){
                            if(isset($v['adv_type']) && $v['adv_type'] == 2){
                                echo '<div class="item">'.uh($v['html'],2).'</div>';
                            }else{
                                echo '<div class="item"><a href="'.$v['link'].'" target="'.$v['target'].'"><img src="'.getAbsoluteUrl($v['image']).'" class="img-fluid mw100" alt="Responsive image"/></a></div>';
                            }
                        }
                        
                        echo '</div></div>';
                        
                        $this->registerJs('$("#'.$r.'")
.modal2({
  fadeDuration: 100
})
.on($.modal2.AFTER_CLOSE, function(event, modal) {
    var date = new Date();
     var minutes = 10;
     date.setTime(date.getTime() + (minutes * 60 * 1000));
     Cookies.set(\''.$key.'\', '.time().', { expires: date });
    
     log("Popup closed");
})
;');
                    }
                    
                    
                    Yii::$app->icache->store(time(), $params);
                    
                    break;
            }
        }else{
            switch(Yii::$app->controller->id){
                case 'index': case 'site':
                    $this->registerJs('
                    Cookies.set(\''.$key.'\', '.time().', { expires: 0 });
                    ;');
                    break;
            }
        }
        // Live chat
        
        if(!(isset(Yii::$app->cfg->param['livechat']['is_active']) && Yii::$app->cfg->param['livechat']['is_active'] == 0)){
        
        $livechat = isset(Yii::$app->cfg->app['livechat']['list_items']) ? Yii::$app->cfg->app['livechat']['list_items'] 
        : (isset(Yii::$app->cfg->app['livechat']['listItem']) ? Yii::$app->cfg->app['livechat']['listItem'] : []);
        
        echo '<div id="slivechat" class="slivechat">';
        foreach ($livechat as $lChat){
            if(isset($lChat['is_active']) && $lChat['is_active'] == 'on'){
                echo '<div class="ss_chat_'.$lChat['position'].'">' . uh($lChat['embed_code'],2) . '</div>';
            }
        }
        echo '</div>';
        }
        // Show google analystic
        $google_analystics = isset(Yii::$app->cfg->seo['google_analystics']) && Yii::$app->cfg->seo['google_analystics'] != "" ? Yii::$app->cfg->seo['google_analystics'] 
        : (isset(Yii::$app->cfg->seo['googleanalystics']) ? Yii::$app->cfg->seo['googleanalystics'] : '');
        echo uh($google_analystics,2);
        
        
//         view($google_analystics,1,1);
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
