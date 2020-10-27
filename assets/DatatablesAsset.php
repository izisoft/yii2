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
class DatatablesAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/datatables';
    public $css = [
        'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css',
//         'datatables-bs4/css/dataTables.bootstrap4.min.css',
//         'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
        'https://cdn.datatables.net/responsive/2.2.1/css/responsive.dataTables.min.css'
    ];
    
    public $js = [
        '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
        '//cdn.datatables.net/responsive/2.2.1/js/dataTables.responsive.min.js',
//         'datatables/datatables.min.js',
//         "https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js",
//         "https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js",
//         "https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js",
//         "https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js",
//         "https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js",
//         "https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js",
//         "https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js",
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'izi\assets\BootstrapAsset',
    ];
}