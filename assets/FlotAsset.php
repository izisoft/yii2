<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class FlotAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/flot/flot-0.8.3';
    
    
    public $css = [
        
    ];
    public $js = [
        'excanvas.js',
        'jquery.flot.js',
//         'https://cdn.jsdelivr.net/jquery.flot/0.8.3/jquery.flot.min.js',
        'jquery.flot.time.js',
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/flot/jquery.flot.time.js',
        'jquery.flot.tooltip.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
//         Yii::$app->view->registerJs(<<<JS

// if($('.colorbox').length>0){
//     $(".colorbox").colorbox({rel: $(this).attr('rel')});
// }            
// JS
// );
    }
}