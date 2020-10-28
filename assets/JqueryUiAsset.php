<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class JqueryUiAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/jquery-ui/jquery-ui-mini';
    
    
    public $css = [
        'jquery-ui.min.css',
        'jquery-ui.theme.min.css', 
    ];
    public $js = [
            'https://code.jquery.com/ui/1.12.1/jquery-ui.js'
    ];
     
    
    public $depends = [
        'yii\web\JqueryAsset', 
    ];
    
 
}