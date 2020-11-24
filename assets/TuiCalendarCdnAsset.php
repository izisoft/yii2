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
class TuiCalendarCdnAsset extends \yii\web\AssetBundle
{
    // public $sourcePath = '@bower/dropdownhover';
    public $css = [
        'https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css',
        'https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.css',
        'https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.css'
    ];
    
    public $js = [
        
        'https://uicdn.toast.com/tui.time-picker/latest/tui-time-picker.min.js',
        'https://uicdn.toast.com/tui.date-picker/latest/tui-date-picker.min.js',

        // 'https://themesbrand.com/skote/layouts/vertical/assets/libs/chance/chance.min.js',

        'https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js'
    ];

    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\BootstrapAsset',
        'izi\assets\TuiCodeSnippetAsset',
        'izi\assets\MomentAsset',
        'izi\assets\TuiDomAsset'
    ];
}