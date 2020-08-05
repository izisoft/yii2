<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SwitcheryAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/switchery';
    
    
    public $css = [
        'switchery.min.css',
      
    ];
    public $js = [
        'switchery.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
    public function init()
    {
 
    }
}