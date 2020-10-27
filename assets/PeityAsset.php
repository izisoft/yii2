<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap javascript files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PeityAsset extends AssetBundle
{
    public $sourcePath = '@bower/peity';
 
    public $js = [
            'jquery.peity.min.js',
         
    ];
     
    
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
