<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class MultiSelectAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/multi-select';
    
    
    public $css = [
        'css/multi-select.css',
      
    ];
    public $js = [
        'js/jquery.multi-select.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
         
    ];
    
    public function init()
    {
 
    }
}