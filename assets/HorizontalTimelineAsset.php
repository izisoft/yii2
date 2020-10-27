<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class HorizontalTimelineAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/timeline';
    
    
    public $css = [
        'horizontal-timeline.css'
      
    ];
    public $js = [
        'horizontal-timeline.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
    public function init()
    {
 
    }
}