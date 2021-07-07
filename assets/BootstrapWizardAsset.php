<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapWizardAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/twitter-bootstrap-wizard';
    
    
    public $css = [
        'prettify.css',      
    ];
    public $js = [
        'jquery.bootstrap.wizard.min.js',
        'prettify.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}