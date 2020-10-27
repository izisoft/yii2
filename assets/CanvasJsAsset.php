<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class CanvasJsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/canvasjs-2.3.2';
    
    
    public $css = [

    ];
    public $js = [
        'canvasjs.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
 
}