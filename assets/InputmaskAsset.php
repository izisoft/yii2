<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class InputmaskAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/inputmask/dist';
    
    
    public $css = [
        
    ];
    public $js = [
        'min/jquery.inputmask.bundle.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
            
$('.inputmask').each(function(i,e){
    var elm = $(e);
    var format = elm.attr('data-format') ? elm.attr('data-format') : undefined;
    
    var option = {};
    
    if(elm.attr('data-format')){
        option.placeholder = elm.attr('data-format');
    }
    
    elm.inputmask(format, option)
    .attr('data-loaded',1);
});


JS
);
    }
}