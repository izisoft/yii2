<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class JsGridAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/jsgrid';
    
    
    public $css = [
        'jsgrid.min.css',
        'jsgrid-theme.min.css'
    ];
    public $js = [
        'jsgrid.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
        
    ];
    
    public function init()
    {
        
    }
}