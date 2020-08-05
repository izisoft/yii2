<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class JasnyBootstrapAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/jasny-bootstrap';
 
    public $js = [
        'jasny-bootstrap.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
 
    }
}