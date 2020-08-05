<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

 
class HandsontableAsset extends \yii\web\AssetBundle
{
    
    public static $version = 'v7.1.0';
    
    public $sourcePath = '@bower/handsontable/v7.1.0';
    
    
    
    public $css = [
        'handsontable.full.min.css',
    ];
    
    
    public $js = [
        'handsontable.full.min.js', 
    ];
    
    public $depends = [        
        'izi\assets\BootstrapAsset',
        'yii\web\JqueryAsset',
    ];
    
    
    public static function setVersion($version)
    {
        HandsontableAsset::$version = $version;
    }
    
    public function init()
    {
        $this->sourcePath = "@bower/handsontable/" . HandsontableAsset::$version;
        
        if(HandsontableAsset::$version == 'cdn'){
            $this->css = [
                'https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css'
            ];
            
            $this->js = [
                'https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js'
            ];
        }
    }
}