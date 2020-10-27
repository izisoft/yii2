<?php

namespace izi\assets;
 
 
class JqueryMobileAsset extends \yii\web\AssetBundle
{
    
    public $css = [
        '//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.5/jquery.mobile.min.css',
      
    ];
    public $js = [
//         '//code.jquery.com/jquery-1.7.2.min.js',
        //'http://code.jquery.com/mobile/1.5.0-alpha.1/jquery.mobile-1.5.0-alpha.1.min.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',       
    ];
    
    public function init()
    {
 
    }
}