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
class TagsInputAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/tagsinput/dist';
    
    public $js = [
        'bootstrap-tagsinput.min.js',
    ];
    
    
    public $css = [
        'bootstrap-tagsinput.css',
    ];
    
    public $depends = [
    	'yii\web\YiiAsset',
    	'izi\assets\BootstrapAsset',
    ];
    
}