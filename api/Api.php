<?php 
namespace izi\api;
use Yii;
class Api extends \yii\base\Component
{
    
    private $_facebook;
    public function getFacebook(){
        if($this->_facebook == null){
            $this->_facebook = Yii::createObject('izi\api\facebook\Facebook');
        }
        return $this->_facebook;
    }
    
    private $_token;
    public function getToken(){
        if($this->_token == null){
            $this->_token = Yii::createObject('izi\api\token\Token');
        }
        return $this->_token;
    }
    
    
    
    
}