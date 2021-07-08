<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class LazyloadXtAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/lazyloadxt';
    
    
    
    public $js = [
        
        'error.js',
        'lazy.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
}