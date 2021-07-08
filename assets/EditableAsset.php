<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class EditableAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/datatables';
    
    
    public $css = [
//         'bootstrap3-editable/css/bootstrap-editable.css',
      
    ];
    public $js = [
        'datatables/datatables.min.js',
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/jquery-datatables-editable/jquery.dataTables.js',
        
        'datatables/DataTables-1.10.18/js/dataTables.bootstrap.min.js',
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/datatables/media/js/dataTables.bootstrap.js',
        
        'tiny-editable/mindmup-editabletable.js',
        
        'tiny-editable/numeric-input-example.js',
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/tiny-editable/numeric-input-example.js'
        
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/jquery-datatables-editable/jquery.dataTables.js',
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/datatables/media/js/dataTables.bootstrap.js',
        
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/tiny-editable/mindmup-editabletable.js',
//         'https://wrappixel.com/demos/admin-templates/material-pro/assets/plugins/tiny-editable/numeric-input-example.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
 
    }
}