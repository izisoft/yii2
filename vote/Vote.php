<?php
namespace  izi\vote;
use Yii;

class Vote extends \yii\base\Component
{
    private $_rating;
    
    public function getRating(){
        if($this->_rating === null){
            $this->_rating = Yii::createObject(['class'=>'izi\vote\Rating']);
        }
        return $this->_rating;
    }
    
     
    
}
