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
class Mdb2Asset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/mdb/4.6.0';
    
    
    public $css = [
        'css/mdb.full.css?v='.__TIME__,
    ];
    
    
    public $js = [
        'js/mdb.full.js?v='.__TIME__,
    ];
    
    public $depends = [        
        'izi\assets\BootstrapAsset',
        'izi\assets\FontAwesome2Asset',
    ];
}