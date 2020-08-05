<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\slink;
use Yii;
class ShortLink extends \yii\base\Component
{
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\slink\ShortLinkModel']);
        }
        return $this->_model;
    }
    
    public function getId($length = 6){
        return $this->getModel()->getId($length);
    }
    
    public function createUrl($params){
        return $this->getModel()->createUrl($params);
    }
    
    public function updateUrl($id, $params){
        return $this->getModel()->updateUrl($id, $params);
    }
    
}