<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class StickyKitAsset extends \yii\web\AssetBundle
{
    //public $sourcePath = '@bower/sticky-kit';
    
    
    public $css = [
     
    ];
    public $js = [
        '//cdn.iziweb.net/sticky-kit/sticky-kit.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
         
    }
}