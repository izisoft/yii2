<?php 
namespace izi\local;
use Yii;
class Place extends \yii\base\Component
{
    public $local, $place;
    
    
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\local\models\Place']);
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
            $newPlace = new \izi\local\models\Place();
            
            $newPlace->id = $id>0? $id : $this->getId();
            
            $newPlace->lang = $lang;
            $newPlace->name = trim($placeInfo['title']);
            $newPlace->code = $placeInfo['inter_code'];
            $newPlace->local_id = $placeInfo['local_id'];
            $newPlace->type_id = $placeInfo['type_id'];
            
            $newPlace->save();
        }
            
    }


    /**
     * 
     */
    public function updateLangcode($place_id, $lang = SYSTEM_LANG)
    {
        $item = $this->getModel()->getItem($place_id);
        
        $item['title'] = trim($item['title']);
        
        $lang_code = 'text_place_' . unMark(trim($item['title']),'_');
        
        if($item['lang_code'] != $lang_code){
            Yii::$app->db->createCommand()->update($this->getModel()->tableName(), [
                'lang_code'=>$lang_code,
                'title' =>  trim($item['title'])
            ], ['id'=>$item['id']])->execute();
            
            
            
//             Yii::$app->db->createCommand()->update('user_text_translate', ['lang_code'=>$lang_code], ['lang_code'=>$item['lang_code']])->execute();
        }
        
        //user_text_translate
        if((new \yii\db\Query())->from('user_text_translate')->where(['sid'=>__SID__, 'lang_code'=>$lang_code])->count(1) == 0){
            Yii::$app->db->createCommand()->insert('user_text_translate', [
                'sid'=>__SID__,
                'lang_code'=>$lang_code,
                'lang'  =>  $lang,
                'value' =>  $item['title']
            ])->execute();
        }else{
            
            Yii::$app->t->updateLangcode($lang_code, $lang, $item['title']);
            
            Yii::$app->db->createCommand()->update('user_text_translate', ['value'=>$item['title']], [
                'sid'=>__SID__, 
                'lang_code'=>$lang_code,
                'lang'=>$lang
            ])->execute();
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