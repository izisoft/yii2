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
class PhotoSwipeAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/photoswipe';
    public $css = [
        'photoswipe.css',
        'default-skin/default-skin.css',
        
    ];
    
    public $js = [
        'photoswipe.min.js',
        'photoswipe-ui-default.min.js',
        
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}