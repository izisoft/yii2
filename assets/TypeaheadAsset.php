<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class TypeaheadAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/typeahead/dist';
    
    
    public $css = [
       
    ];
    public $js = [
        'typeahead.bundle.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset', 
    ];
    
    public function init()
    {
 
    }
}