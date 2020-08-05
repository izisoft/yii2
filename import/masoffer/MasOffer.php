<?php
namespace izi\import\masoffer;
use Yii;

class MasOffer extends \yii\base\Component
{
    
    public $key;
    
    private function parseDataCurl($url){
        //** Bước 1: Khởi tạo request
        $ch = curl_init();
        
        //** Bước 2: Thiết lập các tuỳ chọn
        // Thiết lập URL trong request
        curl_setopt($ch, CURLOPT_URL, $url);
        
        // Thiết lập để trả về dữ liệu request thay vì hiển thị dữ liệu ra màn hình
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // ** Bước 3: thực hiện việc gửi request
        $output = curl_exec($ch);
        
        //view(json_decode($output));
        
        $l = json_decode( $output,1); // hiển thị nội dung
        // ** Bước 4 (tuỳ chọn): Đóng request để giải phóng tài nguyên trên hệ thống
        $data = isset($l['data']) ? $l['data'] : [];
        curl_close($ch);
        return $data;
    }
    
    private function parseData($url ){
//         if(!empty($r = $this->parseDataCurl($url)) ){ 
//             //return $r;
//         }
        return $this->parseDataGetContent($url);
    }
    
    
    private function parseDataGetContent($url ){
        $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $l = json_decode(@file_get_contents($url,false,$context),1);
        $data = isset($l['data']) ? $l['data'] : [];
        return $data;
    }
    public function getAllOffers(){
        $url = 'http://api.masoffer.com/offer/all?pub_id=zinzinx8&token=4QtGT8Xg2eVG7R6fOla5Kg==';
        
        $data = $this->parseDataCurl($url);
         
        $i = 0;
        
        if(!empty($data)){
            foreach ($data as $value) {      
                $c = Yii::$app->customer->model->findIdentityByCode($value['offer_id']);
                
                $f['name'] = $value['name'];
                $f['address'] = $value['address'];
                
                unset($value['address']);
                unset($value['name']);
                $value['icon'] = isset($value['avatar']) ? $value['avatar'] : '';
                $f = splitName($f);
                $f['type_id'] = TYPE_ID_PARTNER;
                $f['bizrule'] = json_encode($value, JSON_UNESCAPED_UNICODE);
                $table = Yii::$app->customer->model->tableName();
                if(!empty($c)){
                    //Yii::$app->db->createCommand()->update($table, $f, ['code'=>$value['offer_id'],'sid'=>__SID__])->execute();
                }else{
                    Yii::$app->db->createCommand()->insert($table, $f + ['code'=>$value['offer_id'],'sid'=>__SID__])->execute();
                    //view($f['name']);
                }
                 
            }
        }
        
        Yii::$app->session->set(md5(__CLASS__), 1);
         
    }
    
    
    public function getAllPromotion($params){
        
        if(!Yii::$app->session->has(md5(__CLASS__))){
            $this->getAllOffers();
        }
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        $promotion_id = $params['promotion_id'];
        $coupon_id = $params['coupon_id'];
        $i = 0;
        $offers = Yii::$app->customer->findCustomer(['type_id'=>TYPE_ID_PARTNER]);
        
        $ip = 0; 
        
        if(!empty($offers)){
            foreach ($offers as $offer){
                
                $url = 'http://api.masoffer.com/promotions/?offer_id=' . $offer['code'];
                
                
                if(Yii::$app->session->has(md5($url))){
                    continue;
                }
                
                
                
                
                
                
                
//                 $data = $this->parseData($url);
                $data = $this->parseDataCurl($url);
                 
                if(!empty($data['promotions'])){
                    
                    Yii::$app->session->set(md5($url), 1);
                    if($ip++ > 5) break;
                    
                    foreach ($data['promotions'] as $value){
                        $item = Yii::$app->frontend->model->findItemByCode($value['id']);
                        
                        $aff_url = str_replace([
                            '{publisher_id}',
                            'http%3A%2F%2Fhttps%2F%2F',
                        ], ['zinzinx8',
                            'https%3A%2F%2F'
                        ], $value['aff_url']);
                        
                        //
                        
                        
                        
                        if(!empty($item) && $item['is_active'] == 0){
                            $text = isset($item['ctab'][0]['text']) ? $item['ctab'][0]['text'] : '';
                            
                            if($text != ""){
                                $dom = new \simple_html_dom();
                                $dom->load($text);
                                foreach($dom->find("a") as $e){
                                    
                                    $checksum = md5($aff_url);
                                    
                                    $url = Yii::$app->slink->model->findUrlByChecksum($checksum);
                                    if(!!empty($url)){
                                        $data = [
                                            'checksum'=>$checksum,
                                            'original'=>$aff_url,
                                            'title'=>$item['title'],
                                            'description'=>'',
                                            'sid'=>__SID__,
                                            'time'=>time(),
                                            'id'=>Yii::$app->slink->model->getId(6),
                                        ];
                                        if(Yii::$app->slink->model->validateId($data['id'])){
                                            Yii::$app->db->createCommand()->insert(\izi\slink\ShortLinkModel::tableName(),$data)->execute();
                                            
                                        }
                                    }else{
                                        $data = $url;
                                    }
                                    
                                    $e->href = '/goto/' . $data['id'];
                                    $e->target = '_blank';
                                    $text = $dom->save();
                                    
                                    $content = [
                                        'ctab'  =>  [
                                            [
                                                'title'=>'Chi tiết',
                                                'style'=>0,
                                                'text'=>$text
                                            ]
                                        ]
                                    ];
                                    
                                    $f['is_active'] = 1;
                                    $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                                    Yii::$app->db->createCommand()->update(\app\modules\admin\models\Content::tableName(), $f,['id'=>$item['id']])->execute();
                                    
                                    
                                }
                            }else{
                                
                            }
                        }
                        
                        
                        if(!!empty($item)){
                            //$offer = Yii::$app->customer->model->findIdentityByCode($value['offer_id']);
                            if(!empty($offer)){
                                $f = [
                                    'type'=>'promotion',
                                    'sid'=>__SID__,
                                    'created_by'=>isset(Yii::$app->user->id) && Yii::$app->user->id > 0 ? Yii::$app->user->id : 0,
                                    'is_active'=>1,
                                    'updated_at'=> date('Y-m-d H:i:s')
                                ];
                                
                                
                                $item = Yii::$app->frontend->model->findItemByCode($value['id']);
                                
                                
                                
                                if(!!empty($item)){
                                    
                                    $i++;
                                    
                                    $f['title'] = trim($value['title']);
                                    
                                    $f['expired_date'] = isset($value['expired_date']) && $value['expired_date'] > 0 ? $value['expired_date'] : time() + 30 * 86400;
                                    
                                    $f['started_date'] = isset($value['started_date']) && $value['started_date'] > 0 ? $value['started_date'] : time();
                                    
                                    
                                    
                                    
                                    $f['code'] = $value['id'];
                                    $biz = [
                                        'info' => trim($value['description']),
                                        'aff_url'   =>  $aff_url,
                                        'original_url'=>$value['original_url'],
                                        'coupon_code'=>isset($value['coupon_code']) ? $value['coupon_code'] : '',
                                        'promotion_id'=>$value['id'],
                                        'icon'=>$value['thumbnail'],
                                        'thumbnail'=>$value['thumbnail'],
                                        'expired_date_format'=>$value['expired_date_format'],
                                        'listImages'=>[
                                            [
                                                'image' =>  $value['thumbnail'],
                                                'main'  =>  1,
                                                'info'  =>  '',
                                                'title' =>  $value['title'],
                                            ]
                                        ]
                                    ];
                                    //$aff_url = str_replace('{publisher_id}', 'zinzinx8', $value['aff_url']);
                                    $dom = new \simple_html_dom();
                                    $dom->load($value['content']);
                                    foreach($dom->find("a") as $e){
                                        //                                 $e->href = $aff_url;
                                        //                                 $e->target = '_blank';
                                        //$dom->load($dom->save());
                                        
                                        $checksum = md5($aff_url);
                                        
                                        $url = Yii::$app->slink->model->findUrlByChecksum($checksum);
                                        if(!!empty($url)){
                                            $data = [
                                                'checksum'=>$checksum,
                                                'original'=>$aff_url,
                                                'title'=>$f['title'],
                                                'description'=>'',
                                                'sid'=>__SID__,
                                                'time'=>time(),
                                                'id'=>Yii::$app->slink->model->getId(6),
                                            ];
                                            if(Yii::$app->slink->model->validateId($data['id'])){
                                                Yii::$app->db->createCommand()->insert(\izi\slink\ShortLinkModel::tableName(),$data)->execute();
                                                
                                            }
                                        }else{
                                            $data = $url;
                                        }
                                        $e->href = '/goto/' . $data['id'];
                                        $e->target = '_blank';
                                        $value['content'] = $dom->save();
                                        
                                    }
                                    $content = [
                                        'ctab'  =>  [
                                            [
                                                'title'=>'Chi tiết',
                                                'style'=>0,
                                                'text'=>$value['content']
                                            ]
                                        ]
                                    ];
                                    
                                    $f['bizrule'] = json_encode($biz, JSON_UNESCAPED_UNICODE);
                                    $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                                    
                                    $f['url'] = unMark($f['title']);
                                    
                                    $f['url'] = \app\modules\admin\models\Slugs::getSlug(isset($f['url']) && $f['url'] != "" ? $f['url'] : unMark($f['title']), (new \app\modules\admin\models\Content())->getID());
                                    $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
                                    
                                    
                                    $id = Yii::$app->zii->insert(\app\modules\admin\models\Content::tableName(), $f);
                                    // Update category
                                    Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$promotion_id])->execute();
                                    
                                    if(isset($value['coupon_code']) && $value['coupon_code'] != ""){
                                        Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$coupon_id])->execute();
                                        
                                    }
                                    
                                    
                                    Yii::$app->db->createCommand()->insert('items_to_customer',['item_id'=>$id,'customer_id'=>$offer['id'],'type_id'=>TYPE_ID_PARTNER])->execute();
                                    
                                    \app\modules\admin\models\Slugs::updateSlug($f['url'],$id,$f['type'],1,$biz);
                                    
                                    /// 
                                    $field = ['field'=>'started_date'];
                                    
                                    $params = [
                                        'checksum'=>md5(json_encode($field)),
                                        'sid'=>__SID__, 'item_id'=>$id, 'type_code'=>Yii::$app->cronjob->type->CRON_UPDATE_ITEM_PROMOTION_STATUS];
                                    
                                    if($f['started_date'] > time()){
                                        $f['is_invisibled'] = 1;
                                    }
                                    
                                    $params['started_time'] = $f['started_date'];
                                    $params['bizrule'] = (json_encode($field));
                                    Yii::$app->cronjob->model->createJob($params);
                                    
                                    
                                    $field = ['field'=>'expired_date'];
                                    
                                    $params = [
                                        'checksum'=>md5(json_encode($field)),
                                        'sid'=>__SID__, 'item_id'=>$id, 'type_code'=>Yii::$app->cronjob->type->CRON_UPDATE_ITEM_PROMOTION_STATUS];
                                    
                                    
                                    
                                    $job = Yii::$app->cronjob->model->getJobs($params);
                                    
                                    $params['started_time'] = $f['expired_date'];
                                    $params['bizrule'] = (json_encode($field));
                                    
                                     
                                        
                                    Yii::$app->cronjob->model->createJob($params);
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                }else{
                                    
                                    
                                    
                                }
                                
                                
                                
                            }
                        }
                    }
                }
            }
        }
        
