<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class FullCalendarAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/fullcalendar';
    
    
    public $css = [
        'fullcalendar.min.css'
    ];
    public $js = [
        'fullcalendar.min.js', 
        'locale-all.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\JqueryUiAsset',
    ];
    
    public function init()
    {
         
    }
}