<?php 
namespace izi\assets;


use yii\web\AssetBundle;

class BaseLessAsset extends AssetBundle
{
    public $sourcePath = '@app/components/bower-assets/base';
    
    public $css = [
        'dist/less.less',
    ];
    
    public $cssOptions = [
        'rel'=>'stylesheet/less'
    ];
    
    public $depends = [
        'izi\assets\BaseAsset'
    ];
}
