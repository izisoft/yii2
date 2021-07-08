<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class IonRangesliderAsset extends \yii\web\AssetBundle
{
    
    public $sourcePath = '@vendor/bower-assets/ion-rangeslider';
    
    
    public $css = [
            'css/ion.rangeSlider.min.css'
    ];
    public $js = [
        'js/ion.rangeSlider.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
 
}