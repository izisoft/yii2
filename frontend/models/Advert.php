<?php 
namespace izi\frontend\models;

use Yii;
use yii\db\Query;

class Advert extends \app\models\Advert
{
    

    public function getItems($params = []){
        $type = isset($params['type']) && is_numeric($params['type']) ? $params['type'] : -1;
        
        $category_id = isset($params['category_id']) && is_numeric($params['category_id']) ? $params['category_id'] : -2;
        
        $default_category_id = isset($params['default_category_id']) ? $params['default_category_id'] : -2;
        
        $box_id = isset($params['box_id']) && is_numeric($params['box_id']) ? $params['box_id'] : -1;
        $lang = isset($params['lang']) ? $params['lang'] : __LANG__;
        $code = isset($params['code']) ? $params['code'] : false;
        $index = isset($params['index']) ? $params['index'] : false;
        $is_all = isset($params['is_all']) ? $params['is_all'] : -1;
        if($index){
            if($category_id  == -1){
                //$category_id = __CATEGORY_ID__;
            }
        }
        $orderBy = isset($params['orderBy']) ? $params['orderBy'] : ['a.position'=>SORT_ASC,'a.title'=>SORT_ASC];
        $query = static::find()
        ->select(['a.*'])
        ->from(['a'=>$this->tableName()])
        ->where(['a.is_active'=>1])
        ->andWhere(['>','a.state',-2]);
        if($lang !== false){
            $query->andWhere(['a.lang'=>$lang]);
        }
        if($is_all == 1 && $code !== false){
            
        }else{
            $query->andWhere(['a.sid'=>__SID__]);
        }
        if($code !== false){
            $type = -1;
            $query->addSelect(['category_title'=>'b.title'])
            ->innerJoin(['b'=>'{{%adverts_category}}'],'a.type=b.id')
            ->andWhere(['b.code'=>$code,'b.is_active'=>1,'b.is_'.Yii::$app->device=>1] + ($lang !== false ? ['b.lang'=>$lang] : []));
            if($is_all == 1){
                $query->andWhere(['b.is_all'=>1,'b.sid'=>0]);
            }
        }
        if($type > -1){
            $query->andWhere(['a.type'=>$type]);
        }
        if($category_id > -2){
            $query->andWhere(['a.category_id'=>$category_id]);
        }
        if($box_id > -1){
            $query->andWhere(['a.box_id'=>$box_id]);
        }        
        
        $rs = $query->orderBy($orderBy)->asArray()->all();
        
        if(empty($rs) && $default_category_id > -2){
            
            $params['category_id'] = $default_category_id;
            
            $params['default_category_id'] = -2;            
            
            return $this->getItems($params);
        }
         
        $d = $this->populateData($rs);

        //$d = [];

        if(empty($d) && isset($params['migrate'])){
            //$this->migrate($params['migrate']);
            Yii::$app->frontend->migrate($params['migrate']);
        }

        return $d;
    }
     
    /**
     * update 05/08/2020
     */

    public function migrate($data)
    {
        
        $this->migrateCategory($data);
    } 


    /**
     * Khởi tao danh muc
     */
    public function migrateCategory($data)
    {
        $adv = \app\models\AdvertCategory::find()
        ->where(['sid' => __SID__, 'code' => $data['code']])
        ->with('adverts')
        ->one();

        if(!empty($adv)){
            // update code if you want
            $data['type'] = $adv->id;
            //$this->migrateAdvert($data);
        }else{
            $adv = new \app\models\AdvertCategory();
            $adv->sid = __SID__;
            $adv->title = $data['name'];
            $adv->code = $data['code'];
            $adv->info = isset($data['info']) ? $data['info'] : '';
 
            if($adv->save()){
                $data['type'] = $adv->id;
                $this->migrateAdvert($data);
            }else{
                view($adv->errors);
            }
        }
    }

    /**
     * Khởi tạo QC
     */
    public function migrateAdvert($data)
    {
        $items = isset($data['data']) ? $data['data'] : $data;
        if(!empty($items)){
            foreach($items as $item){
                $adv = Advert::findOne(['sid' => __SID__, 'image'=>$item['image'], 'type'=>$data['type']]);
                if(empty($adv)){
                    $adv = new Advert();
                    $adv->sid = __SID__;
                    $adv->type = $data['type'];
                    foreach($item as $k=>$v){
                        $adv->$k = $v;
                    }
                    $adv->save();
                }
            }
        }
    }
    
}
