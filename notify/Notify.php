<?php
namespace izi\notify;
use Yii;

class Notify extends \yii\base\Component
{
    
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\notify\models\Notify']);
        }
        return $this->_model;
    }
    
    public function sentNotify($params){
        return $this->getModel()->sentNotify($params);
    }
    
    
    public function sent($params){
        return $this->getModel()->sentNotify($params);
    }
}