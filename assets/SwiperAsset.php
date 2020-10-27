<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

/**
 * This asset bundle provides the [jQuery](http://jquery.com/) JavaScript library.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SwiperAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/swiper/dist';
    
    public $js = [
        //'http://idangero.us/swiper/js/vendor/jquery-1.11.0.min.js',
        'js/swiper.min.js',
        //'http://idangero.us/swiper/dist/js/swiper.min.js'
    ];
    
    public $jsOptions = [
//         'crossorigin'=>"anonymous"
    ];
    
    public $css = [
        'css/swiper.min.css',
    ];
    
    public $cssOptions = [
    //         'crossorigin'=>"anonymous"
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}