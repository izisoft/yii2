<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;


class FontAwesomeProAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/fontawesome-pro-5.6.3-web';
    
    public $css = [
        'css/all.min.css'
// 'https://use.fontawesome.com/releases/v5.6.3/css/all.css'
        
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
//         'integrity'=>"sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" ,
//         'crossorigin'=>"anonymous"
    ];
    
    public $jsOptions = [
        'crossorigin'=>"anonymous",
    ];
}