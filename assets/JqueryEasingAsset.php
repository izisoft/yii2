<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class JqueryEasingAsset extends \yii\web\AssetBundle
{
    // public $sourcePath = '@bower/jquery-ui/jquery-ui-1.12.1.custom';
    
    
    public $css = [
        // 'jquery-ui.min.css',
        // 'jquery-ui.theme.min.css', 
    ];
    public $js = [
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js', 
            // 'http://demo.hinode.nozomijapan.vn/libs/jquery-ui-1.12.1/jquery-ui.min.js'
    ];
     
    
    public $depends = [
        'yii\web\JqueryAsset', 
    ];
    
 
}