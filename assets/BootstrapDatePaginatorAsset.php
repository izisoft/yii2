<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapDatePaginatorAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/bower-assets/bootstrap-datepaginator/dist';
    
    
    public $css = [
        'bootstrap-datepaginator.min.css',
      
    ];
    public $js = [
        'bootstrap-datepaginator.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\BootstrapDatepickerAsset',
    ];
    
    public function init()
    {
 
    }
}