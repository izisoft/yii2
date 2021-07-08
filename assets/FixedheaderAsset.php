<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;
 
 
class FixedheaderAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/tablefixedheader';
    
    
    public $css = [
//         'chartist.min.css',
//         'chartist-init.css',
//         'chartist-plugin-tooltip/chartist-plugin-tooltip.css',
//         'c3-master/c3.min.css'
    ];
    public $js = [
//         'chartist.min.js',
//         'chartist-plugin-tooltip/chartist-plugin-tooltip.min.js',
        'tablefixedheader.js',
//         'c3-master/c3.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset'
    ];
    
 
}