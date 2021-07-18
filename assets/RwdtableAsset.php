<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class RwdtableAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/rwd-table/dist';
    
 
    public $css = [
        'css/rwd-table.min.css'
    ];
     
    public $js = [
        'js/rwd-table.min.js'
    ];
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
 
}