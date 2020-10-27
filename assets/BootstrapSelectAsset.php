<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapSelectAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-select';
    
    
    public $css = [
        'css/bootstrap-select.min.css',
      
    ];
    public $js = [
        'js/bootstrap-select.min.js',
//         'https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.5/dist/js/bootstrap-select.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}