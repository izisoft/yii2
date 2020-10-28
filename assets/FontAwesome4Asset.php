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
class FontAwesome4Asset extends \yii\web\AssetBundle
{
    
    public $sourcePath = '@bower/font-awesome-v4';
    
    public $css = [
        'css/font-awesome.min.css',
    ];
    
    public $jsOptions = [
        //'crossorigin'=>"anonymous"
    ];
}