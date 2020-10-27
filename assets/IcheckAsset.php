<?php
/**
 * @link https://www.iziweb.net/
 * @copyright Copyright (c) 2018 Izi Software LLC
 * @license http://www.iziweb.net/license/
 */

namespace izi\assets;

use Yii;
 
class IcheckAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/icheck';
    
    
    public $css = [
        'skins/all.css',
      
    ];
    public $js = [
        'icheck.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
 
        Yii::$app->view->registerJs(<<<JS
            //iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
      checkboxClass: 'icheckbox_minimal-blue',
      radioClass   : 'iradio_minimal-blue'
    });
    //Red color scheme for iCheck
    $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
      checkboxClass: 'icheckbox_minimal-red',
      radioClass   : 'iradio_minimal-red'
    });
    //Flat red color scheme for iCheck
    $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
      checkboxClass: 'icheckbox_flat-green',
      radioClass   : 'iradio_flat-green'
    });


JS
);
        
    }
}