<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapTreeViewAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/bootstrap-treeview/dist';
    
    
    public $css = [
        'bootstrap-treeview.min.css',
      
    ];
    public $js = [
        'bootstrap-treeview.min.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}