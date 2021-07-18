<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class UploadJsAsset extends \yii\web\AssetBundle
{
    // public $sourcePath = '@vendor/bower-assets/uploadjs';
    
    
    public $css = [
       // 'cropper.min.css'
      
    ];
    public $js = [
        '/js/uploadjs.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
 
    ];
    
    public function init()
    {
 
    }
}