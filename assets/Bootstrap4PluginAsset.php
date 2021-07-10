<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;

class Bootstrap4PluginAsset extends AssetBundle
{
    //public $sourcePath = '@bower/bootstrap5/dist';
    
    public $js = [
        'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js',
         
    ];

    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css'
    ];

    public $jsOptions = [
        'integrity' => 'sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns',
        'crossorigin'=>"anonymous"
    ];
 
    public $cssOptions = [
        'integrity'=>"sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" ,
        'crossorigin'=>"anonymous"
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
