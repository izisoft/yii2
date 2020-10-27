<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class Owl2Asset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/owl2';
    
    
    public $css = [
        'assets/owl.carousel.min.css',
        'assets/owl.theme.default.min.css',
    ];

    
    public $js = [
         
        'owl.carousel.min.js',       
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
    ];
    
 
}