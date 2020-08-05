<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\local\models;
use Yii;
class Code extends \yii\db\ActiveRecord
{
   
    public static function tableName(){
        return '{{%countries_to_code}}';
    }
     
      
    
    
    public function findCountriesCode($country_id){
        
        $model = static::find()->where(['country_id'=>$country_id])->asArray()->all();
        $result = [];
        if(!empty($model)){
            foreach ($model as $m){
                $result[$m['code']] = $m['value'];
            }
        }
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
}