<?php
namespace  izi\note;
use Yii;

class Note extends \yii\base\Component
{
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\note\models\Note']);
        }
        return $this->_model;
    }
    
    
}
