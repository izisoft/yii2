<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class CropperAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/cropper/dist';
    
    
    public $css = [
        'cropper.min.css'
      
    ];
    public $js = [
        'cropper.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
 
    ];
    
    public function init()
    {
 
    }
}