<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class ChartistAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/chartist/dist';
    
    
    public $css = [
        'chartist.min.css',
        'chartist-init.css',
        'chartist-plugin-tooltip/chartist-plugin-tooltip.css',
//         'c3-master/c3.min.css'
    ];
    public $js = [
        'chartist.min.js',
        'chartist-plugin-tooltip/chartist-plugin-tooltip.min.js',
//         'd3/d3.min.js',
//         'c3-master/c3.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
 
    }
}