<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class NestableAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/nestable';
    
    
    public $css = [
        'nestable.css',
    ];
    
    
    public $js = [
        'jquery.nestable.js', 
    ];
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
}