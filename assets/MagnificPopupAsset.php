<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class MagnificPopupAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/magnific-popup/dist';
    
    
    public $css = [
        'magnific-popup.css',
      
    ];
    public $js = [
        'jquery.magnific-popup.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',        
//         'izi\assets\Bootstrap4Asset',
    ];
    
    public function init()
    {
 
    }
}