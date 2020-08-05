<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class Html5EditorAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/html5-editor';
    
    
    public $css = [
        'bootstrap-wysihtml5.css',
      
    ];
    public $js = [
        'wysihtml5/dist/wysihtml5-0.3.0.min.js',
        'bootstrap-wysihtml5.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}