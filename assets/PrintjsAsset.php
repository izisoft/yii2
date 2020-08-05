<?php
/**
 * @link https://www.iziweb.net/
 * @copyright Copyright (c) 2018 by A Tỉn
 * @license https://www.iziweb.net/license/
 */

namespace izi\assets;
 
 
class PrintjsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/print-js/dist';
    
    
    public $css = [
        'print.min.css'
    ];
    public $js = [
            'print.min.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset'
    ];
    
 
}