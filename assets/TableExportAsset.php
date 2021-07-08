<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class TableExportAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/table-export/dist';
    
    
    public $css = [
        'css/tableexport.min.css',
      
    ];
    public $js = [
        'js/FileSaver.min.js',
        'js/Blob.min.js',
        'js/xls.core.min.js',
        'js/tableexport.min.js',

    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}