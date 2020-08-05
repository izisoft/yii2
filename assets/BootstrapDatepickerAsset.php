<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use Yii;
 
class BootstrapDatepickerAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-datepicker';
    
    
    public $css = [
        'css/bootstrap-datepicker.min.css',
      
    ];
    public $js = [
        'js/bootstrap-datepicker.min.js',
        'locales/bootstrap-datepicker.vi.min.js',
    ];
     
    
    public $depends = [        
        'yii\web\JqueryAsset',
        'izi\assets\MomentAsset',
        'izi\assets\BootstrapAsset',
    ];
    
    public function init()
    {
        Yii::$app->view->registerJs(<<<JS
jQuery('.bs-datepicker').each(function(i,elm){

		var e = jQuery(elm);

		if(e.attr('data-loaded') == undefined){
        
            var data = {};
            data.autoclose = true;
            data.format = e.attr('data-format') ? e.attr('data-format') : 'dd/mm/yyyy';
            data.month = e.attr('data-month') ? e.attr('data-month') : 2;
            data.calendarWeeks = true;
            var datesDisabled = e.attr('data-datesDisabled') ? e.attr('data-datesDisabled') : (e.attr('data-disabledDates') ? e.attr('data-disabledDates') : '');
            data.datesDisabled = [datesDisabled];
            
            if(e.attr('data-daysOfWeekDisabled')){
                data.daysOfWeekDisabled = [e.attr('data-daysOfWeekDisabled')];
            }

            if(e.attr('data-daysOfWeekHighlighted')){
                data.daysOfWeekHighlighted = [e.attr('data-daysOfWeekHighlighted')];
            }

            if(e.attr('data-endDate')){
                data.endDate = [e.attr('data-endDate')];
            }
            
            if(e.attr('data-language')){
                data.language = e.attr('data-language');
            }

            if(e.attr('data-multidate')){
                data.multidate = true;
                data.multidateSeparator = ',';
                 data.autoclose = false;
            }


            if(e.attr('data-startDate')){
                data.startDate = e.attr('data-startDate');
            }

            if(e.attr('data-startView')){
                data.startView = e.attr('data-startView');
            }
            
            if(e.attr('data-datepicker-title')){
                data.title = e.attr('data-datepicker-title');
            } 
            if(e.attr('data-todayBtn')){
                data.todayBtn = true;
            } 
            
            data.todayHighlight = true;
            
            
            
            
            
            
            
            


            e.datepicker(data).attr('data-loaded',true);
        }
});
            
JS
            );
    }
}