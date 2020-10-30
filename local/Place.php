<?php 
namespace izi\local;
use Yii;
class Place extends \yii\base\Component
{
    public $local, $place;
    
    
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\local\models\Place2']);
        }
        return $this->_model;
    }
    
    public function getId()
    {
        
        return (($id = (new \yii\db\Query())->from($this->getModel()->tableName())->max('id')) > 0 ? $id : 0) + 1;
    }
    
    public function validatePlace($place, $lang = __LANG__)
    {
        $place = trim($place);
        return (new \yii\db\Query())->from($this->getModel()->tableName())->where(['name'=>$place, 'lang'=>$lang])->count(1) > 0 ? false : true;
    }
    
    
    public function addPlace($placeInfo, $lang = __LANG__, $id = 0)
    {
        if($this->validatePlace(isset($placeInfo['title']) ? $placeInfo['title'] : $placeInfo['name'], $lang)){
            $newPlace = new \izi\local\models\Place2();
            
            $newPlace->id = $id>0? $id : $this->getId();
            
            $newPlace->lang = $lang;
            $newPlace->name = trim($placeInfo['title']);
            $newPlace->code = $placeInfo['inter_code'];
            $newPlace->local_id = $placeInfo['local_id'];
            $newPlace->type_id = $placeInfo['type_id'];
            
            $newPlace->save();
        }
            
    }
    
    
//     public function importOldPlace($lang = __LANG__)
//     {
//         // Get all old place
//         $l = (new \yii\db\Query())->from('places')->orderBy(['title'=>SORT_ASC])->all();
        
//         foreach($l as $v){
//             $this->addPlace($v, $lang);
//         }
        
//     }
    
}