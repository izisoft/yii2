<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;

class Bootstrap4JsAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap4/dist';
    
    public $js = [
//         'js/popper.min.js',
//     	'js/holder.min.js',
//     	'js/bootstrap-dropdownhover.js?v=' . __TIME__,
        'js/bootstrap.bundle.min.js',
         
    ];
 
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
