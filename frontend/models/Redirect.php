<?php 
namespace izi\frontend\models;
 

class Redirect extends \izi\db\ActiveRecord
{
    
    public static function tableName(){
        return '{{%redirects}}';
    }
    
    
    public function getUrlRedirect($id){
        return $this->populateData((new \yii\db\Query())->from($this->tableName())->where(['rule'=>$id, 'state'=>2])->one());
    }
    
    public function getUrlGoto($id){
        return $this->populateData((new \yii\db\Query())->from('{{%short_links}}')->where(['id'=>$id])->one());
    }
    
}