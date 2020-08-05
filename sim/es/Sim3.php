<?php 
namespace izi\sim\es;
use Yii;
class Sim3 extends BaseSim
{
 
    /**
     * Cập nhật 10/11/2019
     * reindex sim3data => sim2data (copy data & mapping)
     * 
     */
    private $_index = 'sim3data';
    private $_type = 'sim';
    
    public function init()
    {
         $this->setIndex($this->_index);
         $this->setType($this->_type);         
         parent::init();
          
         
         if(!$this->existedIndex()){
             $this->setIndexProperties($this->indexProperties());
             $this->createIndex();
             exit;
         }
         
    }
    
    public function indexProperties()
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
    
    public function mapping($index = null, $type = null)
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
                           // 'copy_to' => 'category_label'
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
                        'note' => [
                            'type' => 'text'
                        ],
                        'note2' => [
                            'type' => 'text'
                        ],
                        'is_invisible' => [
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
                        's6' => [
                            'type' => 'keyword',
//                             "fields" => [
//                                 "raw" => [
//                                     "type" => "keyword"
//                                 ]
//                             ]
                        ],
                    ]
                ]
            ]
        ];
        
        // Update the index mapping
        $this->client->indices()->putMapping($params);
         
    }
    
    
    public function import($limit = 50000)
    {
        
        
        
        
        
        //         $l = Yii::$app->sim->getItems(['limit' => 10000]);
        
        $stt = 4;
        
        $query = new \yii\mongodb\Query();
        $query->from(\izi\sim\SimonlineMongodbModel::collectionName())->where(['<' ,'status', $stt]);
        
        //         view($query->count(1),1,1);
        
        $l = $query->limit($limit)->all();
        
        
        $c = 0;
        
        $body = []; $ex = [];
        
        $cv = 0;
        
        foreach ($l as $v){
             
            
            if(!empty($this->findSim($v['id'], $v['partner_id']))){
                Yii::$app->sim->collection->update(['id' => $v['id'], 'partner_id' => $v['partner_id']], ['status' => $stt]);
                continue;
            }
            
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
            
            ////////////////////////////
            
            if($cv++ > 10000){
            
            $params = [
                'body' =>$body
            ];
            
            //             view($params);
            
            $responses = $this->client->bulk($params);
            //             return $responses;
            if($responses['errors']){
                Yii::$app->sim->collection->update(['id' => $v['id'], 'partner_id' => $v['partner_id']], ['status' => -1]);
                view($responses);
                view($ex);
                view($l);
                
            }
            
            $body = []; $ex = [];// ''
            $cv = 0;
            
            }
            
            Yii::$app->sim->collection->update(['id' => $v['id'], 'partner_id' => $v['partner_id']], ['status' => $stt]);
            
        }
        
        
        if(!empty($body)){
            $params = [
                'body' =>$body
            ];
            
            //             view($params);
            
            $responses = $this->client->bulk($params);
            //             return $responses;
            if($responses['errors']){
//                 Yii::$app->sim->collection->update(['id' => $ex], ['status' => -1]);
//                 view($responses);
//                 view($ex);
//                 view($l);
//                 exit;
            }
            
            return $c;
        }
        
        return $c;
    }
    
//     public function getMapping()
//     {
//         $params['index'] = $this->_index;
//         $params['type']  = $this->_type;
//         $params['include_type_name'] = true;
//         return $this->client->indices()->getMapping($params);
        
// //         view($ret);
//     }
    
    public function test()
    {
//         $params = array_merge(['index' => 'sim2data'] );
//         $params = array_merge(['index' => 'sim2data'] , $this->indexProperties());
//         view($params);
//         $this->client->indices()->create($params);
        
//         exit;

//         $this->mapping('sim2data');

//         $this->client->indices()->delete($params);
        
        
//         $params['index'] = 'sim2data';
//         $params['type']  = $this->_type;
//         $params['include_type_name'] = true;
//         return $this->client->indices()->getMapping($params);

        /**
         * POST /_reindex
{
  "source": {
    "index": "users"
  },
  "dest": {
    "index": "new_users"
  }
}
         */
        
//         $params = [
//             'body' => [
//                 'source' => [
//                     'index'  => 'sim3data',
//                 ],
//                 'dest' => [
//                     'index' => 'sim2data'
//                 ]
//             ]
//         ]; 
//         $ret = $this->client->reindex($params);
        
//                 view($ret,1,1);
    }
    
}