<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class JstreeAsset extends \yii\web\AssetBundle
{
    // public $sourcePath = '@bower/jssor';
    
    public $version = '3.3.10';
    
    public $css = [];
    public $js = [];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];

    public function init()
    {
        $this->css = ['//cdn.iziweb.net/jstree/'.$this->version.'/themes/default/style.min.css'];
        $this->js = ['//cdn.iziweb.net/jstree/'.$this->version.'/jstree.min.js'];
    }
}