<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class DropzoneAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/dropzone';
    
    
    public $css = [
        
        'min/dropzone.min.css'
    ];
    public $js = [
        'min/dropzone.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
 
    ];
    
    public function init()
    {
 
    }
}