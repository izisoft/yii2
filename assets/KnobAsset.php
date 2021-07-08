<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class KnobAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/knob';
    
    
 
    public $js = [
        'js/jquery.knob.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
         
    }
}