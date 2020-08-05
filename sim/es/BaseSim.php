<?php 
namespace izi\sim\es;
use Yii;
class BaseSim extends \yii\base\Component
{
    public $client;
    private $_index;
    private $_type;
    
    public function init()
    {
         
        //Tạo đối tượng Client
        $this->client = \Elasticsearch\ClientBuilder::create()
        ->setHosts($this->host())
        ->build();
        
        
        
    }
    
    private function host()
    {
        return  [
            [
                'host' => 'localhost',          //yourdomain.com
                'port' => '9200',
                'scheme' => 'http',             //https
                //        'path' => '/elastic',
                //        'user' => 'username',         //nếu ES cần user/pass
                //        'pass' => 'password!#$?*abc'
            ],
            
        ];
    }
    
    public function putSetting($settings)
    {
        $params2 = [
            'index' => $this->_index,
            'body' => [
                'settings' => $settings
            ]
        ];
        
        return $this->client->indices()->putSettings($params2);
        
    }
    
    public function setIndex($value)
    {
        $this->_index = $value;
    }
    
    public function setType($value)
    {
        $this->_type = $value;
    }
    
    private $_indexProperties;
    
    public function setIndexProperties($value)
    {
        $this->_indexProperties = $value;
    }
    
    
    public function getIndexProperties()
    {
        $params = [
            
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'filter' => [
                            'shingle' => [
                                'type' => 'shingle'
                            ]
                        ],
                        'char_filter' => [
                            'pre_negs' => [
                                'type' => 'pattern_replace',
                                'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                                'replacement' => '~$1 $2'
                            ],
                            'post_negs' => [
                                'type' => 'pattern_replace',
                                'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                                'replacement' => '$1 ~$2'
                            ]
                        ],
                        'analyzer' => [
                            'reuters' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'stop', 'kstem']
                            ]
                        ]
                    ]
                ],
                
            ]
        ];
        
        return $params;
    }
    
    
    public function createIndex($index = null)
    {
        
        if($index == null){
            $index = $this->_index;
        }
        
        $params = array_merge(['index' => $index] , $this->getIndexProperties());
         
        
        //Kiểm tra xem Index đã tồn tại không
        $indexExist = $this->client->indices()->exists(['index' => $index]);
        
         
        
        if (!$indexExist) {
            try {
                //Thực hiện tạo Index
                $this->client->indices()->create($params);
                
                
            }
            catch (\Exception $e) {
                //Lỗi tạo Index
                $res = json_decode($e->getMessage());
                echo $res->error->reason;
            }
        }
        else {
//             echo "Index {$params['index']} đã có rồi!";

            return false;
        }
    }
    
    
    public function existedIndex()
    {
        $params = [
            'index' => $this->_index
        ];
        
        //Kiểm tra xem Index đã tồn tại không
        return $this->client->indices()->exists($params);
    }
    
    public function reindex($source, $dest)
    {
        /**
         * KT source
         */
        $params = [
            'index' => $source
        ];

        //Kiểm tra xem Index nguồn có tồn tại không
        $indexExist = $this->client->indices()->exists($params);

        if ($indexExist) {
 
        }else {
            echo "Index {$params['index']} không có";
            return false;
        }
        
        
        /**
         * KT dest
         */
        $params = [
            'index' => $dest
        ];
        
        //Kiểm tra xem Index đích có tồn tại không, nếu có thì xóa đi
        $indexExist = $this->client->indices()->exists($params);
        
        
        
        if ($indexExist) {
            $this->client->indices()->delete($params);
            
        }else {
           
        }
        
        // Tạo lại index nguồn
        $this->_index = $dest;
        $this->createIndex($dest);
        $this->setMapping($dest);
        
    
        // Reindex
        $params = [
            'body' => [
                'source' => [
                    'index'  => $dest,
                ],
                'dest' => [
                    'index' => $dest
                ]
            ]
        ];
        return $this->client->reindex($params);
        
        
        
    }
    
    
    public function removeIndex($confirm)
    {
        if(!($confirm === true)) return;
        
        $params = [
            'index' => $this->_index
        ];

        //Kiểm tra xem Index đã tồn tại không
        $indexExist = $this->client->indices()->exists($params);
 
        if ($indexExist) {
            return $this->client->indices()->delete($params);
            echo "Đã xóa";
        }
        else {
            echo "Index {$params['index']} không có";
        }
    }
    
    public function getDocuments()
    {
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            
        ];
        
        return $this->client->search($params);
    }
    
    public function getDocument($id)
    {
        if(!$this->existedIndex()) return;
        
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'id' => $id, 
        ];
        try {
            $response = $this->client->get($params);
        } catch (\Exception $e) {
            if ($e->getCode() == '404' && json_decode($e->getMessage(), true)) {
                $response = json_decode($e->getMessage(), true);
            } elseif ($e->getCode() == '400') {
                return array();
            } else {
                throw $e; 
            }
        }
        
        if ($response['found']) {
            $result = array('id' => $response['_id']) + $response['_source'];
            return array($result);
        }
        return array();
    }
    
    
    public function buildSortParams($params)
    {
        $sorts = isset($params['sort']) ? $params['sort'] : $params;
        if(!is_array($sorts)){
                $sorts = array($sorts);
        }
        
//         view($sorts);
        
        $sort_array = [];
//         $sort_array['network_id'] = ["order" => "desc"];
//        $sort_array['s3'] = ["order" => "desc"];
//        $sort_array['dauso'] = ["order" => "desc"];
        
        if(!empty($sorts))
        {
            
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
                        
                        
                    case 3: // mạng inc
                        $sort_array['network_id'] = ["order" => "asc"];
                        break;
                    case 4: // mạng inc
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
                        
                        
                    case 41: // dau so
                        $sort_array['dauso'] = ["order" => "asc"];
                        break;
                    case 42: // dau so
                        
                        $sort_array['dauso'] = ["order" => "desc"];
                        break;
                        
                    case 43: // 6 so cuối
                        $sort_array['s6'] = ["order" => "asc"];
                        break;
                    case 44: // 6 so cuối
                        
                        $sort_array['s6'] = ["order" => "desc"];
                        break;
                    case 45: // 4 so cuối
                        $sort_array += ['s4'=> ["order" => "asc"]];
                        break;
                    case 46: // 4 so cuối
                        
                        $sort_array['s4'] = ["order" => "desc"];
                        break;
                        
                    case 47: // 3 so cuối
                        $sort_array['s3'] = ["order" => "asc"];
                        break;
                    case 48: // 6 so cuối
                        
                        $sort_array['s3'] = ["order" => "desc"];
                        break;
                    case 49: // 2 so cuối
                        $sort_array['s2'] = ["order" => "asc"];
                        break;
                    case 50: // 2 so cuối
                        
                        $sort_array['s2'] = ["order" => "desc"];
                        break;
                        
                    case 51: // 2 so cuối
                        $sort_array['s5'] = ["order" => "asc"];
                        break;
                    case 52: // 2 so cuối
                        
                        $sort_array['s5'] = ["order" => "desc"];
                        break;
                        
                    case 100: // updated_at desc
    
                       //$sort_array['id'] = ["order" => "RANDOM"];
    
                        break;
    
                }
            }
        }
        
        
        
        return $sort_array;
    }
    
    
    public function countItems($params){
        
        $params['count'] = true;
        $params['count_only'] = true;
        
        return $this->getItems($params);
    }
    
    
    /**
     * Lấy danh sách sim
     * @param array $params condition
     * @return array
     */
    
    
    public function getItems($params)
    {
        if(!$this->existedIndex()) return [];
        
        $n49 = $n53 = false;
        
        if(isset($params['explode']) && !empty($params['explode'])){
            
            if(in_array('49', $params['explode'])){
                $n49 = true;
            }
            
            if(in_array('53', $params['explode'])){
                $n53 = true;
            }
            
        }
        
        $limit = (int) (isset($params['limit']) ? $params['limit'] : 30);
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $p = isset($params['p']) && $params['p'] > 1 ? $params['p'] : 1;
        
        $offset = ($p - 1) * $limit;
        
        $count = isset($params['count']) && $params['count'] === true ? true : false;
        $count_only = isset($params['count_only']) && $params['count_only'] === true ? true : false;
        
        
        $sort = $this->buildSortParams($params);
        
//         view($sort);
        
        
        $params = $this->buildConditions($params);
        
//        view($params);
        //  
         
        if($count){
            $c = $this->client->count($params);
            $total_records = $c['count'];
            
            if($count_only){
                return $total_records;
            }
            
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
                
                return [
                    'total_records'=>$total_records,
                    'total_items'=>$total_records,
                    'total_pages'=>ceil($total_records/$limit),
                    'scroll_id'=>isset($response['_scroll_id']) ? $response['_scroll_id'] : '',
                    'offset'=>$offset,
                    'limit'=>$limit,
                    'p'=>$p,
                    'list_items' => $this->populateData($response, ['n49' => $n49, 'n53' => $n53]),
                    
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
                'list_items' => $this->populateData($response, ['n49' => $n49, 'n53' => $n53]),
                
            ];
        }
        
        
        if(!empty($sort)){
            $params['body']['sort'] = $sort;
            
        }
        
        $params['body']['from'] = $offset;
        $params['body']['size'] = $limit;
        $results = $this->client->search($params);
        
        
        
        return $this->populateData($results, ['n49' => $n49, 'n53' => $n53]);
    }
    
    
    /**
     * Lấy danh sách chi tiết 1 số sim     
     * Trả về thông tin sim kèm danh sách đại lý đăng sim
     */
    
    public function getSimDetail($simId, $partner_id = 0)
    {
        $simId = Yii::$app->sim->getSimId($simId);
        
        if(!$this->existedIndex()) return [];
        
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
        
        
        $params['body']['sort'] = [
            'is_invisible' => ["order" => "asc"],
            'price' => ["order" => "asc"],
        ];
         
//         view($params);
        
        $results = $this->client->search($params);
        
//         view($results);
        
        return $this->populateData($results);
    }
    
    public function getItem($simId, $partner_id) {
        $info = $this->findSim($simId, $partner_id);
        
//         view($info);
        
        if(!empty($info)){
            return $info[0];
        }
        return [];
    }
    
    public function findSim($simId, $partner_id = 0)
    {
        $simId = Yii::$app->sim->getSimId($simId);
        
        if(!$this->existedIndex()) return [];
        
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
           // 'refresh' => true, 
            'body' => [
                
                'query' => [
                    'bool' => [
                    //                         'filter' => [
                        //                                 'term' => [ 'id' => $simId]
                        //                         ],
                        'must' => $con
                    ]
                ],
                'sort' => [
                   // 'is_sold' => ['order' => 'asc'],
                    'is_invisible' => ['order' => 'asc'],
                    'price' => ['order' => 'asc'],
                ],
            ]
        ];
        
        
