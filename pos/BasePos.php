<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\pos;
use Yii;
class BasePos extends \yii\base\Component
{
	 
    private $_model;
    
    public function getModel()
    {
        if($this->_model == null){
            $model = get_called_class() . "Model";
            $this->_model = Yii::createObject($model);
        }
 
        return $this->_model;
    }
    
    
    
    
    
}