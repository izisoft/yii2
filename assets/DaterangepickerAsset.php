<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class DaterangepickerAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/daterangepicker';
    
    
    public $css = [
        'daterangepicker.css',
      
    ];
    public $js = [
        'daterangepicker.js'
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
        $('.date-range-picker').daterangepicker();
        $('.date-range-picker-time').daterangepicker({ 
        timePicker: true, 
        timePickerIncrement: 1,
        timePicker24Hour: false,
        format: 'DD/MM/YYYY hh:mm A',
        locale: {
              format: 'DD/MM/YYYY hh:mm A'
            }
        });

        //Date range as a button
        $('.daterange-btn').daterangepicker(
          {
            ranges   : {
              'Hôm nay'       : [moment(), moment()],
              'Hôm qua'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'Tuần trước' : [moment().subtract(6, 'days'), moment()],
              'Tháng trước': [moment().subtract(29, 'days'), moment()],
              'Tháng này'  : [moment().startOf('month'), moment().endOf('month')],
              'Tháng sau'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment(),
buttonClasses: ['btn btn-default'],
		        applyClass: 'btn-small btn-primary',
		        cancelClass: 'btn-small',
		        format: 'MM/DD/YYYY',
		        separator: ' tới ',
		        locale: {
		          applyLabel: 'Xác nhận',
		          cancelLabel: 'Xóa',
		          fromLabel: 'Từ',
		          toLabel: 'Đến',
		          customRangeLabel: 'Tùy chỉnh',
		          daysOfWeek: ['Cn', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
		          monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
		          firstDay: 1
		        }
          },
          function (start, end) {
            $('.daterange-btn span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'))
          }
        );

JS
            );
    }
}