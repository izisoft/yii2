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
class FontAwesome2Asset extends \yii\web\AssetBundle
{
    
    
    public $css = [
        'https://use.fontawesome.com/releases/v5.6.3/css/all.css',
    ];
    
    public $cssOptions = [
        'crossorigin'=>"anonymous",
        
        'integrity'=>"sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/"
    ];
}