<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapConfirmationAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-confirmation';
    
    
    public $css = [
        // 'css/bootstrap-datepicker.min.css',
      
    ];
    public $js = [
        'bootstrap-confirmation.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\BootstrapAsset',
    ];
     
}