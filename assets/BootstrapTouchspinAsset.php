<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapTouchspinAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-touchspin';
    
    
    public $css = [
        'jquery.bootstrap-touchspin.min.css',
      
    ];
    public $js = [
        'jquery.bootstrap-touchspin.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}