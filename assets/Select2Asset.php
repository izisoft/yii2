<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class Select2Asset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/select2';
    
    
    public $css = [
        'css/select2.min.css',
      
    ];
    public $js = [
        'js/select2.full.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
         
    ];
    
    public function init()
    {
        
    }
}