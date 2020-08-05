<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class RaphaelAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/raphael';
    
 
    public $js = [
        'raphael.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
 
}