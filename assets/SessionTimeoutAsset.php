<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class SessionTimeoutAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/session';
    
    
    public $css = [
         
    ];
    public $js = [
        'jquery.sessionTimeout.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
 
    }
}