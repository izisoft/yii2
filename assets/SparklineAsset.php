<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SparklineAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/sparkline';
    
    
    public $css = [
     
    ];
    public $js = [
        'jquery.sparkline.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
 
    }
}