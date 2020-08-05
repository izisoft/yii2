<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\backend;
use Yii;
class Backend extends \yii\base\Component
{
    
    private $_models, $_model;
    
    public function getModel($modelName = 'Backend')
    {
        $modelName = ucfirst($modelName);
        if(!isset($this->_models[$modelName])){
            $this->_models[$modelName] = Yii::createObject([
                'class' =>  'izi\backend\models\\' . $modelName,
                'backend'   => $this,
            ]);
        }
        
        return $this->_models[$modelName];
    }
    
    
    public function loadModel($modelName = 'Backend')
    {
        $modelName = ucfirst($modelName);
        if(!isset($this->_models[$modelName])){
            $this->_models[$modelName] = Yii::createObject([
                'class' =>  'izi\backend\models\\' . $modelName,
                //'backend'   => $this,
            ]);
        }
        
        return $this->_models[$modelName];
    }
    
    /**
     * Set filter component
     * @param unknown $type_id
     * @return string
     */
    
    private $_filter;
    
    public function getFilter()
    {
        if($this->_filter == null){
            $this->_filter = Yii::createObject([
                'class' =>  'izi\backend\Filter',
                'backend'   => $this,
            ]);
        }
        
        return $this->_filter;
        
    }
    
    public function getCustomerControllerByTypeId($type_id){
                
        switch ($type_id){
            case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
                $md = 'hotels';
                break;
            case TYPE_ID_VECL: case TYPE_ID_SHIP:
                $md = 'cars';
                break;
                
            case TYPE_ID_GUIDES: $md = 'guides'; break;
            case TYPE_ID_GUIDE: $md = 'guide'; break;
            case TYPE_ID_TEA: 
            case TYPE_ID_TEACHER: $md = 'teachers'; break;
            
            case TYPE_ID_MEMBER:
            case TYPE_ID_MEMBERS: $md = 'students'; break;
       
            
                
            case 0: $md = 'customers'; break;
            default:
                $md = 'services_provider';
                break;
        }
        return $md;
    }
    
    
    /**
     * 
     */
    public function initFilters($code)
    {
        switch ($code) {
            case 'tour_category':
                
            break;
            
            
        }
    }
    
    
    public function getEavAttrFromCategory($category)
    {
        // category_to_goods_group
        
        $query = (new \yii\db\Query())->from(['a'=>'goods_groups'])
        ->innerJoin(['b'=>'category_to_goods_group'], 'a.id=b.group_id')
        ->where(['b.item_id'=>$category]);
        
       $groups = $query->select(['a.*'])->all();
       
       $r = [0];
       
       if(!empty($groups)){
           foreach($groups as $g){
               if(isset($g['attributes']) && !empty($g['attributes'])){
                   foreach($g['attributes'] as $g2){
                       if(!in_array($g2, $r)){
                           $r[] = $g2;
                       }
                   }
               }
           }
       }
       
       
       return $this->loadModel('MgEavAttribute')->getAll(['in'=>$r]); 
    }
    
    
    
    
    
    public function renderForminput($attr , $params = [])
    {
        
        $colClass = isset($params['colClass']) ? $params['colClass'] : 'col-sm-12';
        
        $html = '<div class="'.$colClass.'">';
        $html .= '<div class="form-group group-sm34"><label class="control-label">'.$attr['frontend_label'].'</label>';
        
        
        switch ($attr['frontend_input']){
            case 'select': case 'multiselect':
                
                if(method_exists($this, ($method_name = 'renderForminput' . ucfirst($attr['attribute_code'])) )){
                    $html .= $this->$method_name();
                }else{
                
                $html .= '<div class="input-group">
<select class="ajax-chosen-select-ajax form-control" data-search="hidden" name=""><option>[--không chọn--]</option>';
                
                if($attr['backend_model']  != ""){
                    
                    $model = new $attr['backend_model'];
                    
                    if(!empty($list = $model->getAll()))
                    {
                        foreach ($list as $v){
                            
                            //$name = Yii::$app->t->translate($v['lang_code']);
                            
                            //$html .= '<option value="'.$v['id'].'">'.$name.'</option>';
                        }
                    }
                    
                }
                
                
                $html .= '</select>
                    
                <span class="input-group-btn">
                <button class="btn btn-default lte" type="button"><i class="fa fa-plus"></i> Thêm nhanh</button>
                </span>                    
                </div>';
                }
                
                break;
        }
        
        
        $html .= '</div></div>';
        return $html;
    }
    
    
    public function renderForminputCountry_of_manufacture($params = [])
    {
        $html = '<select class="ajax-chosen-select-ajax form-control" data-search="hidden" name=""><option>[--không chọn--]</option>';
                
        if(!empty($list = Yii::$app->local->getCountries()))
        {
            foreach ($list as $v){
                
                $name = Yii::$app->t->translate($v['lang_code']);
                
                $html .= '<option value="'.$v['id'].'">'.$name.'</option>';
            }
        }
                                
        $html .= '</select>';
        
        return $html;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
	 
}