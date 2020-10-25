<?php 

namespace izi\frontend;
use Yii;

use izi\frontend\models\Articles;

class Product extends \yii\base\Component
{
    public $frontend;
    
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class' =>  'izi\\frontend\\models\\Product',
                'frontend' => $this,
                'box'  =>  Yii::$app->box
            ]);
        }
        
        return $this->_model;
    }
     
    
      
    
    public function quickImportData($data, $replace = false)
    {
        $url = unMark($data['title']);
        
        $item = Yii::$app->frontend->model->getItemByUrl($url);
         
        if(empty($item)){
            
            $f['sid'] = __SID__;
            $f['lang'] = __LANG__;
            $f['created_by'] = Yii::$app->user->id;
            $f['updated_at'] = date('Y-m-d H:i:s');
            $f['is_active'] = 1;
            $f['title'] = $data['title'];
            $f['url']   = $url;
            $f['type']   = 'products';
            
            
            if(Yii::$app->db->createCommand()->insert(Yii::$app->frontend->model->tableName(),$f)->execute()){
                $item_id = Yii::$app->db->getLastInsertID();
            }
            
        }else{
            $item_id = $item['id'];
            if(!$replace) return;
        }
        
        // Update
        
        $f['price2'] = $data['price2'];
        
        $biz = [
            'icon'  =>  $data['icon'],
            'list_images'   =>  $data['list_images'],
            'info' => '',
            'summary'=>'',
            'age_groups'    =>  [3,2,1],
            
        ];
        
         
        
        $f['url_link'] = Yii::$app->izi->getUrl($url);
        
        $f['bizrule'] = json_encode($biz,JSON_UNESCAPED_UNICODE);
        
        
        
        //tab_biz[0][program][before][0][title]
        //content[ctab][0][title]
        //content[ctab][0][style]
        //content[ctab][0][text]
        
        
        if(isset($data['detail'])){
            
            $tabs = [
                ['title'=>'Thông tin sản phẩm sản phẩm', 'type'=>'text']
            ];
            
            $f['content'] = json_encode([
                'ctab'  =>  [[
                    'title'=>'Thông tin sản phẩm sản phẩm',
                    "style" =>  0,
                    "text" =>   $data['detail']
               ]],
            ], JSON_UNESCAPED_UNICODE);
            
        }
        
        
        $con = array('id'=> $item_id,'sid'=>__SID__);
        
        Yii::$app->db->createCommand()->update(Yii::$app->frontend->model->tableName(),$f,$con)->execute();
        
        $model = new \app\modules\admin\models\Content();
        
        $model->updateCategory($item_id, $data['category']);
        
         
        //item_price[price_4][price]
        if((new \yii\db\Query())->from('articles_prices')->where([
            'item_id' => $item_id,
            'code' => 'price_4'
        ])->count(1) == 0){
            $v['item_id'] = $item_id;
            $v['code'] = 'price_4';
            $v['price'] = $data['price2'];
            
            Yii::$app->db->createCommand()->insert('articles_prices',$v)->execute();
        }else{
            
            $v['price'] = $data['price2'];
            
            Yii::$app->db->createCommand()->update('articles_prices',$v,[
                'item_id' => $item_id,
                'code' => 'price_4'
            ])->execute();
        }
        
        
        \app\modules\admin\models\Slugs::updateSlug($url,$item_id,'products',1,[]);
        
        
         
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}