<?php 
namespace izi\assets;


use yii\web\AssetBundle;

class BaseAsset extends AssetBundle
{
    public $sourcePath = '@app/components/bower-assets/base';
    
    public $css = [
//         'base.min.css'
    ];
    
    public $js = [
//         'base.min.js?v=' . __TIME__
    ];
    
    
    public function init()
    { 
        $debug = true;
        
        $ver = date("Hi");
        
        $this->css[] = 'dist/base.min.css' . ($debug ? '?v=' . $ver : '');
        $this->js[] = 'dist/base.min.js' . ($debug ? '?v=' . $ver : '');
    }
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset'
    ];
}
