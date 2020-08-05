<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

/**
 * This asset bundle provides the [jQuery](http://jquery.com/) JavaScript library.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ReactAsset extends \yii\web\AssetBundle
{
    
//     public $sourcePath = '@bower/lessjs';
    
    
    public $js = [
//         YII_DEBUG ? 'https://unpkg.com/react@16/umd/react.development.js' : 'https://unpkg.com/react@16/umd/react.production.min.js',
//         'https://unpkg.com/react-dom@16/umd/react-dom.development.js',
    ];
    
    public $jsOptions = [
        'crossorigin'=>"anonymous"
    ];
    
    public function init()
    {
        if(YII_DEBUG){
            $this->js = [
                'https://unpkg.com/react@16/umd/react.development.js',
                'https://unpkg.com/react-dom@16/umd/react-dom.development.js',
                
                'https://unpkg.com/babel-standalone@6.15.0/babel.min.js'
            ];
        }else{
            $this->js = [
                'https://unpkg.com/react@16/umd/react.production.min.js',
                'https://unpkg.com/react-dom@16/umd/react-dom.production.min.js',
                'https://unpkg.com/babel-standalone@6.15.0/babel.min.js'
            ];
        }
    }
}