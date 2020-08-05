<?php 
namespace izi\sim\es;
use Yii;
class Sim2 extends \yii\base\Component
{
    public $client;
    private $_index = 'sim2';
    private $_type = 'sim_data';
    
    public function init()
    {
         
        $hosts = [
            [
                'host' => 'localhost',          //yourdomain.com
                'port' => '9200',
                'scheme' => 'http',             //https
                //        'path' => '/elastic',
            //        'user' => 'username',         //nếu ES cần user/pass
            //        'pass' => 'password!#$?*abc'
            ],
            
        ];
        
        //Tạo đối tượng Client
        $this->client = \Elasticsearch\ClientBuilder::create()
        ->setHosts($hosts)
        ->build();
    }
     
    public function createIndex()
    {
        $params = [
            'index' => $this->_index
        ];
        
        
        //Kiểm tra xem Index đã tồn tại không
        $indexExist = $this->client->indices()->exists($params);
        
        if (!$indexExist) {
            try {
                //Thực hiện tạo Index
                $response = $this->client->indices()->create($params);
                
                
            }
            catch (\Exception $e) {
                //Lỗi tạo Index
                $res = json_decode($e->getMessage());
                echo $res->error->reason;
            }
        }
        else {
            echo "Index {$params['index']} đã có rồi!";
        }
    }
    
    public function validateIndex()
    {
        $params = [
            'index' => $this->_index
        ];
        
        //Kiểm tra xem Index đã tồn tại không
        return $this->client->indices()->exists($params);
    }
    
//     public function removeIndex()
//     {
//         $params = [
//             'index' => $this->_index
//         ];
        
//         //Kiểm tra xem Index đã tồn tại không
//         $indexExist = $this->client->indices()->exists($params);
        
//         if ($indexExist) {
//             $response = $this->client->indices()->delete($params);
//             echo "Đã xóa";
//         }
//         else {
//             echo "Index {$params['index']} không có";
//         }
//     }

    public function removeItem($simId, $partner_id)
    {
        $st = [];
        $items = $this->getDocument($simId, $partner_id);
        
        if(isset($items['hits']['hits']) && !empty($items['hits']['hits'])){
            foreach ($items['hits']['hits'] as $item){
                $params = [
                    'index' => $this->_index,
                    'type' => $this->_type,
                    'id' => $item['_id']
                ];
                
                $st[] = $this->client->delete($params);
            }
        }
        
        
    
        return $st;
    }
    
    public function populateData($data)
    {
        $rs = [];
        
        if(isset($data['hits']['hits']) && !empty($data['hits']['hits'])){
            foreach ($data['hits']['hits'] as $item){
                $rs[] = $item['_source'];
            }
        }
        
        return $rs;
    }
    
    public function getDocumentById($id)
    {
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'id' => $id
        ];
        
