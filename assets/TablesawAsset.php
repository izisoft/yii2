<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class TablesawAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/tablesaw/dist';
    
    
    public $css = [
        'tablesaw.css',
      
    ];
    public $js = [
        'tablesaw.jquery.js',
        'tablesaw-init.js'
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