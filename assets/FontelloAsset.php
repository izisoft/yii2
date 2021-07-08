<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class FontelloAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/fontello';
    
    
    public $css = [
        'css/fontello.css',
        'css/animation.css',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
}