<?php

namespace izi\assets;
use Yii;

class ChosenSelectAsset extends \yii\web\AssetBundle
{
         
    public $sourcePath = '@vendor/bower-assets/chosen';
    public $css = [
        'chosen.css',
        'chosen-icon-gh-pages/chosenIcon/chosenIcon.css'
    ];
    
    public $js = [
        'chosen.jquery.js',
        'chosen.ajaxaddition.jquery.js',
        'chosen-icon-gh-pages/chosenIcon/chosenIcon.jquery.js'
    ];     
    
    public $depends = [        
        'yii\web\JqueryAsset',        
    ];
    
 
}