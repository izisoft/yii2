<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SemanticUiAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/semantic-ui';
    
    
    public $css = [
        'dist/components/dropdown.min.css',
        'dist/components/transition.min.css',
        'dist/components/search.min.css',
        'dist/components/flag.min.css',
        'dist/components/label.min.css',
        'dist/components/icon.min.css',
    ];
    public $js = [
        'dist/components/transition.min.js',
        'dist/components/dropdown.js',
        'dist/components/search.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
         
    }
}