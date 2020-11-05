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
use yii\helpers\StringHelper;
use yii\base\Behavior;
class Permission extends \yii\base\Component
{
	
	 private $_model;
	 
	 public function getModel(){
	     if($this->_model == null){
	         $this->_model = Yii::createObject('izi\models\Permission');
	     }
	     
	     return $this->_model;
	 }
	
	 public $root = 'root', $admin = 'admin', $dev = 'dev';
	 
	 public $director = 'director', $manager = 'manager', $leader = 'leader';
	 
	 public $sale = 'sale', $accounting = 'accounting', $operator = 'operator';
	 
	 private $_specialPermission = [
	     'root' => [
	         'title' => 'Người có quyền cao nhất hệ thống',
	         'level' => 0
	     ],
	     'admin' => [
	         'title' => 'Quản trị hệ thống',
	         'level' => 1
	     ],
	     'director' => [
	         'title' => 'Ban giám đốc',
	         'level' => 2
	     ],
	     'manager' => [
	         'title' => 'Quản lý',
	         'level' => 3
	     ],
	     'leader' => [
	         'title' => 'Leader',
	         'level' => 4
	     ],
	     'sale' => [
	         'title' => 'Kinh doanh',
	         'level' => 5
	     ],
	     'accounting' => [
	         'title' => 'Kế toán',
	         'level' => 5
	     ],
	     'operator' => [
	         'title' => 'Điều hành',
	         'level' => 5
	     ],
	     'technical'   =>  [
	         'title' => 'Kỹ thuật',
	         'level' => 5
	     ],
	     'seo'   =>  [
	         'title' => 'Seo web',
	         'level' => 5
	     ],
	     'web'   =>  [
	         'title' => 'Quản trị web',
	         'level' => 5
	     ],
	     'inspector'   =>  [
	         'title' => 'Kiểm duyệt',
	         'level' => 5
	     ],
	     
	     'tester'   =>  [
	         'title' => 'Thử nghiệm',
	         'level' => 5
	     ],
	 ];
	 
	 private $_behaviors;
	 public function __get($name)
	 {
	     $getter = 'get' . $name;
	     if (method_exists($this, $getter)) {
	         // read property, e.g. getName()
	         return $this->$getter();
	     }
	     
	     // behavior property
	     $this->ensureBehaviors();
	     if(!empty($this->_behaviors)){
	     foreach ($this->_behaviors as $behavior) {
	         if ($behavior->canGetProperty($name)) {
	             return $behavior->$name;
	         }
	     }
	     }
	     if (method_exists($this, 'set' . $name)) {
	         throw new \yii\base\InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
	     }	     
	     
	     
	     if(isset($this->_specialPermission[$name])){
	         return $name;
	     }
	     throw new \yii\base\UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
	 }
	 
	 public function __set($name, $value)
	 {
	     $setter = 'set' . $name;
	     if (method_exists($this, $setter)) {
	         // set property
	         $this->$setter($value);
	         
	         return;
	     } elseif (strncmp($name, 'on ', 3) === 0) {
	         // on event: attach event handler
	         $this->on(trim(substr($name, 3)), $value);
	         
	         return;
	     } elseif (strncmp($name, 'as ', 3) === 0) {
	         // as behavior: attach behavior
	         $name = trim(substr($name, 3));
	         $this->attachBehavior($name, $value instanceof Behavior ? $value : Yii::createObject($value));
	         
	         return;
	     }
	     
	     // behavior property 
	     $this->ensureBehaviors();
	     foreach ($this->_behaviors as $behavior) {
	         if ($behavior->canSetProperty($name)) {
	             $behavior->$name = $value;
	             return;
	         }
	     }
	     
	     if (method_exists($this, 'get' . $name)) {
	         throw new \yii\base\InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
	     }
	     
	     throw new \yii\base\UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
	 }
	 
	 
	 
	 public function getPermission($permission = null, $params = []){
	     if($permission != null){
	         if(isset($this->_specialPermission[$permission])){
	             return $this->_specialPermission[$permission];
	         }
	     }else{
	         $allows = isset($params['allows']) ? $params['allows'] : [];
	         $igrone = isset($params['igrone']) ? $params['igrone'] : [];
	         $rs = [];
	         foreach ($this->_specialPermission as $permission => $val){
	             if(!empty($allows) && in_array($permission, $allows) && !in_array($permission, $igrone)){
	                 $rs[$permission] = $val;
	             }elseif(!in_array($permission, $igrone)){
	                 $rs[$permission] = $val;
	             }
	         }
	         return $rs;
	     }
	     return [];
	 }
	  
}