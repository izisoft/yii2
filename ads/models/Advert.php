<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\ads\models;
use Yii;
class Advert extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%adverts}}';
    }
    
    public static function tableCategory(){
        return '{{%adverts_category}}';
    }
    
    public function initData(){
        $params = [
            __CLASS__,
            __FUNCTION__,
            date('d'),
            __LANG__
        ];
        
        $cache = Yii::$app->icache->getCache($params);        
        if(!!empty($cache)){
            $_a = [
                ['sid'=>__SID__,'code'=>'ADV_SLIDER','title'=>'Slider', 'lang'=>__LANG__],
                ['sid'=>__SID__,'code'=>'ADV_POPUP','title'=>'Popup', 'lang'=>__LANG__],
                
            ];
            foreach ($_a as $value) {
                if((new \yii\db\Query)->from(Advert::tableCategory())->where(['code'=>$value['code'],'sid'=>__SID__, 'lang'=>__LANG__])->count(1) == 0){
                    Yii::$app->db->createCommand()->insert(Advert::tableCategory(),$value)->execute();
                }
            }
            Yii::$app->icache->store(true, $params);
        }
    }
}