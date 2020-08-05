<?php
namespace izi\models;
use Yii;

class Template extends \yii\db\ActiveRecord

{
    public static function tableName(){
        return '{{%templates}}';
    }
    
    public static function tableBlockTemplate(){
        return '{{%ctemplate}}';
    }
    
    public static function tableItemToTemplate(){
        return '{{%item_to_template}}';
    }
    
    
    public function getAllBlock(){
        $query = static::find()->from(Template::tableBlockTemplate())->where(['is_active'=>1]);
        
        return $query->orderBy(['type_code'=>SORT_ASC,'name'=>SORT_ASC])->asArray()->all();
    }
    
    public function getAllAvailableTemplate(){
        $query = static::find()->from(Template::tableName())->where(['is_active'=>1,'is_invisible'=>0]);
        
        return $query->orderBy(['title'=>SORT_ASC])->asArray()->all();
    }
    
    public function getRefTemplateNameByItem($item_id){
        $query = static::find()->from(Template::tableItemToTemplate())->where(['item_id'=>$item_id]);
        
        $v = $query->asArray()->one();
        
        if(!empty($v)) return $v['temp_id'];
        
    }
}