<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\db;

use Yii;

class ActiveRecord extends \yii\db\ActiveRecord
{
    
    
    public static function populateData($row)
    { 
        $result = [];
        
        if(is_array($row) && !empty($row)){
            foreach ($row as $key=>$value) {
                if(is_array($value)){
                    if(!empty($value)){
                        foreach ($value as $key2=>$value2) {
                            
                            switch ($key2) {
                                case 'bizrule': 
                                case 'json_data': 
                                case 'content':
                                    
                                    
                                    
                                    if(is_string($value2) && is_array($content = json_decode($value2, true, JSON_UNESCAPED_UNICODE))){
//                                         $value = array_merge($value, $content);

                                        // Sử dụng cộng mảng thay cho merge mảng
                                        // Nếu sử dụng merge đặt mảng content lên đầu tiên
                                        $value += $content;
                                        unset($value[$key2]);
                                    }
                                    
                                    break;
                                    
                            }
                            
                            
                            $result[$key] = $value;
                        }
                    }
                    
                }else{
                    switch ($key) {
                        case 'bizrule': case 'json_data':case 'content':
                            
                            if(is_array($content = json_decode($value, true, JSON_UNESCAPED_UNICODE))){
                                
                                unset($row[$key]);
//                                 $row = array_merge($row, $content);
                                $row += $content;
                                
                            }
                            
                        break;
                        
                    }
                    
                    $result = $row;
                }
            }
        }else{
            return $row;
        }
        
        return $result;
    }
}
