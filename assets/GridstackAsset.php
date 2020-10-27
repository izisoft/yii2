<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class GridstackAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/gridstack/dist';
    
    
    public $css = [
        'gridstack.min.css',
      
    ];
    public $js = [
        'lodash.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js',        
        'gridstack.min.js',
        'gridstack.jQueryUI.min.js',        
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
        'izi\assets\JqueryUiAsset',
    ];
    
    public function init()
    {
 
    }
}