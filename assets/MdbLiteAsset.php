<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;


class MdbLiteAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/mdb/4.6.1-s';
    
    
    public $css = [
        'css/mdb.lite.min.css',
    ];
    
    
    public $js = [
        'js/mdb.min.js',
    ];
    
    public $depends = [        
        'izi\assets\BootstrapAsset',
        'izi\assets\FontAwesomeAsset',
    ];
}