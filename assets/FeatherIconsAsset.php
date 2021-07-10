<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class FeatherIconsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/feather-icons/dist';
    
    
    public $css = [
        
    ];
    public $js = [
        'feather.min.js', 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
 
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
feather.replace();
JS
);
    }
}