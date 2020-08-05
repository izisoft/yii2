<?php 
namespace izi\assets;


use yii\web\AssetBundle;

class FastclickAsset extends AssetBundle
{
    public $sourcePath = '@bower/fastclick';
    
    public $js = [
        'lib/fastclick.js',
    ];
    
     
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
