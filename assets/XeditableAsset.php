<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class XeditableAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/x-editable';
    
    
    public $css = [
        'bootstrap3-editable/css/bootstrap-editable.css',
      
    ];
    public $js = [
        'bootstrap3-editable/js/bootstrap-editable.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
        'izi\assets\MomentAsset',
    ];
    
    public function init()
    {
 
    }
}