//         $params2 = [
//             'index' => $this->_index,
//             'body' => [
//                 'settings' => [
//                     'number_of_replicas' => 0,
//                     'refresh_interval' => -1,
                     
                    
//                 ]
//             ]
//         ];
        
//         $response = $this->client->indices()->putSettings($params2); 
        
//         view($response);
      
        
        $results = $this->client->search($params);
        
        return $this->populateData($results);
    }
    
    public function deleteSim($simId, $partner_id = 0)
    {
        $items = $this->getDocumentsBySimId($simId, (int)$partner_id);
        
        if(isset($items['hits']['hits']) && !empty($items['hits']['hits'])){
            foreach ($items['hits']['hits'] as $sim){
                $params = [
                    'index' => $this->_index,
                    'type'  => $this->_type,
                    'id'    => $sim['_id']
                ];
                
                $this->client->delete($params);
                
            }
        }
        
    }
    
    
    public function deleteMultipleSim($params)
    {
        
        $p = $this->buildConditions($params);
        
        $this->client->deleteByQuery($p);
        
//         $items = $this->getDocumentsBySimId($simId, (int)$partner_id);
        
//         if(isset($items['hits']['hits']) && !empty($items['hits']['hits'])){
//             foreach ($items['hits']['hits'] as $sim){
//                 $params = [
//                     'index' => $this->_index,
//                     'type'  => $this->_type,
//                     'id'    => $sim['_id']
//                 ];
                
//                 $this->client->delete($params);
                
//             }
//         }
        
    }
    
    
    public function getDocumentsBySimId($simId, $partner_id = 0)
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
        ];
        
        return $this->client->search($params);
    }
    
    private $_cus;
    
    
    public function updateSimValue($simId, $field, $value, $params = [])
    {
        if(in_array($field, ['id', '_id'])){
            return;
        }
        
        
        $items = $this->getDocumentsBySimId($simId);

        if(isset($items['hits']['hits']) && !empty($items['hits']['hits'])){
            foreach ($items['hits']['hits'] as $sim){
                
                if(isset($params['partner_id']) && $params['partner_id'] > 0){
                    
                    if($params['partner_id'] == $sim['_source']['partner_id']){
                        $params = [
                            'index' => $this->_index,
                            'type' => $this->_type,
                            'id' => $sim['_id'],
                            'body' => ['doc' => [$field => is_numeric($value) ? (int) $value : $value]]
                        ];
                        
                        $this->client->update($params);
                        
                        return;
                        break;
                    }
                    
                }else{
                
                    $params = [
                        'index' => $this->_index,
                        'type' => $this->_type,
                        'id' => $sim['_id'],
                        'body' => ['doc' => [$field => is_numeric($value) ? (int) $value : $value]]
                    ];
                    
                    $this->client->update($params);
                }
                   
            }
        }
    }
    
    
    
    public function addPackageToSim($simId, $data, $params = [])
    {
        $field = isset($params['field']) ? $params['field'] : null;
        
        if($field == null) return false;
        
        
        $items = $this->getDocumentsBySimId($simId);

        if(isset($items['hits']['hits']) && !empty($items['hits']['hits']) && !empty($data)){
            foreach ($items['hits']['hits'] as $sim){
                
                if(!(isset($params['remove_old']) && $params['remove_old'])){
                
                    if(isset(['attrs'][$field]) && !empty($sim['attrs'][$field])){
                        if(is_array($data)){
                            $data = array_merge($sim['attrs'][$field], $data);
                        }
                    }else{
                        
                    }
                }
 
                $params = [
                    'index' => $this->_index,
                    'type' => $this->_type,
                    'id' => $sim['_id'],
                    'body' => ['doc' => ['attrs' => [$field =>$data]]]
                ];
                
                $this->client->update($params);
                
                   
            }
        }
    }
    
    
    /**
     * 
     * @param unknown $simId
     * @param unknown $partner_id
     * @param unknown $data
     * @param array $params
     * @return void|callable|array
     */
    
    private $_updated_sim = [];
    
    public function updateSingleSimData($simId, $partner_id, $data, $params = [])
    {
        
        
        
        if(in_array("$simId-$partner_id", $this->_updated_sim)){
            return false; 
        }else{
            $this->_updated_sim[] = "$simId-$partner_id";
        }
         
        $simId = Yii::$app->sim->getSimId($simId);
        
        $rewrite_duplicate = isset($params['rewrite_duplicate']) && !$params['rewrite_duplicate'] ? false : true;
        
        $partner_id = (int) $partner_id;
          
        if($partner_id > 0){
            $cus = isset($this->_cus[$partner_id]) ? $this->_cus[$partner_id] : ($this->_cus[$partner_id] = Yii::$app->customer->model->getItem($partner_id));

            if(!empty($cus)){
//                 $partner_label = $cus['code'];  
            }else{
                
                return;
            }
        }else{
            
            
            return;
        }
        
      
        
        if(!Yii::$app->sim->validateSimData($data)){
            $data = array_merge(Yii::$app->sim->getSiminfo($simId),$data);
        }
         
        
        $s = [] ;
        
        $items = $this->getDocumentsBySimId($simId);
        
         
        
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
        
        if(isset($data['price0'])){
            $data['price0'] = (float)$data['price0'];
        }
        
        if(isset($data['price1'])){
            $data['price1'] = (float)$data['price1'];
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
//             if(isset($data['price2'])){
//                 unset($data['price2']);
//             }
        }
        
        if(isset($data['price0']) && $data['price0'] < 1){
            unset($data['price0']);
        }
        
        if(isset($data['price1']) && $data['price1'] < 1){
            unset($data['price1']);
        }
        
        if(isset($data['price2']) && $data['price2'] < 1){
            unset($data['price2']);
        }
        
        if(!isset($data['price0']) && isset($data['price0']) && $data['price0'] > 1){
            $data['price0'] = $data['price1'];
        }
        
//         if(!isset($data['is_invisible'])) $data['is_invisible'] = 0;
        
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
            
            if(!isset($sim['is_sold']) && !isset($data['is_sold']))
            {
                $data['is_sold'] = 0;
            }
            
            if(!isset($sim['is_invisible']) && !isset($data['is_invisible']))
            {
                $data['is_invisible'] = 0;
            }
            
            if(!isset($sim['p_invisible']) && !isset($data['p_invisible']))
            {
                $data['p_invisible'] = 0;
            }
             
            
            $data['updated_at'] = time();
            
            // Kiểm tra có thay đổi giá thu
            
            $history = isset($sim['history']) && !is_array($sim['history']) ? json_decode($sim['history'], 1) : (isset($sim['history']) ? $sim['history'] : []);
            
            if(isset($data['price']) && ($data['price'] != $sim['price']  || (isset($data['price2']) && $data['price2'] != $sim['price2']))){
                                
                if(isset($history['price']) && !empty($history['price'])){
                    
                    //                     $hs = date("d-m-Y H:i: " . number_format($data['price']));
                    
                    $px = $data['price'] - $sim['price']; // Lấy giá mới - giá cũ
                    
                    //                     $hs .= " | " . ($px > 0 ? '+' : '-') . number_format($px);
                    
                    // ↑ ↓
                    //$pc = (abs($px) / $sim['price']) * 100;
                    
                    //                     $hs .= " | " . ($px > 0 ? '↑' : '↓') . number_format(round($pc,2)) . '%';
                    
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  $data['price'],
                        'last_price' => $sim['price'],
                        'price2' =>  isset($data['price2']) ? $data['price2'] : $sim['price2'],
                        'last_price2' => $sim['price2'],
                        'partner_id' => !Yii::$app->member->isGuest ? Yii::$app->member->id : 0,
                        'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
                        'url'   =>  FULL_URL,
                    ];
                    
                    $history['price'][] = $pre;
                    
                    $data['exchange_price'] = $sim['price'] > 0 ? min( round($px / $sim['price'] * 100 , 2), 100000) : 0 ;
                    
                    
                    
                }else{
                    
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  $data['price'],
                        'last_price' => $data['price'],
                        
                        'price2' =>  isset($data['price2']) ? $data['price2'] : $sim['price2'],
                        'last_price2' => isset($data['price2']) ? $data['price2'] : $sim['price2'],
                        
                        'partner_id' => !Yii::$app->member->isGuest ? Yii::$app->member->id : 0,
                        'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
                        'url'   =>  FULL_URL,
                    ];
                    
                    $history['price'] = [$pre];
                    $data['exchange_price'] = 0;
                }
                
                
                $data['history'] = ($history);
            }else{
                $data['exchange_price'] = 0;
                
                if(!(isset($history['price']) && !empty($history['price']))){   
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  isset($data['price']) ? $data['price'] : $sim['price'],
                        'last_price' => isset($data['price']) ? $data['price'] : $sim['price'],
                        
                        'price2' =>  isset($data['price2']) ? $data['price2'] : $sim['price2'],
                        'last_price2' => isset($data['price2']) ? $data['price2'] : $sim['price2'],
                        
                        'partner_id' => !Yii::$app->member->isGuest ? Yii::$app->member->id : 0,
                        'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
                        'url'   =>  FULL_URL,
                    ];
                    
                    $data['history']['price'] = [$pre];
                }
                
            }
            
            $params = [
                'index' => $this->_index,
                'type' => $this->_type,
                'id' => $s['_id'],
                'body' => ['doc' => $data]
            ];
            
            
            
            if($rewrite_duplicate){
                $response = $this->client->update($params);    
 
                return $response;
            }
             
            
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
                'category4_id',
                'type_id',
                'fixed_price',
                'istm',
                'duplicate',
                'exchange_price',
                'nguhanh',
                'nut',
                'is_invisible',
                'p_invisible',
                'is_invisible',
                'is_sold'
            ];
            
            
            foreach ($aa as $field){
                $data[$field] = isset($data[$field]) ? (int) $data[$field] : 0;
            }
            
            $pre = [
                'time'  =>  time(),
                'price' =>  $data['price'],
                'last_price' => $data['price'],
                'partner_id' => !Yii::$app->member->isGuest ? Yii::$app->member->id : 0,
                'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
                'url'   =>  FULL_URL,
                'first' => true
            ];
             
            $data['exchange_price'] = 0;
            
            $data['history']['price'] = [$pre];
             
            
            $data['partner_id'] = $partner_id;