//         $url = 'http://api.masoffer.com/promotions';
//         $data = $this->parseData($url);
        
//         if(!empty($data['promotions'])){
//             foreach ($data['promotions'] as $value){
                
//             }
//         }
        
        $log_file = Yii::getAlias('@runtime/logs/masoffer.log');
        
        writeFile($log_file, '['.date('Y-m-d H:i:s').'] ' . $i . ' - ' . FULL_URL . '
', 'a');
        
        if($i > 0){
            $notis = [
                'title'=>"Có $i chương trình km mới được cập nhật.",
                'link'=>'#',
                //'uid'=>Yii::$app->user->id
            ];
            
            Yii::$app->notify->model->insertNotification($notis);
            
        }
        
        return $i;
    }
    
    public function getAllPromotion2($params){
        require_once Yii::getAlias('@common') . "/functions/simple_html_dom.php";
        $promotion_id = $params['promotion_id'];
        $coupon_id = $params['coupon_id'];
        $i = 0;
        
        $url = 'http://api.masoffer.com/promotions';
        $data = $this->parseData($url);
        
        if(!empty($data['promotions'])){
            foreach ($data['promotions'] as $value){
                $item = Yii::$app->frontend->model->findItemByCode($value['id']);
                
                $aff_url = str_replace([                    
                    '{publisher_id}',
                    'http%3A%2F%2Fhttps%2F%2F',
                ], ['zinzinx8',
                    'https%3A%2F%2F'
                ], $value['aff_url']);
                
                //
                
                
                
                if(!empty($item) && $item['is_active'] == 0){
                    $text = isset($item['ctab'][0]['text']) ? $item['ctab'][0]['text'] : '';
                     
                    if($text != ""){
                        $dom = new \simple_html_dom();
                        $dom->load($text);
                        foreach($dom->find("a") as $e){
                           
                            $checksum = md5($aff_url);
                            
                            $url = Yii::$app->slink->model->findUrlByChecksum($checksum);
                            if(!!empty($url)){
                                $data = [
                                    'checksum'=>$checksum,
                                    'original'=>$aff_url,
                                    'title'=>$item['title'],
                                    'description'=>'',
                                    'sid'=>__SID__,
                                    'time'=>time(),
                                    'id'=>Yii::$app->slink->model->getId(6),
                                ];
                                if(Yii::$app->slink->model->validateId($data['id'])){
                                    Yii::$app->db->createCommand()->insert(\izi\slink\ShortLinkModel::tableName(),$data)->execute();
                                    
                                }
                            }else{
                                $data = $url;
                            }
                            
                            $e->href = '/goto/' . $data['id'];
                            $e->target = '_blank';
                            $text = $dom->save();
                            
                            $content = [
                                'ctab'  =>  [
                                    [
                                        'title'=>'Chi tiết',
                                        'style'=>0,
                                        'text'=>$text
                                    ]
                                ]
                            ];
                            
                            $f['is_active'] = 1;
                            $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                            Yii::$app->db->createCommand()->update(\app\modules\admin\models\Content::tableName(), $f,['id'=>$item['id']])->execute();
                            
                            
                        }
                    }else{
                        
                    }
                }
                    
                
                if(!!empty($item)){
                    $offer = Yii::$app->customer->model->findIdentityByCode($value['offer_id']);
                    if(!empty($offer)){
                        $f = [
                            'type'=>'promotion',
                            'sid'=>__SID__,
                            'created_by'=>isset(Yii::$app->user->id) && Yii::$app->user->id > 0 ? Yii::$app->user->id : 0,
                            'is_active'=>1,
                            'updated_at'=> date('Y-m-d H:i:s')
                        ];
                         
                        
                        $item = Yii::$app->frontend->model->findItemByCode($value['id']);
                        
                        
                        
                        if(!!empty($item)){
                            
                            $i++;
                            
                            $f['title'] = trim($value['title']);
                            $f['expired_date'] = isset($value['expired_date']) && $value['expired_date'] > 0 ? $value['expired_date'] : 0;
                            $f['started_date'] = isset($value['started_date']) && $value['started_date'] > 0 ? $value['started_date'] : 0;
                            $f['code'] = $value['id'];
                            $biz = [
                                'info' => trim($value['description']),
                                'aff_url'   =>  $aff_url,
                                'original_url'=>$value['original_url'],
                                'coupon_code'=>isset($value['coupon_code']) ? $value['coupon_code'] : '',
                                'promotion_id'=>$value['id'],
                                'icon'=>$value['thumbnail'],
                                'thumbnail'=>$value['thumbnail'],
                                'expired_date_format'=>$value['expired_date_format'],
                                'listImages'=>[
                                    [
                                        'image' =>  $value['thumbnail'],
                                        'main'  =>  1,
                                        'info'  =>  '',
                                        'title' =>  $value['title'],
                                    ]
                                ]
                            ];
                            //$aff_url = str_replace('{publisher_id}', 'zinzinx8', $value['aff_url']);
                            $dom = new \simple_html_dom();
                            $dom->load($value['content']);
                            foreach($dom->find("a") as $e){
//                                 $e->href = $aff_url;
//                                 $e->target = '_blank';
                                //$dom->load($dom->save());
                               
                                $checksum = md5($aff_url);
                                
                                $url = Yii::$app->slink->model->findUrlByChecksum($checksum);
                                if(!!empty($url)){
                                    $data = [
                                        'checksum'=>$checksum,
                                        'original'=>$aff_url,
                                        'title'=>$item['title'],
                                        'description'=>'',
                                        'sid'=>__SID__,
                                        'time'=>time(),
                                        'id'=>Yii::$app->slink->model->getId(6),
                                    ];
                                    if(Yii::$app->slink->model->validateId($data['id'])){
                                        Yii::$app->db->createCommand()->insert(\izi\slink\ShortLinkModel::tableName(),$data)->execute();
                                        
                                    }
                                }else{
                                    $data = $url;
                                }
                                $e->href = '/goto/' . $data['id'];
                                $e->target = '_blank';
                                $value['content'] = $dom->save();
                                
                            }
                            $content = [
                                'ctab'  =>  [
                                    [
                                        'title'=>'Chi tiết',
                                        'style'=>0,
                                        'text'=>$value['content']
                                    ]
                                ]
                            ];
                            
                            $f['bizrule'] = json_encode($biz, JSON_UNESCAPED_UNICODE);
                            $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                            
                            $f['url'] = unMark($f['title']);
                            
                            $f['url'] = \app\modules\admin\models\Slugs::getSlug(isset($f['url']) && $f['url'] != "" ? $f['url'] : unMark($f['title']), (new \app\modules\admin\models\Content())->getID());
                            $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
                            
                            
                            $id = Yii::$app->zii->insert(\app\modules\admin\models\Content::tableName(), $f);
                            // Update category
                            Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$promotion_id])->execute();
                            if(isset($value['coupon_code']) && $value['coupon_code'] != ""){
                                Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$coupon_id])->execute();
                                
                            }
                            
                            
                            Yii::$app->db->createCommand()->insert('items_to_customer',['item_id'=>$id,'customer_id'=>$offer['id'],'type_id'=>TYPE_ID_PARTNER])->execute();
                            
                            \app\modules\admin\models\Slugs::updateSlug($f['url'],$id,$f['type'],1,$biz);
                            
                        }else{
                            
                            //                             $aff_url   =  str_replace('{publisher_id}', 'zinzinx8', $value['aff_url']);
                            
                            //                             $content = [
                            //                                 'ctab'  =>  [
                            //                                     [
                            //                                         'title'=>'Chi tiết',
                            //                                         'style'=>0,
                            //                                         'text'=>str_replace($value['original_url'], $aff_url, $value['content'])
                            //                                     ]
                            //                                 ]
                            //                             ];
                            
                            //                             //view($content);
                            //                             $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                            
                            
                            //                             $f['url'] = unMark($item['title']);
                            //                             $f['url'] = \app\modules\admin\models\Slugs::getSlug($f['url'],$item['id']);
                            //                             $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
                            //                             Yii::$app->db->createCommand()->update(\app\modules\admin\models\Content::tableName(), $f,['id'=>$item['id']])->execute();
                            //                             \app\modules\admin\models\Slugs::updateSlug($f['url'],$item['id'],$f['type'],1,$item);
                            
                        }
                        
                        
                        
                    }
                }
            }
        }
        
