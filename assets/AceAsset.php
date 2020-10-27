<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class AceAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/ace/src-min-noconflict';
    
    
    public $css = [
        'min/vs/editor/editor.main.css',
    ];
    
    
    public $js = [
        'ace.js', 
    ];
    
    public $depends = [        
        'izi\assets\BootstrapAsset',
    ];
}