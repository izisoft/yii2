<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapMaterialDatetimepickerAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-material-datetimepicker';
    
    
    public $css = [
        'css/bootstrap-material-datetimepicker.css'
    ];
    public $js = [
        'js/bootstrap-material-datetimepicker.js'
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