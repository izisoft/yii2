<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class SlickAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/slick';
    
    
    public $css = [
        'slick.css',
        'slick-theme.css',
    ];
    
    
    public $js = [
        'slick.min.js'
    ];
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
}