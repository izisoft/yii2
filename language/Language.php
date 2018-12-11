<?php 
namespace izi\language;

use Yii;

class Language extends \yii\base\Component

{
    
    private $_model ;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject(Model::class);
        }
        return $this->_model;
    }
    
    public function getDefault(){
        
        $l = \app\models\SiteConfigs::getConfigs('LANGUAGE', null, __SID__);
        
        if(!empty($l)){
            foreach ($l as $v){
                if(isset($v['is_default']) && $v['is_default'] == 1){
                    return $v;
                }
            }
        }
        
    }
    
    
    public function initDefaultLanguage(){
        
        $items = Yii::$app->l->model->getDefault();
        
        foreach ($items as $k => $item) {
            if(($item['code']) == SYSTEM_LANG){
                $item['is_default'] = 1;
                
                $items[$k] = $item;
            }
        }
        
        $conditions = [
            'code'=>'LANGUAGE',
            'sid'=>__SID__,
            'lang'=>SYSTEM_LANG
        ];
         
        
        \app\models\SiteConfigs::updateData($items, $conditions);
        
        
        return SYSTEM_LANG;
    }
    
 
}
