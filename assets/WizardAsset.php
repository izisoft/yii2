<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class WizardAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/wizard';
    
    
    public $css = [
        'steps.css',
      
    ];
    public $js = [
        'jquery.steps.min.js',
        'jquery.validate.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
    ];
    
    public function init()
    {
 
    }
}