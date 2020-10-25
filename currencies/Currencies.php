<?php 
namespace izi\currencies;

class Currencies extends \yii\base\Component
{
    
    private $_default = 1;

    public function getDefault(){
        return $this->_default;
    }

}