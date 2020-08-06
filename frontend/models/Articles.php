<?php 
namespace izi\frontend\models;
use Yii;
use yii\db\Query;

class Articles extends \app\models\Articles
{
    public $frontend;
    
    public $box;
    
    public static function tableName()
    {
        return '{{%articles}}';
    }
    
    
    public function getItem($id, $params = [])
    {
        $query = static::find()
        ->from(['a'=>$this->tableName()])
        ->select(['a.*'])
        ->where(['a.id'=>$id]);
        
        return $this->populateData($query->asArray()->one());
    }
    
    public function getItemByUrl($url, $params = [])
    {
        $query = static::find()
        ->from(['a'=>$this->tableName()])
        ->select(['a.*'])
        ->where(['a.url'=>$url]);
        
        
        return $this->populateData($query->asArray()->one());
    }
    
    
    public function findItemByUrl($url, $params = [])
    {
        $query = static::find()
        ->from(['a'=>$this->tableName()])
        ->select(['a.*'])
        ->where(['a.url'=>$url]);
        
        return $this->populateData($query->asArray()->one());
    }
    
    
    public function findItemByCode($code, $params = [])
    {
        $query = static::find()
        ->from(['a'=>$this->tableName()])
        ->select(['a.*'])
        ->where(['a.code'=>$code]);
        
        return $this->populateData($query->asArray()->one());
    }
    
    
    public function getItems($params)
    {
        //view($params);
        /**
         * Box param 
         */
        $list_orderby = [];
        $key = isset($params['key']) ? $params['key'] : '';
        $box_id = isset($params['box_id']) && $params['box_id'] > 0 ? $params['box_id'] : 0;
        $box_code = isset($params['box_code']) ? $params['box_code'] : '';
        
        $box = isset($params['box']) && is_array($params['box']) ? $params['box'] : [];

        $box2 = [];
        
        $box_params = isset($params['box_params']) && is_array($params['box_params']) ? $params['box_params'] : [];
        
        if(empty($box) && (strlen($_boxcode = $key) > 0 || strlen($_boxcode = $box_code) > 0 )){
            $box = $this->box->getBox($_boxcode, $box_params);
        }elseif($box_id>0){
            
            $box = $box2 = $this->box->getItem($box_id);
            
        }
        
        // Product type
        $type =  isset($params['type']) ? $params['type'] : Yii::$app->controller->id;
        
        $category_id = isset($params['category_id']) ? $params['category_id'] : (isset($params['category']) ? $params['category'] : 0);
        
        $filter_text = isset($params['filter_text']) ? $params['filter_text'] : '';
        
        $search = isset($params['search']) && $params['search'] ?: false;
        
        $count = isset($params['count']) && $params['count'] ?: false;
        
        $action_detail = isset($params['action_detail']) ? $params['action_detail'] : '';
        
        $sort_subtitle = isset($params['sort_subtitle']) && $params['sort_subtitle'] == true ? true : false;
        
        $attr = isset($params['attr']) ? $params['attr'] : '';
        
        
        
        if($search && isset($params['q'])){
            $filter_text = $params['q'];
        }
        /**
         * Paging
         */
        $p = isset($params['p']) && is_numeric($params['p']) ? $params['p'] : 1;
        
        $limit = isset($params['limit']) && $params['limit']>0 ? $params['limit'] : (!empty($box) && $box['limit']>0 ? $box['limit'] : 12);
        
        $offset = ($p-1) * $limit;
        
        
        $sort = isset($params['sort']) ? $params['sort'] : '';
        
        $vb = $box;
        
        if($box_id>0){
            if(!empty($box2)){
                $vb = $box2;
            }else{
                $vb = $this->box->getItem($box_id);
            }
            
        }
         
        
        /**
         * add filter
         */
        $filters = [];
        
        /**
         * Lấy các item được chỉ định trực tiếp trong cài đặt box
         */
        $_box['list_items'] = [];
        if(!empty($vb)){
                        
            /**
             * Thuộc tính type theo box có mức độ ưu tiên cao hơn
             */
            if(isset($vb['form']) && $vb['form'] != ""){
                $type = $vb['form'];
            }
            
            if(isset($vb['attr']) && !empty($vb['attr'])){
                $attr = $vb['attr'];
            }
             
            
            /**
             * Kiển tra box có gán menu
             */
            if($vb['menu_id'] > 0){
                $m = $this->frontend->menu->getItem($vb['menu_id']);
                if(!empty($m)){
                    $type = $m['type'];
                    $action_detail = isset($m['action_detail']) && $m['action_detail'] != "" ? $m['action_detail'] : $action_detail;
                    $category_id = $vb['menu_id'];
                }
                
                if(isset($vb['articles_list']) && !empty($vb['articles_list'])){
                    $_box['list_items'] = $vb['articles_list'];
                    
                } else{
                    $_box['list_items'] = isset($m['list_items']) && !empty($m['list_items']) ? $m['list_items'] : $_box['list_items'];
                    
                }
                
                
            }elseif(isset($vb['articles_list']) && !empty($vb['articles_list'])){
                $_box['list_items'] = $vb['articles_list'];
                
            }
            
            
            
            if(isset($vb['filter_by']) && !empty($vb['filter_by'])){
                $filters += is_array($vb['filter_by']) ? $vb['filter_by'] : [$vb['filter_by']];
            }
            
            if(isset($vb['order_by']) && !empty($vb['order_by'])){
                $sort = $vb['order_by'][0];
                $list_orderby = $vb['order_by'];
            }
            
            /**
             * Add filter
             */
            
            //$filters += \app\modules\admin\models\Box::getFilterExisted($vb['id'],'id');
            
        }
        
        if(!empty($_box['list_items'])){
            $category_id = 0;
            $attr = null;
            $type = false;
        }
        
        /**
         * Build query 
         */
        $query = static::find()->select(['a.*'])
        ->from(['a'=>Articles::tableName()])
        ->where(['a.is_active'=>1, 'a.sid'=>__SID__])
        ->andWhere(['>','a.state',-2]);
//         if(!$detail){
        $query->andWhere(['a.is_invisibled'=>0]);
//         }

        if(isset($params['hide_expired']) && $params['hide_expired']){
            $query->andWhere(['>', 'a.expired_date', __TIME__]);
        }
         
        /**
         * Check action detail
         */
        
          
        switch ($action_detail){
            
            case '{get_hot_item}': // Get item hot
                 
                $attr = 'is_hot';
                break;
                
            default:
                
                $required_type = true;

                if($category_id > 0){
                    $subQuery = (new Query())->select(['item_id'])->from(['{{%items_to_category}}'])->where([
                        'category_id'   =>  $this->frontend->menu->getAllChildID($category_id)
                    ]);

                    if(isset($_box['list_items']) && !empty($_box['list_items'])){
                        
                        $query->andWhere(['or',[
                            'a.id'=>$subQuery
                        ],[
                            'a.id'=>$_box['list_items']
                        ]]);
                        
                        $required_type = false;
                        
                    }else{
                        $query->andWhere(['a.id'=>$subQuery]);
                    }
                    
                }else{
                    if(isset($_box['list_items']) && !empty($_box['list_items'])){
                        $query->andWhere(['in','a.id',$_box['list_items']]);
                        
                        $required_type = false;
                    }
                    
                }
                 
                
                if($type !== false && $required_type)
                    $query->andWhere(['a.type'=>$type]);
                
                break;
        }
        
        
        /**
         * Search
         */
        if(strlen($filter_text)>1){
            $query->andWhere(['or',
                ['like','a.code', $filter_text],
                ['like','a.title', $filter_text],
                ['like','a.url', unMark($filter_text)],
            ]);
        }
        
        if(isset($params['in']) && $params['in'] != null){
            $query->andWhere(['a.id' => $params['in']]);
        }
        
        if(isset($params['not_in']) && $params['not_in'] != null){
            $query->andWhere(['not in','a.id',$params['not_in']]);
        }
        
        if(isset($params['other']) && $params['other'] != null){
            $query->andWhere(['not in','a.id',$params['other']]);
        }
        
        /**
         * applied filter
         */
        $ft_filters = isset($params['filters']) ? $params['filters'] : '';
        if($ft_filters != "" && !is_array($ft_filters)){
            $ft_filters = explode(',', $ft_filters);
        }
        
        
        if(is_array($ft_filters) && !empty($ft_filters)){
            foreach ($ft_filters as $a){
                if($a > 0) $filters[] = $a;
            }
        }
        
        /**
         * 
         */
        
        
        if(!empty($filters)){            
            
            $fArrays = Yii::$app->filter->model->getFilters(['id'=>$filters,'parent_id'=>-1,'select'=>['a.id','a.menu_id','a.code','a.value','a.value1']]);            
            
            $f1 = $f2 = [];
            if(!empty($fArrays)){
                foreach ($fArrays as $f){
                    switch ($f['code']){
                        case 'filter_prices':
                            $query->andWhere(['between','a.price2',$f['value'],$f['value1']]);
                            break;
                        default:
                            if($f['menu_id'] > 0){
                                $fxs = Yii::$app->frontend->menu->getAllChildID($f['menu_id']);
                                if(!empty($fxs)){
                                    foreach ($fxs as $fx){
                                        $f1[] = $fx;
                                    }
                                }
                            }else{
                                $f2[] = $f['id'];
                            }
                            break;
                    }
                    
                    
                    
                    
                }
            }
            
            if(!empty($f1)){
                $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('items_to_category')->where(['in','category_id',$f1])->groupBy('item_id')]);
            }
            if(!empty($f2)){
                $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where(['in','filter_id',$f2])]);
            }
            
        }
        
        /**
         * check by attr
         */
        $recent = false;
        // Check Attr
        if($attr == 'recent'){
            $recent = true;  $attr = false;
        }
        
        
        
        if($attr != ""){
            if(is_array($attr) && !empty($attr)){
                
                foreach ($attr as $kt=>$at){
                    if($at == 'recent'){
                        $recent = true;
                        unset($attr[$kt]);
                    } 
                        
                }
                 
            } 
            
            
            $subQuery = (new Query())->select('g.item_id')->from(['g'=>'{{%articles_to_attrs}}'])
            ->innerJoin(['h'=>Articles::tableName()],'g.item_id=h.id')
            ->where(['h.sid'=>__SID__,'g.state'=>1,'g.attr_id'=>$attr]);
            $query->andWhere(['in','a.id',$subQuery]);
                
             
        }
        
        $places = isset($params['places']) ? $params['places'] : [];
        
        /**
         * 
         */
        switch ($type) {
            case 'tours':
                $query -> innerJoin(['t' => 'tours_attrs'], 'a.id=t.item_id');
                $query->addSelect(['t.*']);
                
                if(isset($params['filter_duration']) && !empty($durations = $params['filter_duration'])){
                    $duration_con = ['or'];
                    foreach ($durations as $duration){
                        $d = explode('-', $duration);
                        if(!isset($d[1])) $d[1] = 999;
                        
                        $duration_con[] = [
                            'between', 't.day', (int)$d[0], (int)$d[1]
                        ];
                        
                        
                    }
                     
                    $query->andWhere($duration_con);
                }
                
                $f2 = [];
                if(isset($params['filter_type']) && !empty($filter_type = $params['filter_type'])){
                    
                    foreach ($filter_type as $ft){
                        $filter = Yii::$app->filter->model->getFilters([
                            'query' => 'one',
                            'code'=>'tour_type',
                            'filter_value' => $ft
                        ]);
                        
                       
                        if(!empty($filter)) $f2[] = $filter['id'];
                    }
                    if(!empty($f2)){
                        $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where(['in','filter_id',$f2])]);
                    }
                    
                }
                
                $f2 = [];
                if(isset($params['filter_region']) && !empty($filter_type = $params['filter_region'])){
                    
                    foreach ($filter_type as $ft){
                        $filter = Yii::$app->filter->model->getFilters([
                            'query' => 'one',
                            'code'=>'tour_region',
                            'filter_value' => $ft
                        ]);
                        
                        
                        if(!empty($filter)) $f2[] = $filter['id'];
                    }
                    if(!empty($f2)){
                        $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where(['in','filter_id',$f2])]);
                    }
                    
                }
                
                $f2 = [];
                if(isset($params['filter_theme']) && !empty($filter_type = $params['filter_theme'])){
                    
                    foreach ($filter_type as $ft){
                        $filter = Yii::$app->filter->model->getFilters([
                            'query' => 'one',
                            'code'=>'tour_theme',
                            'filter_value' => $ft
                        ]);
                        
                        
                        if(!empty($filter)) $f2[] = $filter['id'];
                    }
                    if(!empty($f2)){
                        $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where(['in','filter_id',$f2])]);
                    }
                    
                }
                
                if(isset($params['filter_destination']) && !empty($filter_type = $params['filter_destination'])){
                    $places += $filter_type;
                }
                
            break;
            
            default:
                ;
            break;
        }
        
        /**
         * Place
         */
        if(!empty($places)){
            $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('item_to_place')->where(['in','place_id',$places])]);
        }
        
        
        
        
        /*/
         1=>'Tên / tiêu đề (a-z)',
         2=>'Tên / tiêu đề (z-a)',
         3=>'Thời gian (tăng)',
         4=>'Thời gian (giảm)',
         5=>'Giá (tăng)',
         6=>'Giá (giảm)',
         100=>'Ngẫu nhiên',
         /*/
        $order_array = []; $order_rand = false;
        if(!empty($list_orderby)){
            foreach ($list_orderby as $order){
                switch ($order){
                    case 4: // Mới nhất
                        $order_array['a.updated_at'] = SORT_DESC;
                        break;
                    case 3: // Cux nhất
                        $order_array['a.updated_at'] = SORT_ASC;
                        break;
                    case 6: // Giá cao - thấp
                        
                        $order_array['a.price2'] = SORT_DESC;
                        
                        break;
                    case 5: // Giá thấp - cao
                        
                        $order_array['a.price2'] = SORT_ASC;
                        break;
                    case 1: // Tên a - z
                        if($sort_subtitle){
                            $order_array['a.short_title'] = SORT_ASC;
                            $order_array['a.title'] = SORT_ASC;
                        }else{
                            $order_array['a.title'] = SORT_ASC;
                        }
                        break;
                    case 2: // Tên z - a
                        
                        if($sort_subtitle){
                            $order_array['a.short_title'] = SORT_DESC;
                            $order_array['a.title'] = SORT_DESC;
                        }else{
                            $order_array['a.title'] = SORT_DESC;
                        }
                        break;
                    case 100:
                        
                        $order_rand = true;
                        
                        break;
                        
                }
            }
        }
        
        $order_array['a.position'] = SORT_ASC;
        if(!$order_rand && !isset($order_array['a.time'])){
            $order_array['a.updated_at'] = SORT_DESC;
        }
         
        if($order_rand){
            $order_array = 'rand()';
        }
        
        /**
         * 
         */
        
        $query->andWhere(['a.lang'=>__LANG__]);
        
        if(isset($params['post_by_name']) && $params['post_by_name']){
            $query->addSelect(['post_by_name'=>'concat(z.lname, \' \' , z.fname)']);
            $query->leftJoin(['z'=>'{{%users}}'],'a.created_by=z.id');
        
        }
        
        if($recent){
            //
            $cookies1 = Yii::$app->request->cookies;
            $r = $cookies1->getValue('recent_viewed', []);
            $query->andWhere(['a.id'=>(array_slice($r, 0, $limit))]);
        }
                
        $total_items = 0;
        // Excute query 
        if($count){
            $total_items = $query->count(1);
        }
        
        
        
        $query->offset($offset)->limit($limit)->orderBy($order_array);
        
        //view($query->createCommand()->getRawSql());
        
        $list_items = $this->populateData($query->asArray()->all());
        return [
            /**
             * OLD
             */
            'listItem'=>$list_items,
            'totalItem'=>$total_items,
            /**
             * 
             */
            'list_items'    =>  $list_items,
            'total_items' =>  $total_items,           
            'total_pages'=> $limit > 0 ? ceil($total_items/$limit) : 1,
            'p'=>$p,
            'key'=>$key,
            'limit'=>$limit,
            'box'=>$vb
        ];
    }
    
    
    public function getTourPriceDetail($item_id, $departure_date)
    {
        $date = date('Y-m-d', strtotime(str_replace('/', '-', $departure_date)));
        
        $query = static::find()
        ->from(['a'=>'filters'])
        ->innerJoin(['b'=>'articles_to_filters'],'a.id=b.filter_id')
        ->where([
            'a.sid'     =>  __SID__,
            'a.code'    =>  'tour_date_time',
            'a.date'    =>  $date
        ])
        ;
        
        $query->select(['a.date', 'a.time', 'b.*']);
        return $this->populateData($query->orderBy(['a.date'=>SORT_ASC])->asArray()->one());
    }
    
    
    public function resetTourPrices($item_id)
    {
        $l = static::find()->from('articles_prices')->where([
            'item_id'=>$item_id,
        ])->asArray()->all();
        
        foreach ($l as $v){
            
            $v['price1'] = $v['price2'] = $v['price3'] = $v['price4'] = 0;
            
            $date = str_replace('tour_date_time_', '', $v['code']);
            
            //
            preg_match_all('/\d{4}\-\d{2}\-\d{2}/',$v['code'] ,$matches);
            
            if(!empty($matches[0])){
                $date = $matches[0][0];
                 
                
                if(strtotime($date) + 14440 > time()){
                    Yii::$app->db->createCommand()->update('articles_prices', ['date'=>$date, 'state'=>2], ['code'=>$v['code'] , 'item_id'=>$v['item_id']])->execute();
                }else{
                    Yii::$app->db->createCommand()->update('articles_prices', ['date'=>$date, 'state'=>0], ['code'=>$v['code'] , 'item_id'=>$v['item_id']])->execute();
                    
                }
                
            }
            
            
        }
    }
    
    public function getTourPrices($item_id, $params = [])
    {
        $this->resetTourPrices($item_id);
        // articles_prices
        // Get list price
        $l = static::find()->from('articles_prices')->where(['and', [
            'item_id'=>$item_id,
        ],
            
        [
            '>' , 'state' , 0
        ]
//             ,
            
//         [
//             'like'  , 'code' , 'tour_date_time_%', false
//         ]
            
        ])->orderBy(['code'=>SORT_ASC])->asArray()->all();
        
        
        $date_lists = [];
        if(!empty($l)){
            foreach ($l as $v){
               
                $v['price1'] = $v['price2'] = $v['price3'] = $v['price4'] = 0;
                
                $date = str_replace('tour_date_time_', '', $v['code']);
                
                // 
                preg_match_all('/\d{4}\-\d{2}\-\d{2}/',$v['code'] ,$matches);
                
                if(!empty($matches[0])){
                    $date = $matches[0][0];                                       
                }
                
                
                if(strtotime($date) + 14440 > time()){
                
                    if(!isset($date_lists[$date])){
                        $date_lists[$date] = $v;
                    }
                    
                    
                    
                    
                    $precode = str_replace($date, '', $v['code']);
                    
                    switch ($precode){
                        case 'tour_date_time_':
                            
                            $date_lists[$date]['price2'] = $v['price'];
                            break;
                            
                         case 'old_price_tour_date_time_':
                             $date_lists[$date]['price1'] = $v['price'];
                            break;
                            
                         case 'single_suppliment_tour_date_time_':
                             $date_lists[$date]['price3'] = $v['price'];
                            break;   
                            
                    }
                    
                    if(!empty($d = $this->getTourPriceDetail($item_id, $date))){
                        $date_lists[$date] += $d;
                    }
                
                
                }
                
                
            }
        } 
        
//         view($date_lists,1,1);
        
        return $date_lists;
    }
    
    
    
    
    public function countItemComment($item_id){
        return (new \yii\db\Query())->from('comments')->where(['item_id'=>$item_id])->count(1);
    }
    
    
    
    
    
    public function quickImportNewsData($url, $params = [])
    {
        $category_id = isset($params['category_id']) ? $params['category_id'] : 0;
        
        $categories = isset($params['categories']) ? $params['categories'] : [];
        
        if($category_id > 0 && !in_array($category_id, $categories)){
            $categories[] = $category_id;
        }
        
        if(!is_array($url)){        
//             $f = $this->getNewsData($url);
            return;
        }else{
            $f = $url;
        }
        
        if(isset($f['category'])){
            $categories = $f['category'];
            unset($f['category']);
        }
        
        
        if(!empty($f)){
            
            $f['url'] = unMark($f['title']);
            
            $f['type'] = 'news';
            
            $f['sid'] = __SID__;
            
            $f['is_active'] = 1;
            
            $f['time'] = $f['updated_at'] = date('Y-m-d H:i:s');
            
            $f['updated'] = time();
            
            
            $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
             
            
            $item = Yii::$app->frontend->model->findItemByUrl($f['url']);
            
            
            if(!empty($item)){
                
                
                if(isset($params['overwrite']) && $params['overwrite'] == true){
                    
                    Yii::$app->db->createCommand()->update(\izi\frontend\models\Articles::tableName(), $f, ['id' => $item['id']])->execute();
                    return 1;
                }
                
                return 0;
            }
            
            Yii::$app->db->createCommand()->insert(\izi\frontend\models\Articles::tableName(), $f)->execute();
            
            $id = Yii::$app->db->lastInsertID;
            
            // Update category
            if(!empty($categories)){
                foreach ($categories as $category_id){
                    if($category_id > 0)
                        Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$category_id])->execute();
                }
            }
            
            
            \app\modules\admin\models\Slugs::updateSlug($f['url'],$id,$f['type'],1,$f);
            
            return 1;
        }
        
        return 0;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}

