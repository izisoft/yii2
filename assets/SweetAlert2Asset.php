<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SweetAlert2Asset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/sweetalert2/dist';
    
    
    public $css = [
        'sweetalert2.min.css'
      
    ];
    public $js = [
        'sweetalert.min.js',
        
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}