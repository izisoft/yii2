<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class DropifyAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/dropify/dist';
    
    
    public $css = [
        'css/dropify.min.css',
      
    ];
    public $js = [
        'js/dropify.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
    public function init()
    {
        Yii::$app->view->registerCss(<<<CSS
.dropify-wrapper .dropify-message span.file-icon p{font-size: 1rem}
CSS
    );
    }
}