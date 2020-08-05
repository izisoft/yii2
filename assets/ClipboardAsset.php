<?php

namespace izi\assets;
use Yii;

class ClipboardAsset extends \yii\web\AssetBundle
{
         
    public $css = [
        
    ];
    
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js'
    ];     
    
    public $depends = [        
        'yii\web\JqueryAsset',        
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
            var clipboard = new ClipboardJS('.clipboard-copy');
 
JS
);
    }
 
}