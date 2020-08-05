<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\filters;
use Yii;
class Filter extends \yii\base\Component
{
    public $backend;
    
    private $_model;
    
    public function getModel()
    {
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class'     =>  'izi\filters\models\Filters',
//                 'filter'    =>  $this,
//                 'backend'   =>  $this,
            ]);
        }
        
        return $this->_model;
    }
    
    
    /**
     * get filter by code
     */
    
    public function getItemsByCode($code, $params = [])
    {
        $l = $this->getModel()->getItemsByCode($code, $params);
        
        if(empty($l) && (isset($params['parent_id']) && $params['parent_id'] == 0) && isset($params['set_default']) && $params['set_default'] == true){
            //
            
        }
        
        return $l;
    }
    
	 
}