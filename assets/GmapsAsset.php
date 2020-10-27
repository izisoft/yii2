<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class GmapsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/gmaps';
    
    
    public $css = [
//         'chartist.min.css',
//         'chartist-init.css',
//         'chartist-plugin-tooltip/chartist-plugin-tooltip.css',
//         'c3-master/c3.min.css'
    ];
    public $js = [
//         'chartist.min.js',
            'https://maps.googleapis.com/maps/api/js?key=AIzaSyDoliAneRffQDyA7Ul9cDk3tLe7vaU4yP8',
        'gmaps.min.js',
//         'c3-master/c3.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
 
}