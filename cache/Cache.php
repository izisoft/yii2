<?php 
namespace izi\cache;

use Yii;

class Cache extends \yii\base\Component
{
    private $_session;
    
    
    
    public function getSession(){
        if($this->_session == null){
            $this->_session = Yii::$app->session;
        }        
        return $this->_session;
    }
    
    public function getCache($params){        
        return $this->getSession()->get($this->getKey($params),null);        
    }
    
    public function remove($params){ 
        $key = $this->getKey($params);

        if ($this->getSession()->has($key)){
         
            $this->getSession()->remove($key);
            
        }
    }
    
    public function store($value, $params){
        $key = $this->getKey($params);
        $this->getSession()->set($key,$value);
    }
    
    public function encript($params){
        return md5(json_encode($params));
    }
    
    public function getKey($params){
        return $this->encript($params);
    }
}