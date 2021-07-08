<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;
use Yii;

/**
 * Asset bundle for the Twitter bootstrap javascript files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UniformAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/uniform/3.0';
    public $css = [
        'css/default.css',
    ];
    public $js = [
        'js/jquery.uniform.standalone.js',
         
    ];
 
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(
            <<< JS
var uniformed = $("input.uniform, textarea.uniform, select.uniform, button.uniform, a.uniform").not(".skipThese");
uniformed.uniform();
JS
            );
    }
}
