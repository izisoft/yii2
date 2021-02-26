<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class WaypointAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/waypoint';
    
    
    public $css = [
        // 'waves.min.css'
    ];
    public $js = [
        'jquery.waypoints.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        
    }
}