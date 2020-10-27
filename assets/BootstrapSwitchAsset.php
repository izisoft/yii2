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
class BootstrapSwitchAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-switch/dist';
    
    
    public $css = [
        'css/bootstrap-switch.min.css',
        
    ];
    public $js = [
        'js/bootstrap-switch.min.js'
    ];
    
    
    public $depends = [
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
        
    }
}
