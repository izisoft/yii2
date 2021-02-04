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
        'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js',
         
    ];

    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css'
    ];

    public $jsOptions = [
        'integrity' => 'sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW',
        'crossorigin'=>"anonymous"
    ];
 
    public $cssOptions = [
        'integrity'=>"sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" ,
        'crossorigin'=>"anonymous"
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
