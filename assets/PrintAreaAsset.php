<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class PrintAreaAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/print';
    
    
    public $css = [
//         'chartist.min.css',
//         'chartist-init.css',
//         'chartist-plugin-tooltip/chartist-plugin-tooltip.css',
//         'c3-master/c3.min.css'
    ];
    public $js = [
            'jquery.PrintArea.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\JqueryUiAsset'
    ];
    
 
}