<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class FloatingLabelAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/floating-labels';
    
    
    public $css = [
        'floating-labels.css?v=' . __TIME__,
 
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset'
    ];
    
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
            $('.floating-labels .form-control').on('focus blur', function (e) {
        $(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
    }).trigger('blur');
JS
);
    }
 
}