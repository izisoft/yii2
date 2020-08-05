<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class ClockpickerAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/clockpicker';
    
    
    public $css = [
        'bootstrap-clockpicker.min.css',
         
        
      
    ];
    public $js = [
        'bootstrap-clockpicker.min.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}