//             $data['partner_label'] = $partner_label;
            $data['created_time'] = time();
            $data['updated_at'] = time();
            
            if(!isset($data['status'])){
                $data['status'] = -1;
            }
            
            
            if(!($data['price'] + $data['price2'] > 0)){
                return false;
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
    
    /**
     * 
     * Cập nhật hàng loạt
     * 
     */
    
    public function bulkUpdate($params, $conditions)
    {
        $p = $this->buildConditions($conditions);                 
        
        if(!empty($p['body']) && !empty($params)){                  
            $p['body']['script']['params'] = $params;     
            foreach ($params as $k=>$v){
               $p['body']['script']['source'] = 'ctx._source.'.$k.' = params.'.$k.'';
               
               unset($params[$k]);
                          
               $this->client->updateByQuery($p);

               return $this->bulkUpdate($params, $conditions);
                              
            } 
            
        }
                
    }
    
    
    public function adjustFieldValue($params, $conditions)
    {
        $p = $this->buildConditions($conditions);
        if(!empty($p['body'])){
                      
            $method = $params['method'];
            
            switch($params['unit'])
            {
                case '%':
                    $method = 'mul';
                    
                    if($params['value_of_change'] > 0){
                        $params['value_of_change'] = (1 + $params['value_of_change']/100);
                    }else{
                        $params['value_of_change'] = (1 - $params['value_of_change']/100);
                    }
                    
                    break;
            }
            
            switch($method)
            {
                case 'mul':
                    
                    $p['body']['script'] = [
                    'source' => 'ctx._source.'.$params['field'].' *= params.count',
                    'params' => [
                    'count' => $params['value_of_change']
                    ],
                    ];
                    
                    $r = $this->client->updateByQuery($p);
                     
                    
                    break;
                    
                case 'inc':
                    
                    $p['body']['script'] = [
                    'source' => 'ctx._source.'.$params['field'].' += params.count',
                    'params' => [
                    'count' => $params['value_of_change']
                    ],
                    ];
                    
                    $r = $this->client->updateByQuery($p);
                    
                    break;
                    
                default:
                    
                    //return $this->getCollection()->update($p, [$params['field'] => $params['value_of_change']] );
                    
                    break;
            }
            
            
            
            
        }
         
    }
    
    
    public function buildConditions($params)
    {
        $limit = (int) (isset($params['limit']) ? $params['limit'] : 30);
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $p = isset($params['p']) && $params['p'] > 1 ? $params['p'] : 1;
        
        $offset = ($p - 1) * $limit;
        
        $count = isset($params['count']) && $params['count'] === true ? true : false;
        
        $price_field = isset($params['price_field']) ? $params['price_field'] : 'price2';
        
        $min_price = isset($params['min_price']) ? (float) str_replace(',', '', $params['min_price']) : 0;
        
        $max_price = isset($params['max_price']) ? (float) str_replace(',', '', $params['max_price']) : 0;
        
        $validate_min_price = false;     
        
     
        
        $sosim = isset($params['sosim']) ? $params['sosim'] : '';
        
        if($sosim != ""){
            $pattern = Yii::$app->sim->phonePattern();
            
            preg_match_all($pattern, $sosim, $match);
 
            
            if(!empty($match[0])){
                
            }else{
                preg_match_all('/\D+/i', $sosim, $txts);
                if(!empty($txts[0])){
                    foreach ($txts[0] as $txt){
                        
                        $t2 = explode('|', str_replace(['hoặc','hoac'], '|', $txt));
                        foreach ($t2 as $t3){
                            switch (unMark($t3)){
                                case 'vt': case 'viettel':
                                    if(isset($params['network_id']) && is_array($params['network_id'])){
                                        $params['network_id'][] = 1;
                                    }else{
                                        $params['network_id'] = [1];
                                    }
                                    
                                    break;
                                case 'mb': case 'mobifone': case 'mobi':
                                    if(isset($params['network_id']) && is_array($params['network_id'])){
                                        $params['network_id'][] = 3;
                                    }else{
                                        $params['network_id'] = [3];
                                    }
                                    break;
                                    
                                case 'vina': case 'vinaphone': case 'vn':
                                    if(isset($params['network_id']) && is_array($params['network_id'])){
                                        $params['network_id'][] = 2;
                                    }else{
                                        $params['network_id'] = [2];
                                    }
                                    break;
                                case 'vnm': case 'vietnamobile':
                                    if(isset($params['network_id']) && is_array($params['network_id'])){
                                        $params['network_id'][] = 4;
                                    }else{
                                        $params['network_id'] = [4];
                                    }
                                    break;
                                    
                            }
                        }
                    }
                    
 
                }
            }
        }
        $body = $con = [];
         
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
            
            if($val == 'category_id' && isset($params[$val]) && is_numeric($params[$val])){
                $sim_filter[$val] = (int) $params[$val];
                if(!($sim_filter[$val] > -1)){
                    unset($sim_filter[$val]);
                    unset($params[$val]);
                }
                continue;
            }
            
            if(isset($params[$val]) && is_numeric($params[$val]) && $params[$val] > 0){
                $sim_filter[$val] = (int) $params[$val];
            }elseif(isset($params[$val]) && is_array($params[$val])){
                $sim_filter[$val] = $params[$val];
            }
        
        }
        
        $val = 'is_sold';
        if(isset($params[$val]) && is_numeric($params[$val])){
            $sim_filter[$val] = (int) $params[$val];
            if(!($sim_filter[$val] > -1)){
                unset($sim_filter[$val]);
                unset($params[$val]);
            } 
        }
        
        $val = 'is_invisible';
        if(isset($params[$val]) && is_numeric($params[$val])){
            $sim_filter[$val] = (int) $params[$val];
            if(!($sim_filter[$val] > -1)){
                unset($sim_filter[$val]);
                unset($params[$val]);
            }
        }
        
        
        $val = 'p_invisible';
        if(isset($params[$val]) && is_numeric($params[$val])){
            $sim_filter[$val] = (int) $params[$val];
            if(!($sim_filter[$val] > -1)){
                unset($sim_filter[$val]);
                unset($params[$val]);
            }
        }
        
        
        if(!empty($sim_filter)){
            foreach ($fArrays as $val){
                if(isset($sim_filter[$val])){
                    
                    switch ($val){
                        case 'category_id':
                            
                            if(is_numeric($sim_filter[$val]) && $sim_filter[$val] > 0){
                                $con[] = ['bool' => [
                                    "should" =>  [
                                        ['match' => ['category_id' => $sim_filter[$val] ]],
                                        ['match' => ['category3_id' => $sim_filter[$val] ]]
                                    ]
                                    
                                ]];
                            }elseif(is_numeric($sim_filter[$val])){
                                $con[] = ['match' => [$val => $sim_filter[$val]]];
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
                            
                            if(is_numeric($sim_filter[$val]) && $sim_filter[$val] > 0){
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
            
            // ĐK loại trừ
            // mạng
            if(isset($params['exclude_network_id'])){
                
                if(is_numeric($params['exclude_network_id']) && $params['exclude_network_id'] > 0){
                    
                    $con[] = ['bool' => ['must_not' => [
                            ['match' => ['network_id' => $params['exclude_network_id']]],                                            
                    ]]];
                    
                }elseif(is_array($params['exclude_network_id']) && !empty($params['exclude_network_id'])){                    
                    
                    $con[] = ['bool' => ['must_not'=>['bool' =>[
                        'filter' => [
                            'terms' => [
                                'network_id' => $params['exclude_network_id']
                            ]
                        ],
                    ]]]];
                }
            }
            
            // danh mục
            if(isset($params['exclude_category_id'])){
                
                if(is_numeric($params['exclude_category_id']) && $params['exclude_category_id'] > 0){
                    
                    $con[] = ['bool' => ['must_not' => ['bool' => [
                        "should" =>  [
                            ['match' => ['category_id' => $params['exclude_category_id'] ]],
                            ['match' => ['category3_id' => $params['exclude_category_id']]]
                        ]
                        
                    ]]]];
                    
                }elseif(is_array($params['exclude_category_id']) && !empty($params['exclude_category_id'])){
                    $con[] = ['bool' => ['must_not' =>['bool' => [
                        "should" =>  [
                            ['bool' =>[
                                'filter' => [
                                    'terms' => [
                                        'category_id' => $params['exclude_category_id']
                                    ]
                                ],
                            ]],
                            ['bool' =>[
                                'filter' => [
                                    'terms' => [
                                        'category_id' => $params['exclude_category_id']
                                    ]
                                ],
                            ]]
                            
                        ]]]]];
                }
            }
            
            // đối tác
            if(isset($params['exclude_partner_id'])){
                
                if(is_numeric($params['exclude_partner_id']) && $params['exclude_partner_id'] > 0){
                    
                    $con[] = ['bool' => ['must_not' => [
                        ['match' => ['partner_id' => $params['exclude_partner_id']]],
                    ]]];
                    
                }elseif(is_array($params['exclude_partner_id']) && !empty($params['exclude_partner_id'])){
                    $con[] = ['bool' => ['must_not'=>['bool' =>[
                        'filter' => [
                            'terms' => [
                                'partner_id' => $params['exclude_partner_id']
                            ]
                        ],
                    ]]]];
                }
            }
            
            
            if(isset($params['is_invisible']) && $params['is_invisible'] > -1){
                $con[] = ['match' => ['is_invisible' => $params['is_invisible'] ]];
            }
            
            if(isset($params['p_invisible']) && $params['p_invisible'] > -1){
                $con[] = ['match' => ['p_invisible' => $params['p_invisible'] ]];
            }
            
            if(isset($params['is_sold']) && $params['is_sold'] > -1){
                $con[] = ['match' => ['is_sold' => $params['is_sold'] ]];
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
            
            if($min_key + $max_key > 0){
                
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
                        
                        $s1 = substr($sim_filter['regex'], 0,1);
                        $s2 = substr($sim_filter['regex'], -1);
                        
                        $rgx = trim($sim_filter['regex'],'^$');
                        
                        if($s1 == '^' && substr_count($rgx, '*') == 0){
                            $rgx .= '.*';
                        }elseif($s2 == '$' && substr_count($rgx, '*') == 0){
                            $rgx = ".*$rgx";
                        }
                        
                        
                        $con[] = ['regexp' => ['id' => [
                            'value' => $rgx,
                            "flags" => "ALL","max_determinized_states" => 10000,
                            //"rewrite" => "constant_score"
                        ]]];
                        
                        
                        break;
                }
                
                
            }
            
        }
        
        if(isset($params['listsim']) && !in_array($listsim = str_replace(['O','o'], ['0','0'], trim($params['listsim'])) , ['', '*', '.*', '.','+'])){
            
            $pattern = Yii::$app->sim->phonePattern();
            
            preg_match_all($pattern, $listsim, $match);
            
            if(!empty($match[0])){
                $l1 = [];
                
                foreach($match[0] as $sim_number){
                    $l1[] = Yii::$app->sim->getSimId($sim_number);
                    
                    
                }
                
                
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
         
        if(isset($params['listsim_exclude']) && !in_array($listsim_exclude = str_replace(['O','o'], ['0','0'], trim($params['listsim_exclude'])) , ['', '*', '.*', '.','+'])){                       
            
            $pattern = Yii::$app->sim->phonePattern();            
            
            preg_match_all($pattern, $listsim_exclude, $match);
            
            if(!empty($match[0])){
                $l1 = [];
                
                //view($match[0]);
                
                foreach($match[0] as $sim_number){
                    $l1[] = Yii::$app->sim->getSimId($sim_number);
                    
                    
                }
                
                
                $con[] = [
                    
                    'bool' => ['must_not' => [
                    
                    'bool' =>[
                    'filter' => [
                        'terms' => [
                            "id" => $l1,
                            "boost" => 1.0
                        ]
                    ],
                ]]
                    ]];
                
                
            }
        }
        
        if(isset($params['sosim']) && !in_array($sosim = trim($params['sosim']) , ['', '*', '.*', '.','+'])){
            
            $sosim = str_replace(['o', 'O'], ['0','0'], $sosim);
            
         
            
            $pattern = Yii::$app->sim->phonePattern();
            
            preg_match_all($pattern, $sosim, $match);
             
            
            if(!empty($match[0])){
                $l1 = [];
                
                foreach($match[0] as $sim_number){
                    $l1[] = Yii::$app->sim->getSimId($sim_number);
                }
                 
                $con[] = ['bool' =>[
                    'filter' => [
                        'terms' => [
                            "id" => $l1,
                            "boost" => 1.0
                        ]
                    ],
                ]];
                
                
            }else{
                
                
                
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
                    'y' => $y,
                    'đít'=>'.+',
                    'dit'=>'.+',
                    'ĐÍT'=>'.+',
                    'DIT'=>'.+',
                    'Đít'=>'.+',
                    'Dit'=>'.+',
                    'Đit'=>'.+',                     
                    'ĐUÔI'=>'.+',
                    'DUOI'=>'.+',
                    'Đuôi'=>'.+',
                    'Duoi'=>'.+',
                    'đuôi'=>'.+',
                    'duoi'=>'.+',
                    
                    'VT' => '.+',
                    'Vt' => '.+',
                    'vt' => '.+',
                    
                    'VN' => '.+',
                    'Vn' => '.+',
                    'vn' => '.+',
                    'vina' => '.+',
                    
                    'MB' => '.+',
                    'Mb' => '.+',
                    'mb' => '.+',
                    'mobi' => '.+',
                    
                    'VNM' => '.+',
                    'vnm' => '.+',
                    'Vnm' => '.+',
                    '.+|.+'     =>  '.+'
                    
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
                            //"rewrite" => "constant_score"
                        ]]];
                        
                    }else{
                        $con[] = ['regexp' => ['id' => [
                            'value' => "$sosim",
                            "flags" => "ALL","max_determinized_states" => 10000,
                            //"rewrite" => "constant_score"
                        ]]];
                    }
                }
                
                //             view($con);
            }
            
        }
        
        if(isset($params['regex']) && $params['regex'] != ""){
            
            $con[] = ['regexp' => ['id' => [
                'value' =>  trim($params['regex'],'^$'),
                "flags" => "ALL","max_determinized_states" => 10000,
                //"rewrite" => "constant_score"
            ]]];
        }
        
        $exclude = isset($params['exclude']) ? $params['exclude'] : (isset($params['explode']) ? $params['explode'] : []);
        
        
        
        if(!is_array($exclude)){
            $exclude = explode(',', $exclude);
        }
        
        
        if(!empty($exclude)){
            
            $a = [0,1,2,3,4,5,6,7,8,9];
            
            $n49 = $n53 =false;
            
            foreach($exclude as $k=>$n){
                if($n==49){
                    $n49 = true;
                    unset($exclude[$k]);
                }
                
                if($n==53){
                    $n53 = true;
                    unset($exclude[$k]);
                }
            }
            $array_diff = array_diff($a, $exclude);
            
            
            $con[] = ['regexp' => ['id' => [
                'value' =>  "[".implode('', $array_diff)."]+",
                "flags" => "ALL","max_determinized_states" => 10000,
                //"rewrite" => "constant_score"
            ]]];
            
            if($n49 == true)
            {
                
            }
            
            if($n53 == true)
            {
                
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
        
        $from_date = isset($params['from_date']) && $params['from_date'] != "" ? strtotime(str_replace('/', '-', $params['from_date'])) : 0;
        
        $to_date = isset($params['to_date']) && $params['to_date'] != "" ? strtotime(str_replace('/', '-', $params['to_date'])) : 0;
        
        
        if($from_date + $to_date > 0){
            
            if($to_date < $from_date){
                $to_date = $from_date + 365 * 86400;
            }
            
            $con[] = ['range' => ['updated_at' => [
                'gte'   => $from_date,
                'lte'   =>  $to_date
            ]
            ]
            ];
        }
          
        
        if(!empty($con)){
            $body['query']['bool']['must'] = $con;
        }
       
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'body' => $body,
        ];
        
        
        //view($params);
        
        return $params;
    }
    
    
    public function populateData($data, $params = [])
    {
        $rs = [];
        $n49 = $n53 = false;
        if(isset($params['n49'])){
            $n49 = $params['n49'];
        }
        
        if(isset($params['n53'])){
            $n53 = $params['n53'];
        }
        
        if(isset($data['hits']['hits']) && !empty($data['hits']['hits'])){
            foreach ($data['hits']['hits'] as $item){
                
                if($n49){
                   $pattern = '/49/i';
                   preg_match($pattern, $item['_source']['id'], $m);
                   if(!empty($m)){
                       continue;
                   }
                }
                
                if($n53){
                    $pattern = '/53/i';
                    preg_match($pattern, $item['_source']['id'], $m);
                    if(!empty($m)){
                        continue;
                    }
                }
                
                $rs[] = $item['_source'];
            }
        }
        
        return $rs;
    }
    
    
    
    public function setMapping($index = null, $type = null)
    {
        if($index == null){
            $index = $this->_index;
        }
        
        if($type == null){
            $type = $this->_type;
        }
        
        $params = [
            'index' => $index,
            'type' => $type,
            'include_type_name'=>true,
            'body' => [
                
                $this->_type => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'keyword'
                        ],
                        'display' => [
                            'type' => 'text',
                            'analyzer' => 'reuters',
//                             'copy_to' => 'category_label'
                        ],
                        
//                         'category_label' => [
//                             'type' => 'text',
//                             'analyzer' => 'reuters'
//                         ],
                        
//                         'partner_label' => [
//                             'type' => 'text',
//                             'analyzer' => 'reuters'
//                         ],
                        'partner_id' => [
                            'type' => 'integer'
                        ],
                        'category_id' => [
                            'type' => 'integer'
                        ],
                        'category2_id' => [
                            'type' => 'integer'
                        ],
                        'category3_id' => [
                            'type' => 'integer'
                        ],
                        'category4_id' => [
                            'type' => 'integer'
                        ],
                        
                        'price' => [
                            'type' => 'long'
                        ],
                        'price1' => [
                            'type' => 'long'
                        ],
                        'price0' => [
                            'type' => 'long'
                        ],
                        'price2' => [
                            'type' => 'long'
                        ],
                        
                        'score' => [
                            'type' => 'integer'
                        ],
                        'number_of_key' => [
                            'type' => 'integer'
                        ],
                        'type_id' => [
                            'type' => 'integer'
                        ],
                        
                        'updated_at' => [
                            'type' => 'integer'
                        ],
                        'created_time' => [
                            'type' => 'integer'
                        ],
                        'duplicate' => [
                            'type' => 'integer'
                        ],
                        'is_sold' => [
                            'type' => 'integer'
                        ],
                        'exchange_price' => [
                            'type' => 'integer'
                        ],
                        'nguhanh' => [
                            'type' => 'integer'
                        ],
                        'nut' => [
                            'type' => 'integer'
                        ],
                        'nguhanh' => [
                            'type' => 'integer'
                        ],
                        'istm' => [
                            'type' => 'integer'
                        ],
                        'status' => [
                            'type' => 'integer'
                        ],
                        'history' => [
                            'type' => 'object'
                        ],
                        'attrs' => [
                            'type' => 'object'
                        ],
//                         'list' => [
//                             'type' => 'object'
//                         ],
                        
                        'note' => [
                            'type' => 'text'
                        ],
                        'note2' => [
                            'type' => 'text'
                        ],
                        'is_invisible' => [
                            'type' => 'integer'
                        ],
                        
                        'p_invisible' => [
                            'type' => 'integer'
                        ],
                        
                        'is_fixed_price' => [
                            'type' => 'integer'
                        ],
                        
                        'daicat' => [
                            'type' => 'integer'
                        ],
                        
                        'dauso' => [
                            'type' => 'keyword',
                            //                             "fields" => [
                                //                                 "raw" => [
                                    //                                     "type" => "keyword"
                                    //                                 ]
                                //                             ]
                        ],
                        
                        's2' => [
                            'type' => 'keyword',
                            //                             "fields" => [
                                //                                 "raw" => [
                                    //                                     "type" => "keyword"
                                    //                                 ]
                                //                             ]
                        ],
                        's3' => [
                            'type' => 'keyword',
                            //                             "fields" => [
                                //                                 "raw" => [
                                    //                                     "type" => "keyword"
                                    //                                 ]
                                //                             ]
                        ],
                        's4' => [
                            'type' => 'keyword',
                            //                             "fields" => [
                                //                                 "raw" => [
                                    //                                     "type" => "keyword"
                                    //                                 ]
                                //                             ]
                        ],
                        's5' => [
                            'type' => 'keyword',
                            //                             "fields" => [
                            //                                 "raw" => [
                            //                                     "type" => "keyword"
                            //                                 ]
                            //                             ]
                        ],
                        
                        's6' => [
                            'type' => 'keyword',
                            //                             "fields" => [
                                //                                 "raw" => [
                                    //                                     "type" => "keyword"
                                    //                                 ]
                                //                             ]
                        ],
                        
                        'meta' => [
                            'type' => 'object'
                        ],
                        
                    ]
                ]
            ]
        ];
         
        
        // Update the index mapping
        $this->client->indices()->putMapping($params);
        
         
        
    }
    
    public function getMapping($index = null)
    {
        if($index == null){
            $index = $this->_index;
        }
        
        
        
        $params = [
            'index' => $index,
            'type'  => $this->_type,
            'include_type_name' => true,
        ];
        
        
        return $this->client->indices()->getMapping($params);
    }
    
    
    public function addMapping($properties)
    {
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            'include_type_name'=>true,
            'body' => [
                
                $this->_type => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => $properties
                ]
            ]
        ];
        
        return $this->client->indices()->putMapping($params);
    }
    
    
    public function removeMapping($fieldName, $limit = 1000)
    {
        $body = [];
        
        $body['script'] = "ctx._source.remove('".$fieldName."')";
        $body['query'] = [
            "exists" => ["field" => "$fieldName"]
        ];
         
        
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
//             'include_type_name'=>true,
            'body' => $body
        ];
        
        
        
        $params['size'] = $limit;
        
        $this->client->updateByQuery($params);
    }
    
    public function countFieldMapping($fieldName)
    {
        $body = [];
        
        //$body['script'] = "ctx._source.remove('".$fieldName."')";
        $body['query'] = [
            "exists" => ["field" => "$fieldName"]
        ];
        
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
//             'include_type_name'=>true,
            'body' => $body
        ];
        
        return $this->client->count($params);
    }
    
    
    
    public function findItemsNotExistField($fieldName, $limit = 1000)
    {
        $body = [];
         
        $body['query'] = ['bool' => ['must_not' => [
            "exists" => ["field" => "$fieldName"]
        ]]];
        
        
        $params = [
            'index' => $this->_index,
            'type' => $this->_type,
            //             'include_type_name'=>true,
            'body' => $body
        ];
        
        
        
        $params['size'] = $limit;
        
//         view($params);
        
        return $this->client->search($params);
    }
}