<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapValidationAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/jqBootstrapValidation/dist';
    
    
    public $css = [
        
      
    ];
    public $js = [
        'jqBootstrapValidation-1.3.7.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',

        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}