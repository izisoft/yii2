<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap javascript files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Bootstrap4Asset extends AssetBundle
{
// //     public $sourcePath = '@bower/bootstrap4/dist';
// //     public $sourcePath = '@bower/bootstrap4/dist';
// //     public $sourcePath = '@bower/bootstrap/dist';
//     public $css = [
// //         'css/bootstrap.min.css',
// //     	'css/floating-labels.css',    	
// //     	'css/bootstrap-dropdownhover.css',
//     ];
//     public $js = [
// //         'js/popper.min.js',
// //     	'js/holder.min.js',
// //     	'js/bootstrap-dropdownhover.js?v=' . __TIME__,
// //         'js/bootstrap.bundle.min.js',
         
//     ];
    
//     public $jsOptions = [
//         //'integrity'=>"sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T",
//         //'crossorigin'=>"anonymous"
//     ];
    
//     public $cssOptions = [
//         //'integrity'=>"sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB",
//         //'crossorigin'=>"anonymous"
//     ]; 
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',                
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
