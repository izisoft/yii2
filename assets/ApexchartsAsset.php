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
class ApexchartsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/apexcharts.js-3.22.1/dist';
    public $js = [
        'apexcharts.min.js',
    ];
}