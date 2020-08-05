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
class ScrollMagicAsset extends \yii\web\AssetBundle
{
    
    public $sourcePath = '@bower/scroll-magic/scrollmagic';
    
    public $js = [
        'minified/ScrollMagic.min.js',
        'minified/plugins/debug.addIndicators.min.js',
    ];
    
    public $jsOptions = [
//         'crossorigin'=>"anonymous",
        
//         'integrity'=>"sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/"
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}