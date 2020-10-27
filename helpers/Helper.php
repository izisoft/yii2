<?php
namespace izi\helpers;

class Helper extends \yii\helpers\Html
{
    
    public static function isEmail($email)
    {         
        if(filter_var($email, FILTER_VALIDATE_EMAIL) !==false){
            return true;
        }
        return false;        
    }
    
    public static function renderTimeLimit($from, $to, $language = __LANG__){
        
        $time1 = !is_numeric($from) ? strtotime($from) : $from;
        
        $time2 = !is_numeric($to) ? strtotime($to) : $to;
        
        $d1 = date('d', $time1);
        $d2 = date('d', $time2);
        
        $m1 = date('m', $time1);
        $m2 = date('m', $time2);
        
        $y1 = date('y', $time1);
        $y2 = date('y', $time2);
        $year1 = '';
        if($language == 'vi-VN'){
            if($m2>$m1){
                
                $days = "$d1/$m1-$d2/$m2/";
            }else{
                $days = "$d1-$d2/$m2/";
            }
            
            if($y2>$y1){
                $year1 = $y1;
            } 
            $period = $d1 . ($m2!=$m1 ? "/$m1" : '') . ($y2>$y1 ? "/$y1" : "") . "-$d2/$m2/$y2";
            
        }else{

            if($m2>$m1){
                $m1 = date('M', $time1);
                $m2 = date('M', $time2);
                $days = "$d1 $m1-$d2 $m2 ";
            }else{
                $m2 = date('M', $time2);
                $days = "$d1-$d2 $m2 ";
            }
            
            if($y2>$y1){
                $year1 = $y1;
            } 
            $period = $d1 . ($m2!=$m1 ? "/$m1" : '') . ($y2>$y1 ? "/$y1" : "") . "-$d2/$m2/$y2";
        }
        
        return $period;
        
    }
}