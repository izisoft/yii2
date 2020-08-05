<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapTimepickerAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-timepicker';
    
    
    public $css = [
        'css/bootstrap-timepicker.min.css',
      
    ];
    public $js = [
        'js/bootstrap-timepicker.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
        $('.timepicker').timepicker({
            showInputs: false,
            showMeridian:false,
        });
JS
            );
    }
}