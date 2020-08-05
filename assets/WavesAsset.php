<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class WavesAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/waves/dist';
    
    
    public $css = [
        'waves.min.css'
    ];
    public $js = [
        'waves.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        
    }
}