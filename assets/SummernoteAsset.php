<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SummernoteAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/summernote';
    
    
    public $css = [
        'summernote-bs4.css',
      
    ];
    public $js = [
        'summernote-bs4.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}