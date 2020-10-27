<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;


class FontAwesomeProCssAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/fontawesome-pro-5.6.3-web';
    
    public $css = [
        'css/all.min.css'
        
//         'less/fontawesome.less',
//         'less/brands.less',
//         'less/light.less',
//         'less/regular.less',
//         'less/solid.less',
//         'less/v4-shims.less',
    ];
    
    public $js = [
//         'js/all.min.js',
    ];
    
    public $cssOptions = [
//         'rel'=>"stylesheet/less",
    ];
    
    public $jsOptions = [
        'crossorigin'=>"anonymous",
    ];
    
    public $depends = [
//         'izi\assets\BaseAsset'
    ];
}