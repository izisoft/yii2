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
class JqueryCookieAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/jquerycookie';
    public $js = [
        'jquery.cookie.js',
    ];
}