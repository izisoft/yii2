<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class GaugeAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/gauge/dist';
    
     
    public $js = [
        'gauge.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
 
    }
}