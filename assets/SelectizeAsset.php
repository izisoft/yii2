<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SelectizeAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/selectize.js/dist';
    
    
    public $css = [
        'css/selectize.bootstrap3.css',
      
    ];
    public $js = [
        // 'js/selectize.min.js',
        'https://coderthemes.com/ubold/layouts/assets/libs/selectize/js/standalone/selectize.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
         
    ];
    
    public function init()
    {
        
    }
}