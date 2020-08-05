<?php
namespace  izi\vote;
use Yii;

class Rating extends \yii\base\Component
{
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\vote\models\Rating']);
        }
        return $this->_model;
    }
    
    
    
    public function getRating($params = []){
        return $this->getModel()->getRating($params);
    }
    
}
