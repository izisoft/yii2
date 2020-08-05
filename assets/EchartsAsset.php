<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class EchartsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/echarts';
    
    
    public $css = [
    
    ];
    public $js = [
        'echarts-all.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
         
    }
}