<?php
namespace izi\menu;
use Yii;

class Menu extends \yii\base\Component
{
      
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject('izi\\menu\\models\\Menu');
        }
        
        return $this->_model;
    }
}