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
class FontAwesomeAsset extends \yii\web\AssetBundle
{
    
    
    public $js = [
        'https://use.fontawesome.com/releases/v5.6.1/js/all.js',
    ];
    
    public $jsOptions = [
        'crossorigin'=>"anonymous"
    ];
}