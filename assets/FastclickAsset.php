<?php 
namespace izi\assets;


use yii\web\AssetBundle;

class FastclickAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/fastclick';
    
    public $js = [
        'lib/fastclick.js',
    ];
    
     
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
