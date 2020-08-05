<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class ColorboxAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/colorbox/dist';
    
    
    public $css = [
        'colorbox.css'
    ];
    public $js = [
        'jquery.colorbox-min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS

if($('.colorbox').length>0){
    $(".colorbox").colorbox({rel: $(this).attr('rel')});
}            
JS
);
    }
}