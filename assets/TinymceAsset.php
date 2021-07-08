<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class TinymceAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/tinymce';
    
    
    public $css = [
        'bootstrap-wysihtml5.css',
      
    ];
    public $js = [
        'tinymce.min.js' 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}