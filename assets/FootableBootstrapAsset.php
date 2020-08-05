<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class FootableBootstrapAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/footable-bootstrap';
    
    
    public $css = [
        'css/footable.bootstrap.min.css',
      
    ];
    public $js = [
        'js/footable.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
        'izi\assets\MomentAsset',
    ];
    
    public function init()
    {
 
    }
}