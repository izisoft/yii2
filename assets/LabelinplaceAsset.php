<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;


class LabelinplaceAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/labelinplace';
    
    
    public $css = [
        'css/jquery.labelinplace.min.css',
    ];
    
    
    public $js = [
        'js/jquery.labelinplace.js', 'js/jquery.label2inplace.js'
    ];
    
    public $depends = [        
        'izi\assets\BootstrapAsset',
        // 'izi\assets\FontAwesomeAsset',
    ];
}