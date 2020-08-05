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
class Place extends \yii\base\Component
{
    
    
    
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\models\Place']);
        }
        return $this->_model;
    }
    
    
    public function validatePlace($place, $lang = __LANG__)
    {
        $place = trim($place);
        return (new \yii\db\Query())->from($this->getModel()->tableName())->where(['title'=>$place, 'sid'=>__SID__])->count(1) > 0 ? false : true;
    }
    
    public function addPlace($placeInfo, $removeAccent = false)
    {
        if($this->validatePlace(isset($placeInfo['title']) ? $placeInfo['title'] : $placeInfo['name'])){
            $newPlace = new \izi\models\Place();                                     
            
            $newPlace->title = trim($placeInfo['title']);
            $newPlace->code = $placeInfo['code'];
            $newPlace->local_id = $placeInfo['local_id'];
            $newPlace->type_id = $placeInfo['type_id'];
            $newPlace->sid = __SID__;
            
            $newPlace->inter_code = $placeInfo['inter_code'];
            $newPlace->short_name = $placeInfo['short_name'];
            $newPlace->iata = $placeInfo['iata']; 
            $newPlace->lang_code = $placeInfo['lang_code']; 
            $newPlace->save();
        }
        
    }
    
    public function removeAccentPlace()
    {
        $l = (new \yii\db\Query())->from('places')->where(['sid'=>__SID__])->orderBy(['title'=>SORT_ASC])->all();
        
        foreach($l as $v){
            
//             $this->updateLangcode($v['id'], __LANG__);
            
//             $new_title = remove_accent($v['title']);
            
//             Yii::$app->db->createCommand()->update('places', ['title'=>$new_title], [
//                 'sid'=>__SID__,
//                 'id'=>$v['id']
//             ])
            
//             ->execute();
            
        }
    }
    
    public function importFromOtherUser($user_id)
    {
        return;
        // Get all old place
        $l = (new \yii\db\Query())->from('places')->where(['sid'=>$user_id])->orderBy(['title'=>SORT_ASC])->all();

        foreach($l as $v){
//             $this->addPlace($v);

            if($v['lang_code'] != ''){
                Yii::$app->db->createCommand()->update('places', ['lang_code'=>$v['lang_code']], [
                    'sid'=>__SID__,
                    'title'=>$v['title']
                ])
                
                ->execute();
            }
        }
        
    }
	 
    
    public function getPlaceInfo($place_id){
        
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
            Yii::$app->db->createCommand()->update(\izi\models\Place::tableName(), [
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
    
    
    public function updateDestination($place_id)
    {
        if(__LANG__ == SYSTEM_LANG) return;
        
        $item = $this->getModel()->getItem($place_id);
        
        $categorys = \app\modules\admin\models\Filters::getFilters([
            'code'=>'tour_category', 
        ]);
        
        $categorys2 = \app\modules\admin\models\Filters::getFilters([
            'code'=>'tour_category' , 'lang'=>SYSTEM_LANG
        ]);
        
        if(!empty($categorys)){
            foreach ($categorys as $v2){
                if($v2['value']>0){
                    
                    
                    if(!empty($categorys2)){
                        foreach ($categorys2 as $c2){
                            
                            
                            if($c2['value'] == $v2['value']){
                                
                                // Điểm đến
                                $c = \app\modules\admin\models\Filters::checkFilterPlace([
                                    'place_id'=>$place_id,
                                    'filter_id'=>$c2['id'],
                                    'rf_value'=>1,
                                    
                                ]);
                                
                                if($c && (new \yii\db\Query())->from('place_to_filters')->where([
                                    'place_id'=>$place_id,
                                    'filter_id'=>$v2['id'],
                                    'rf_value'=>1,
                                ])->count(1) == 0){
                                    Yii::$app->db->createCommand()->insert('place_to_filters', [
                                        'place_id'=>$place_id,
                                        'filter_id'=>$v2['id'],
                                        'rf_value'=>1,
                                    ])->execute();
                                }
                                
                                // Điểm kh
                                $c = \app\modules\admin\models\Filters::checkFilterPlace([
                                    'place_id'=>$place_id,
                                    'filter_id'=>$c2['id'],
                                    'rf_value'=>2,
                                    
                                ]);
                                
                                if($c && (new \yii\db\Query())->from('place_to_filters')->where([
                                    'place_id'=>$place_id,
                                    'filter_id'=>$v2['id'],
                                    'rf_value'=>2,
                                ])->count(1) == 0){
                                    Yii::$app->db->createCommand()->insert('place_to_filters', [
                                        'place_id'=>$place_id,
                                        'filter_id'=>$v2['id'],
                                        'rf_value'=>2,
                                    ])->execute();
                                }
                                
                            }
                        }
                    }
                    
                
                }
            }
        }
    }
    
    
    
    public function updatePlaceLocaltion()
    {
//         $l = (new \yii\db\Query())->from('places')->where(['local_id1' => 0])->limit(100)->all();
        
// //         view($l);
        
//         if(!empty(($l))){
//             foreach ($l as $v){
//                 $local = Yii::$app->local->parseCountry($v['local_id']);
//                 $d = [];
//                 if($local['country']['id']>0){
//                     $d['local_id1'] = $local['country']['id'];
//                 }
//                 if($local['province']['id']>0){
//                     $d['local_id2'] = $local['province']['id'];
//                 }
//                 if($local['district']['id']>0){
//                     $d['local_id3'] = $local['district']['id'];
//                 }
//                 if(!empty($d)){
//                     Yii::$app->db->createCommand()->update('places', $d, ['id'=>$v['id']])->execute();
//                 }else {
//                     view($v);
//                 }
//             }
//         }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}