        return $this->client->get($params);
    }
    
    public function getDocument($simId, $partner_id = 0)
    {
        
        $con = [
            ['match' => ['id' => $simId]]
        ];
        
        if($partner_id > 0){
            $con[] =
                ['match' => ['partner_id' => $partner_id]];
            
        }
        
        $body['query']['bool']['must'] = $con;
        
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'body' => $body,
            //             'settings'=>['index' => ['max_result_window'=>1000000]]
        ];
        
        return $this->client->search($params);
    }
    
    public function findSim($simId, $partner_id = 0)
    {
        if(!$this->validateIndex()) return [];
        
        $con = [
            ['match' => ['id' => $simId]]
        ];
        
        if($partner_id > 0){
//             $con['partner_id'] = $partner_id;
            $con[] = ['match' => ['partner_id' => $partner_id]];
        }
        
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'body' => [
                'query' => [
                    'bool' => [
//                         'filter' => [
//                                 'term' => [ 'id' => $simId]
//                         ],
                        'must' => $con
                    ]
                ]
            ]
        ];
        
        $results = $this->client->search($params);
        
        return $this->populateData($results);
    }
    
    
    public function import()
    {
//         $l = Yii::$app->sim->getItems(['limit' => 10000]);
        
        $query = new \yii\mongodb\Query();
        $query->from(\izi\sim\SimonlineMongodbModel::collectionName())->where(['<' ,'status', 3]);
        
//         view($query->count(1),1,1);
        
        $l = $query->limit(60000)->all();
         
         
        $c = 0;
        
        $body = []; $ex = [];
        
        foreach ($l as $v){
            
            Yii::$app->sim->collection->update(['id' => $v['id'], 'partner_id' => $v['partner_id']], ['status' => 3]); 
            
            if(!empty($this->findSim($v['id'], $v['partner_id']))) continue;
            
            unset($v['_id']);
            
            $body[] = [ 'index' => ['_index' => $this->_index,  '_type' => $this->_type, ] ];
            
            //$v['history'] = array(['price' => $v['price']]);
            if(isset($v['history']) && !is_array($v['history'])){
                $hp = json_decode($v['history'],1);
                
                $prices = isset($hp['price']) ? $hp['price'] : [];
                
                $pp = [];
                
                if(!empty($prices)){
                    foreach ($prices as $p){
                        if(is_array($p)){
                            $pp[] = $p;
                        }
                    }
                }
                
                $v['history'] = ['price'  => $pp];
                 
                 
            }else{
                
            }
            
            $body[] = $v;
            
            $c++;
            
            $ex [] = $v['id'];
        }
         
        
        if(!empty($body)){
            $params = [
                'body' =>$body
                ];
            
//             view($params);
            
            $responses = $this->client->bulk($params);
//             return $responses;
            if($responses['errors']){
                Yii::$app->sim->collection->update(['id' => $ex], ['status' => -1]); 
                view($ex);
                view($l);
                exit;
            }

            return $c;
        }
        
//         $params = [
//             'index' => $this->_index,
//             'type' => 'sim_data',
//             'id' => 'kj2rDm4Bv7AyHM_PNxif'
//         ];
        
//         $response = $this->client->get($params);
        
// //         view($response);
        
//         $params = [
//             'index' => $this->_index,
//             'type' => 'sim_data',
//             'body' => [
//                 'query' => [
                    
                 
//                         'regexp' => [
//                             'id' => [ "value" => '[^38].*5' , "flags" => "ALL","max_determinized_states" => 10000,
// //                                 "rewrite" => "constant_score"
//                             ]
//                         ],
                         
//                 ]
//             ]
//         ];
        
//         $results = $this->client->search($params);
//         view($results);
    }
    
    
    public function countItems($params){
        
        $params['count'] = true;
        $params['count_only'] = true;
        
        return $this->getItems($params);
    }
    
    
    public function getItems($params)
    {
        if(!$this->validateIndex()) return [];
        
        
        $limit = (int) (isset($params['limit']) ? $params['limit'] : 30);
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $p = isset($params['p']) && $params['p'] > 1 ? $params['p'] : 1;
        
        $offset = ($p - 1) * $limit;
        
        $count = isset($params['count']) && $params['count'] === true ? true : false;
        
        $price_field = isset($params['price_field']) ? $params['price_field'] : 'price2';
        
        $min_price = isset($params['min_price']) ? (float) $params['min_price'] : 0;
        
        $max_price = isset($params['max_price']) ? (float) $params['max_price'] : 0; 
        
        //$params['sosim'] = '0949*0';
        
        // Conditions
         
        
        $validate_min_price = false;
        
        
        $con = [
           // ['match' => ['id' => $simId]]
        ];
        
        $body = [];
        
        // Filter
        $sim_filter = isset($params['sim_filter']) ? $params['sim_filter'] : [];
        
        $fArrays = [
            'network_id',
            'category_id',
            'category2_id',
            'category3_id',
            'type_id',
            'partner_id'
            
        ];
        
        $rangeArrays = [
            'min_key',
            'max_key',
            'min_score',
            'max_score',
            
        ];
        
        foreach (array_merge($fArrays, $rangeArrays ) as $val){
            if(isset($params[$val]) && $params[$val]>0){
                $sim_filter[$val] = $params[$val];
            }
        }
        
        if(!empty($sim_filter)){
            foreach ($fArrays as $val){
                if(isset($sim_filter[$val])){
                    
                    switch ($val){
                        case 'category_id':
                            
                            if(is_numeric($sim_filter[$val])){
                            $con[] = ['bool' => [
                                "should" =>  [
                                    ['match' => ['category_id' => $sim_filter[$val] ]],
                                    ['match' => ['category3_id' => $sim_filter[$val] ]]
                                ]
                                        
                            ]];
                            }elseif(is_array($sim_filter[$val]) && !empty($sim_filter[$val])){
                                
                                $con[] = ['bool' => [
                                    "should" =>  [
                                        ['bool' =>[
                                    'filter' => [
                                        'terms' => [
                                            $val => $sim_filter[$val]
                                        ]
                                    ],
                                ]],
                                        ['bool' =>[
                                            'filter' => [
                                                'terms' => [
                                                    'category3_id' => $sim_filter[$val]
                                                ]
                                            ],
                                        ]]
                                        
                                        ]]];
                            }
                            
                            break;
                        default:
                            
                            if(is_numeric($sim_filter[$val])){
                                $con[] = ['match' => [$val => $sim_filter[$val]]];
                            }elseif(is_array($sim_filter[$val]) && !empty($sim_filter[$val])){
                                
                                $con[] = ['bool' =>[
                                    'filter' => [
                                        'terms' => [
                                            $val => $sim_filter[$val]
                                        ]
                                    ],
                                ]];
                            }
                            break;
                    }
                    
                    
                    
                    
                }
            }
            
            $min_score = isset($sim_filter['min_score']) ? $sim_filter['min_score']  : 0;
            $max_score = isset($sim_filter['max_score']) ? $sim_filter['max_score']  : 0;
            
            if($min_score + $max_score > 0){
                 
                if($max_score < $min_score){
                    $max_score = 99;
                }
                
                $con[] = ['range' => ['score' => [
                    'gte'   =>  (int)$min_score,
                    'lte'   =>  (int)$max_score
                ]
                ]
                ];
            }
            
            ///
            $min_key = isset($sim_filter['min_key']) ? $sim_filter['min_key']  : 0;
            $max_key = isset($sim_filter['max_key']) ? $sim_filter['max_key']  : 0;
            
            if($min_score + $max_score > 0){
                
                if($max_score < $min_score){
                    $max_score = 10;
                }
                
                $con[] = ['range' => ['number_of_key' => [
                    'gte'   =>  (int)$min_key,
                    'lte'   =>  (int)$max_key
                ]
                ]
                ];
            }
            
            //
            if(isset($sim_filter['regex']) && ($regex = $sim_filter['regex']) != ""){
            
            $val = explode('|', $regex);
            
            
            switch ($val[0]){
                
                case 'price':
                    $val1 = max($min_price, isset($val[1]) && is_numeric($val[1]) ? $val[1] : 0);
                    $val2 = max($max_price, isset($val[2]) && is_numeric($val[2]) ? $val[2] : 0);
                    
                    if($val2 > $val1-1){
                         
                        
                        $con[] = ['range' => [$price_field => [
                            'gte'   =>  (float)$val1,
                            'lte'   =>  (float)$val2
                        ]
                        ]
                        ];
                        
                        $validate_min_price = true;
                    }
                    break;
                    
                default:
                    
                    
                    
                    $con[] = ['regexp' => ['id' => [
                        'value' => trim($sim_filter['regex'],'^$'),
                        "flags" => "ALL","max_determinized_states" => 10000,
                        "rewrite" => "constant_score"
                    ]]];
                    
                    
                    break;
            }
            
            
        }
            
        }
        
        
        if(isset($params['sosim']) && ($sosim = trim($params['sosim'])) != ""){
            $sosim = str_replace(['o', 'O'], '0', $sosim);
            $sosim = ltrim($sosim, '0^ ');
            $sosim = rtrim($sosim, '$ ');
            
            $a = isset($params['a']) ? $params['a'] : rand(0,9);
            
            $b = isset($params['b']) ? $params['b'] : rand(0,9);
            
            $c = isset($params['c']) ? $params['c'] : rand(0,9);
            $d = isset($params['d']) ? $params['d'] : rand(0,9);
            $e = isset($params['e']) ? $params['e'] : rand(0,9);
            $f = isset($params['f']) ? $params['f'] : rand(0,9);
            
            $x = isset($params['x']) ? $params['x'] : rand(0,9);
            $y = isset($params['y']) ? $params['y'] : rand(0,9);
            
            
            $rg = [
                'o' =>'0',
                'O' => '0',
                '.' => '',
                '*' => '.*',
                '_' => '[0-9]',
                '?' => '[0-9]',
                'a' => $a,
                'b' => $b,
                'c' => $c,
                'd' => $d,
                'e' => $e,
                'f' => $f,
                'x' => $x,
                'y' => $y
            ];
            
            $sosim = str_replace(array_keys($rg),array_values($rg), $sosim);
            
            
            if(is_numeric($sosim)){
                if(strlen($sosim) < 9){
                    $sosim = ".*$sosim.*";
                }elseif(strlen($sosim) > 9){
                    $sosim = substr($sosim, 0, 9);
                }
            }
            
            
            if(strlen($sosim) == 9 && !empty(preg_match('/[1-9]\d{8}/', $sosim))){
                $con[] = ['match' => ['id' => $sosim]];
            }else{
                
              
                
                if(preg_match('/\d+/', $sosim, $m) && $m[0] == $sosim){
                    
                    $con[] = ['regexp' => ['id' => [
                        'value' => "$sosim",
                        "flags" => "ALL","max_determinized_states" => 10000,
                        "rewrite" => "constant_score"
                    ]]];
                    
                }else{ 
                    $con[] = ['regexp' => ['id' => [
                        'value' => "$sosim",
                        "flags" => "ALL","max_determinized_states" => 10000,
                        "rewrite" => "constant_score"
                    ]]];
                }
            }
            
            
        }
        
        if(isset($params['regex']) && $params['regex'] != ""){           
            
            $con[] = ['regexp' => ['id' => [
                'value' =>  trim($params['regex'],'^$'),
                "flags" => "ALL","max_determinized_states" => 10000,
                "rewrite" => "constant_score"
            ]]];
        }
        
        
        if(isset($params['explode']) && !empty($params['explode'])){
            
            $a = [0,1,2,3,4,5,6,7,8,9];
            
            $n49 = $n53 =false;
            
            foreach($params['explode'] as $k=>$n){
                if($n==49){
                    $n49 = true;
                    unset($params['explode'][$k]);
                }
                
                if($n==53){
                    $n53 = true;
                    unset($params['explode'][$k]);
                }
            }
            $array_diff = array_diff($a, $params['explode']);
                        
            
            $con[] = ['regexp' => ['id' => [
                'value' =>  "[".implode('', $array_diff)."]+",
                "flags" => "ALL","max_determinized_states" => 10000,
                "rewrite" => "constant_score"
            ]]];
            
            if($n49 == true)
            {
                
                //$query->andWhere(['not in', 'id',  ['REGEX' ,'id',   "/^.*49.*/"]]);
            }
            
            if($n53 == true)
            {
                
                // 				$query->andWhere(['$not', 'id', new \MongoDB\BSON\Regex('/53/')]);
            }
            
        }
        
        
        if(isset($params['sim_filter']['list_id'])){
            $params['list_id'] = $params['sim_filter']['list_id'];
        }
        
        if(isset($params['list_id'])){
            
            if(is_numeric($params['list_id']) && $params['list_id'] > 0){
                $lists = [$params['list_id']];
            }elseif(is_array($params['list_id']) && !empty($params['list_id'])){
                $lists = $params['list_id'];
            }else{
                $lists = [];
            }
            
            if(!empty($lists)){
                
                $l1 = [];
                
                foreach ($lists as $list_id){
                    $list = Yii::$app->sim->list->getItem($list_id);
                    if(isset($list['data']) && !empty($list['data'])){
                        $l1 += $list['data'];
                    }
                }
                
                
                if(!empty($l1)){
                      
                    $con[] = ['bool' =>[
                        'filter' => [
                            'terms' => [
                                "id" => $l1,
                                "boost" => 1.0
                            ]
                        ],
                    ]];
                }
            }
            
            
            
        }
        
        if(!$validate_min_price){
            if($min_price>0 && $max_price>0){
                 
            }elseif ($min_price > 0){
                $max_price = 100000000000;
                 
            }elseif ($max_price > 0){
                $min_price = 0;
            }
             
            
            if($min_price + $max_price > 0){
                $con[] = ['range' => [$price_field => [
                'gte'   => (float) $min_price,
                    'lte'   =>  (float) $max_price
                        ]
                    ]
                ];
            }
            
            
        }
         
        
        if(!empty($con)){
            $body['query']['bool']['must'] = $con;
        }
        
        //$body['from']   =   0;
//         $body['size']   =   30;

//         $body['settings'] = ['index' => ['max_result_window'=>1000000]];

        $sort = $this->buildSortParams($params);
       
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            //'body' => $body,
//             'settings'=>['index' => ['max_result_window'=>1000000]]
        ];
        
