<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;
 
class MdbJsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/mdb/4.6.1-s';
    
   
    public $js = [
        'js/mdb.bundle.min.js',
    ];
    
    public $depends = [        
        'izi\assets\Bootstrap4JsAsset',
//         'izi\assets\FontAwesomeAsset',
    ];
}