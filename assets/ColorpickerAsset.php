<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class ColorpickerAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-colorpicker';
    
    
    public $css = [         
        'dist/css/bootstrap-colorpicker.min.css',
        'jquery-asColorPicker/css/asColorPicker.min.css',              
    ];
    public $js = [ 
        'dist/js/bootstrap-colorpicker.min.js',
        
        'jquery-asColor/jquery-asColor.min.js',
        
        'jquery-asGradient/jquery-asGradient.min.js',
        
        'jquery-asColorPicker/jquery-asColorPicker.min.js',
        
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
//         'izi\assets\MomentAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}