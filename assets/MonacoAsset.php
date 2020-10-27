<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class MonacoAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/monaco-editor';
    
    
    public $css = [
        'min/vs/editor/editor.main.css',
    ];
    
    
    public $js = [
        'min/vs/loader.js',
        'min/vs/editor/editor.main.nls.js',
        'min/vs/editor/editor.main.js',
    ];
    
    public $depends = [        
        'izi\assets\BootstrapAsset',
    ];
}