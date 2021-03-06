<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;

class Bootstrap5PluginAsset extends AssetBundle
{
    //public $sourcePath = '@bower/bootstrap5/dist';
    
    public $js = [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js',
         
    ];

    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css'
    ];

    public $jsOptions = [
        'integrity' => 'sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM',
        'crossorigin'=>"anonymous"
    ];
 
    public $cssOptions = [
        'integrity'=>"sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" ,
        'crossorigin'=>"anonymous"
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
