<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\web;
use Yii;
class Box extends \yii\base\Component
{
	
	 private $_model;
	 
	 public function getModel(){
	     if($this->_model == null){
	         $this->_model = Yii::createObject('izi\models\Box');
	     }
	     
	     return $this->_model;
	 }
	
	 
	 public function getItem($id){
	     if(is_numeric($id) && $id>0){
	         return $this->getModel()->getItem($id);
	     }else{
	         return $this->getBox($id);
	     }
	 }
	 
	 public function getBox($code, $params = []){
	     return $this->getModel()->getBox($code, $params);
	 }
	 
	 public function showBoxText($code , $params = []){
	     $r = $this->getBox($code, $params);
	     if(!empty($r) && isset($r['text'])){
	         return uh($r['text'],2);
	     }
	 }
}