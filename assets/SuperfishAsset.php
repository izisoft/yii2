<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;
use Yii;

 
class SuperfishAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/superfish';
    
    
    public $css = [
        'css/full.css',
    ];
    
    
    public $js = [
        'js/hoverIntent.js', 
        'js/superfish.js'
    ];
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
var sfOption = {};



jQuery('ul.superfish-menu,ul.superfish-megamenu').each(function(i,e){

var sf = $(e);
sfOption.autoArrows = sf.attr('data-autoArrows') && sf.attr('data-autoArrows') == '0' ? false : true;
sfOption.cssArrows = sf.attr('data-cssArrows') && sf.attr('data-cssArrows') == '0' ? false : true;
log(sfOption);
    $(e).superfish(sfOption);
});
JS
);
    }
    
}