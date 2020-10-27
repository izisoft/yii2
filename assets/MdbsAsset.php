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
class MdbsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/mdb/4.6.1-s';
    
    
    public $css = [
        'css/mdb.min.css',
    ];
    
    
    public $js = [
        'js/mdb.min.js',
    ];
    
    public $depends = [        
        'izi\assets\Bootstrap4Asset',
        'izi\assets\FontAwesomeAsset',
    ];
}