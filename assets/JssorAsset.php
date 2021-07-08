<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class JssorAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/jssor';
    
    
    public $css = [
        'css/style.css'
    ];
    public $js = [
        'js/jssor.slider.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
}