//         $offers = Yii::$app->customer->findCustomer(['type_id'=>TYPE_ID_PARTNER]);
//         if(!empty($offers)){
//             foreach ($offers as $offer){
//                 $url = 'http://api.masoffer.com/promotions/' . $offer['code'];
//                 $data = $this->parseData($url);
//                 $f = [
//                     'type'=>'promotion',
//                     'sid'=>__SID__,
//                     'created_by'=>isset(Yii::$app->user->id) && Yii::$app->user->id > 0 ? Yii::$app->user->id : 0,
//                     'is_active'=>0                
//                 ];
//                 $f['updated_at'] = date('Y-m-d H:i:s');
//                 if(!empty($data['promotions'])){
//                     foreach ($data['promotions'] as $value) {
                        
//                         $item = Yii::$app->frontend->model->findItemByCode($value['id']);

                        
//                         if(!!empty($item)){
                            
//                             $i++;
                            
//                             $f['title'] = trim($value['title']);
//                             $f['expired_date'] = isset($value['expired_date']) && $value['expired_date'] > 0 ? $value['expired_date'] : 0;
//                             $f['started_date'] = isset($value['started_date']) && $value['started_date'] > 0 ? $value['started_date'] : 0;
//                             $f['code'] = $value['id'];
//                         $biz = [
//                             'info' => trim($value['description']),
//                             'aff_url'   =>  str_replace('{publisher_id}', 'zinzinx8', $value['aff_url']),
//                             'original_url'=>$value['original_url'],
//                             'coupon_code'=>isset($value['coupon_code']) ? $value['coupon_code'] : '',
//                             'promotion_id'=>$value['id'],
//                             'icon'=>$value['thumbnail'],
//                             'thumbnail'=>$value['thumbnail'],
//                             'expired_date_format'=>$value['expired_date_format'],
//                             'listImages'=>[
//                                 [
//                                     'image' =>  $value['thumbnail'],
//                                     'main'  =>  1,
//                                     'info'  =>  '',
//                                     'title' =>  $value['title'],
//                                 ]
//                             ]
//                         ];
//                         $aff_url = str_replace('{publisher_id}', 'zinzinx8', $value['aff_url']);
//                         $content = [
//                             'ctab'  =>  [
//                                 [
//                                     'title'=>'Chi tiết',
//                                     'style'=>0,
//                                     'text'=>str_replace($value['original_url'], $aff_url, $value['content'])
//                                 ]
//                             ]
//                         ];
                        
