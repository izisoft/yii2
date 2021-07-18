<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class Bootstrap5TableAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/bootstrap-table/1.18.3/dist';
    
    
    public $css = [
        'bootstrap-table.min.css',
      
    ];
    public $js = [
        'bootstrap-table.min.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\Bootstrap5PluginAsset'
    ];
        
    // public function init()
    // {
    //     $this->bsVersion = isset(Yii::$app->params['bsVersion']) ? Yii::$app->params['bsVersion'] : '4.x';
    //     if($this->isBs4()){
    //         $this->depends[] = 'yii\bootstrap4\BootstrapPluginAsset';
    //     }else{
    //         $this->depends[] = 'yii\bootstrap\BootstrapPluginAsset';
    //     }
    // }
}