<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapTableAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-table/dist';
    
    
    public $css = [
        'bootstrap-table.min.css',
      
    ];
    public $js = [
        'bootstrap-table.min.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}