//                         $f['bizrule'] = json_encode($biz, JSON_UNESCAPED_UNICODE);
//                         $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                        
//                         $f['url'] = unMark($f['title']);
                        
//                         $f['url'] = \app\modules\admin\models\Slugs::getSlug(isset($f['url']) && $f['url'] != "" ? $f['url'] : unMark($f['title']), (new \app\modules\admin\models\Content())->getID());
//                         $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
                        
                        
//                         $id = Yii::$app->zii->insert(\app\modules\admin\models\Content::tableName(), $f);
//                         // Update category
//                         Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$promotion_id])->execute();
//                         if(isset($value['coupon_code']) && $value['coupon_code'] != ""){
//                             Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$coupon_id])->execute();
                            
//                         }
                        
                        
//                         Yii::$app->db->createCommand()->insert('items_to_customer',['item_id'=>$id,'customer_id'=>$offer['id'],'type_id'=>TYPE_ID_PARTNER])->execute();
                        
//                         \app\modules\admin\models\Slugs::updateSlug($f['url'],$id,$f['type'],1,$biz);
                        
//                         }else{
                           
// //                             $aff_url   =  str_replace('{publisher_id}', 'zinzinx8', $value['aff_url']);
                             
// //                             $content = [
// //                                 'ctab'  =>  [
// //                                     [
// //                                         'title'=>'Chi tiết',
// //                                         'style'=>0,
// //                                         'text'=>str_replace($value['original_url'], $aff_url, $value['content'])
// //                                     ]
// //                                 ]
// //                             ];
                            
// //                             //view($content);
// //                             $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                            
                            
// //                             $f['url'] = unMark($item['title']);
// //                             $f['url'] = \app\modules\admin\models\Slugs::getSlug($f['url'],$item['id']);
// //                             $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
// //                             Yii::$app->db->createCommand()->update(\app\modules\admin\models\Content::tableName(), $f,['id'=>$item['id']])->execute();
// //                             \app\modules\admin\models\Slugs::updateSlug($f['url'],$item['id'],$f['type'],1,$item);
                            
//                         }
                    
//                     };
//                 }
//             }
//         }
        
        return $i;
    }
    
    
    
    
    
}