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
class LessAsset extends \yii\web\AssetBundle
{
    
    public $sourcePath = '@bower/lessjs';
    
    
    public $js = [
//         'https://cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js',
        'less.min.js',
    ];
    
    public $jsOptions = [
//         'crossorigin'=>"anonymous"
    ];
}