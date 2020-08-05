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
class DropdownHoverAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/dropdownhover';
    public $css = [
        'bootstrap-dropdownhover.min.css',
    ];
    
    public $js = [
        'bootstrap-dropdownhover.js',
    ];
}