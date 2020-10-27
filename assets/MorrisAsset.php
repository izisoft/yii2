<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class MorrisAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/morrisjs';
    
    
    public $css = [
        'morris.css',
    ];
    
    
    public $js = [
        'morris.min.js', 
    ];
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
}