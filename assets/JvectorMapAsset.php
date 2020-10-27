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
class JvectorMapAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/jvectormap';
    
    public $css = [
        'jquery-jvectormap-2.0.3.css'
    ];
    
    public $js = [
        'jquery-jvectormap-2.0.3.min.js',
        'jquery-jvectormap-world-mill.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}