<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class ToastAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/jquery-toast-plugin/dist';
    
    
    public $css = [
        'jquery.toast.min.css'
      
    ];
    public $js = [
        'jquery.toast.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
    public function init()
    {
 
    }
}