//         $this->client->settings(['index' => ['max_result_window'=>1000000]]);
        
        
        
        if($count){
            $c = $this->client->count($params);
            
            if(isset($params['count_only']) && $params['count_only'] === true){
                return $c['count'];
            }
            
            $total_records = $c['count'];

            if($offset + $limit < 10000){
                $params['from'] = $offset;
            }else{
                $params['scroll'] = '30s';
            }

            
            $params['size'] = $limit;
            
             
            if(!empty($sort)){
                $params['body']['sort'] = $sort;
            
            }
            if($offset + $limit < 10000){
                 
            }else{
                $response = $this->client->search($params);
                 
                
//                 $response2 = $this->client->scroll([
//                     "scroll_id" => $response['_scroll_id'],  //...using our previously obtained _scroll_id
//                     "scroll" => "30s"           // and the same timeout window
//                 ]
//                 );
                
//                 view($response, 'R2');
                
                // Now we loop until the scroll "cursors" are exhausted
//                 while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
                    
//                     // **
//                     // Do your work here, on the $response['hits']['hits'] array
//                     // **
                    
//                     // When done, get the new scroll_id
//                     // You must always refresh your _scroll_id!  It can change sometimes
//                     $scroll_id = $response['_scroll_id'];
                    
//                     // Execute a Scroll request and repeat
//                     $response = $this->client->scroll([
//                         "scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
//                         "scroll" => "30s"           // and the same timeout window
//                     ]
//                         );
//                 }
                
                return [
                    'total_records'=>$total_records,
                    'total_items'=>$total_records,
                    'total_pages'=>ceil($total_records/$limit),
                    'scroll_id'=>isset($response['_scroll_id']) ? $response['_scroll_id'] : '',
                    'offset'=>$offset,
                    'limit'=>$limit,
                    'p'=>$p,
                    'list_items' => $this->populateData($response),
                    
                ];
                
            }
            
            $response = $this->client->search($params);
            
            
            return [
                'total_records'=>$total_records,
                'total_items'=>$total_records,
                'total_pages'=>ceil($total_records/$limit),
                'offset'=>$offset,
                'limit'=>$limit,
                'scroll_id'=>isset($response['_scroll_id']) ? $response['_scroll_id'] : '',
                'p'=>$p,
                'list_items' => $this->populateData($response),
                
            ];
        }
        
        
        $params['body']['from'] = $offset;
        $params['body']['size'] = $limit;
        $results = $this->client->search($params);                
        
        
        
        return $this->populateData($results);
    }
    
    
    public function enableFielddata()
    {
        // Set the index and type
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'include_type_name' => true  ,
            'body' => [
                $this->_type => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'text',
//                             'analyzer' => 'standard'
                        ]
                    ]
                ]
            ]
        ];
        
        // Update the index mapping
        $this->client->indices()->putMapping($params);
    }
    
    
    public function updateSingleSimData($simId, $partner_id, $data, $params = [])
    {
        $simId = Yii::$app->sim->getSimId($simId);
        
        $partner_id = (int) $partner_id;
        
        if(!Yii::$app->sim->validateSimData($data)){
            $data = array_merge(Yii::$app->sim->getSiminfo($simId),$data);
        }
        
        $s = [] ; // $this->findSim($simId, $partner_id);
        
        $items = $this->getDocument($simId);
        
        view($items,'Items');
        
        $db = 0;
        if(isset($items['hits']['hits']) && !empty($items['hits']['hits'])){
            foreach ($items['hits']['hits'] as $sim){
                if($sim['_source']['partner_id'] == $partner_id){
                    $s = $sim;
                }else{
                    $db ++;
                }
            }
        }
        
        $data['duplicate'] = $db;
        
        if(isset($data['_id'])){
            unset($data['_id']);
        }
        
        
        if(isset($data['price'])){
            $data['price'] = (float)$data['price'];
        }
        
        if(isset($data['price2'])){
            $data['price2'] = (float)$data['price2'];
        }
        
        if(isset($data['partner_id'])){
            $data['partner_id'] = (int)$data['partner_id'];
        }
        if(isset($data['type_id'])){
            $data['type_id'] = (int)$data['type_id'];
        }
        
        
        if(isset($data['price']) && $data['price'] < 1){
            unset($data['price']);
            if(isset($data['price2'])){
                unset($data['price2']);
            }
        }
        
        if(isset($data['price2']) && $data['price2'] < 1){
            unset($data['price2']);
        }             
        
        if(isset($data['display'])){
            $data['display'] = trim(trim_space($data['display']), '.,');
            
            if(substr_count($data['display'], '.') == 0){
                $sp = Yii::$app->sim->splitNumber($data['display'], $data);
                if(!empty($sp)){
                    $data['display'] = $sp[0];
                }
            }
        }
         
        
        if(!empty($s)){
            
            $sim = $s['_source'];
            
            $data['updated_at'] = time();
            
            // Kiểm tra có thay đổi giá thu
            if(isset($data['price']) && $data['price'] != $sim['price'] && $sim['price'] > 0){
                //$s = Yii::$app->frontend->simonline->getItem($sim['_id']);
                $history = isset($sim['history']) && !is_array($sim['history']) ? json_decode($s['history'], 1) : (isset($sim['history']) ? $sim['history'] : []);
                
                if(isset($history['price']) && !empty($history['price'])){
                    
                    //                     $hs = date("d-m-Y H:i: " . number_format($data['price']));
                    
                    $px = $data['price'] - $sim['price']; // Lấy giá mới - giá cũ
                    
                    //                     $hs .= " | " . ($px > 0 ? '+' : '-') . number_format($px);
                    
                    // ↑ ↓
                    $pc = (abs($px) / $sim['price']) * 100;
                    
                    //                     $hs .= " | " . ($px > 0 ? '↑' : '↓') . number_format(round($pc,2)) . '%';
                    
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  $data['price'],
                        'last_price' => $sim['price'],
                    ];
                    
                    $history['price'][] = $pre;
                    
                    $data['exchange_price'] = round($px / $sim['price'] * 100 , 2);
                    
                }else{
                    
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  $data['price'],
                        'last_price' => $data['price'],
                    ];
                    
                    $history['price'] = [$pre];
                    $data['exchange_price'] = 0;
                }
                
                
                $data['history'] = ($history);
            }else{
                $data['exchange_price'] = 0;
            }
            
            $params = [
                'index' => $this->_index,
                'type' => $this->_type,
                'id' => $s['_id'],
                'body' => ['doc' => $data]
            ];
             
            
            $response = $this->client->update($params); 
            
            return $response;
            
        }else{
            
             
            
            $aa = [
                'price',
                'price1',
                'price2',
                'partner_id',
                'score',
                'number_of_key',
                'network_id',
                'category_id',
                'category2_id',
                'category3_id',
                'type_id',
                'fixed_price',
                'istm',
                'duplicate',
                'exchange_price',
                'nguhanh',
                'nut'
            ];
            
            
            foreach ($aa as $field){
                $data[$field] = isset($data[$field]) ? (int) $data[$field] : 0;
            }
            
            $data['partner_id'] = $partner_id;
            $data['created_time'] = time();
            $data['updated_at'] = time();

            if(!isset($data['status'])){
                $data['status'] = -1;
            }
             
            $params = [
                'body' => [
                    [ 'index' => ['_index' => $this->_index,  '_type' => $this->_type, ] ],
                    $data,                   
                ]
            ];
            
            $responses = $this->client->bulk($params);
            return $responses;
        }
    }
    
    
    
    private function buildSortParams($params)
    {
        $sort_array = [];
        
        if(isset($params['sort']) && ($sorts = $params['sort']) != "")
        {
            if(!is_array($sorts)){
                $sorts = array($sorts);
            }else{
                
            }
            
            foreach($sorts as $sort){
                
                
                
                
                switch ($sort){
                    case 1: // price inc
                        
                        $field = 'price2';
                        
                        if(!Yii::$app->collaborator->isGuest){
                            //$field = 'price';
                        }
                        
                        $sort_array[$field] = ["order" => "asc"] ;
                        /**
                         * // [ "updated_at" => ["order" => "desc"]],
                [ "price" => ["order" => "asc"]],
                         */
                        break;
                    case 2: // price inc
                        
                        $field = 'price2';
                        
                        if(!Yii::$app->collaborator->isGuest){
                            //$field = 'price';
                        }
                        $sort_array[$field] = ["order" => "desc"] ;
                        break;
                        
                    case 21: // price inc
                        
                        $field = 'price';
                        
                        $sort_array[$field] = ["order" => "asc"];
                        break;
                    case 22: // price inc
                        
                        $field = 'price';
                        $sort_array[$field] = ["order" => "desc"];
                        break;
                        
                        
                    case 3: // price inc
                        $sort_array['network_id'] = ["order" => "asc"];
                        break;
                    case 4: // price inc
                        $sort_array['network_id'] = ["order" => "desc"];
                        break;
                        
                    case 5: // price inc
                        $sort_array['id'] = ["order" => "asc"];
                        break;
                    case 6: // price inc
                        $sort_array['id'] = ["order" => "desc"];
                        break;
                    case 7: // price inc
                        $sort_array['category_id'] = ["order" => "asc"];
                        break;
                    case 8: // price inc
                        $sort_array['category_id'] = ["order" => "desc"];
                        break;
                        
                    case 9: // price inc
                        $sort_array['partner_id'] = ["order" => "asc"];
                        break;
                    case 10: // price inc
                        $sort_array['partner_id'] = ["order" => "desc"];
                        break;
                        
                    case 11: // updated_at inc
                        $sort_array ['updated_at'] = ["order" => "asc"];
                        break;
                    case 12: // updated_at desc
                        
                        $sort_array['updated_at'] = ["order" => "desc"];
                        break;
                        
                        
                    case 13: // updated_at inc
                        $sort_array['score'] = ["order" => "asc"];
                        break;
                    case 14: // updated_at desc
                        
                        $sort_array['score'] = ["order" => "desc"];
                        break;
                        
                        
                    case 15: // updated_at inc
                        $sort_array['number_of_key'] = ["order" => "asc"];
                        break;
                    case 16: // updated_at desc
                        
                        $sort_array['number_of_key'] = ["order" => "desc"];
                        break;
                    case 17: // updated_at inc
                        $sort_array['type_id'] = ["order" => "asc"];
                        break;
                    case 18: // updated_at desc
                        
                        $sort_array['type_id'] = ["order" => "desc"];
                        break;
                        
                    case 31: // updated_at inc
                        $sort_array['exchange_price'] = ["order" => "asc"];
                        break;
                    case 32: // updated_at desc
                        
                        $sort_array['exchange_price'] = ["order" => "desc"];
                        break;
                        
                        
                        //                 case 100: // updated_at desc
                        
                        //                     $sort_array = new \yii\db\Expression('rand()');
                        
                        //                     break;
                        
                }
            }
        }
        
        return $sort_array;
    }
    
     
    
}