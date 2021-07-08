<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class JqueryCountDownAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/jquery.countdown-2.2.0';
    
    
    public $css = [
        // 'jquery.toast.min.css'
      
    ];
    public $js = [
        'jquery.countdown.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
    public function init()
    {
 
    }
}