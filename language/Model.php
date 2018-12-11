<?php 
namespace izi\language;

class Model extends \yii\db\ActiveRecord

{
    public static function tableName()
    {
        return '{{%languages}}';
    }
    
    public function getDefault()
    {
        return static::find()->where(["code"=> ['vi','en','vi-VN','en-US']])->asArray()->all();
        
    }
 
}
