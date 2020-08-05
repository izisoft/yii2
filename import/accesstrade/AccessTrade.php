<?php
namespace izi\import\accesstrade;
use Yii;

class AccessTrade extends \yii\base\Component
{
    
    public $access_key = 'K1qy6icAXYoIdpEtJxSsVfiYGzL18hQo';
    
    public $token;

    public function init()
    {
        $this->token = 'Token ' . $this->access_key;
    }
    
    private function parseDataCurl($url){
        //** Bước 1: Khởi tạo request
        $ch = curl_init();
        
        //** Bước 2: Thiết lập các tuỳ chọn
        // Thiết lập URL trong request
        curl_setopt($ch, CURLOPT_URL, $url);
        
//         curl_setopt($ch, CURLOPT_POST, 0);

// header
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Authorization:' .  $this->token
        ]);
        
        
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Thiết lập để trả về dữ liệu request thay vì hiển thị dữ liệu ra màn hình
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        
        
         
        
        // ** Bước 3: thực hiện việc gửi request
        $output = curl_exec($ch);
         
         
        
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
    
    /**
     *  1. Lấy danh sách chiến dịch
     *  https://api.accesstrade.vn/v1/campaigns        
     *      
     *  Mô tả kết quả
Tham số	Mô tả
total	Số lượng chiến dịch (campaigns).
data.id	Id của campaign.
data.name	Tên chiến dịch.
data.approval	unregistered: User chưa đăng kí chiến dịch. pending: Chiến dịch đang chờ duyệt cho user. successful: User được chấp thuận chạy chiến dịch.
data.status	Trạng thái của campaign, 1: Running.
data.merchant	Tên merchant.
data.conversion_policy	
data.cookie_duration	Thời gian hiệu lực của cookie (second).
data.cookie_policy	
data.description	Mô tả campaign.
data.start_time	Ngày băt đầu campaign.
data.end_time	Ngày kết thúc campaign.
data.category	
data.sub_category	
data.type	
data.url	Url campaign.    
    */
    
    
    public function getCampaigns()
    {
        $url = 'https://api.accesstrade.vn/v1/campaigns';
        
        $data = $this->parseDataCurl($url);
        
        view($data);
        
    }
    
    /**
     * Lấy thông tin datafeeds
     */
    
    
    /**
     * 
     */
    
    public function getDatafeeds()
    {
        $url = 'https://api.accesstrade.vn/v1/datafeeds';
        
        $data = $this->parseDataCurl($url);
        
        view($data);
        
    }
    
    public function getOffers()
    {
        $url = 'https://api.accesstrade.vn/v1/offers_informations';
        
        $data = $this->parseDataCurl($url);
        
        return $data;
        
    }
    
    
    
    
    public function importProducts($params)
    {
        
        $promotion_id = $params['promotion_id'];
        $coupon_id = $params['coupon_id'];
        
        $offers = $this->getOffers();
        
        $i = 0;
        
        if(!empty($offers)){
            foreach ($offers as $ip => $offer){
                
                
                
                $item = Yii::$app->frontend->model->findItemByCode($offer['id']);
                
                if(!empty($item)){
//                     $f['expired_date'] = isset($offer['end_time']) && $offer['end_time'] != "" ? strtotime($offer['end_time']) : time() + 30 * 12 * 86400;
                    
//                     $f['started_date'] = isset($offer['start_time']) && $offer['start_time'] != "" ? strtotime($offer['start_time']) : time();
                    
//                     Yii::$app->db->createCommand()->update(\izi\frontend\models\Articles::tableName(), $f, ['id'=>$item['id']])->execute();
                    
                    continue;
                }
                
                //
                
                if($i++ > 9) break;
                
                $f = [
                    
                    'code' => $offer['id'],
                    'type'=>'promotion',
                    'sid'=>__SID__,
                    'created_by'=>isset(Yii::$app->user->id) && Yii::$app->user->id > 0 ? Yii::$app->user->id : 0,
                    'is_active'=>1,
                    'updated_at'=> date('Y-m-d H:i:s')
                ];
                
                
                $f['title'] = trim_space($offer['name']);
                
                
                $aff_url = $offer['aff_link'];
                
                $checksum = md5($offer['aff_link']);
                
                $data_aff = [
                    'checksum'=>$checksum,
                    'original'=>$offer['aff_link'],
                    'title'=>$f['title'],
                    'description'=>'',
                    'sid'=>__SID__,
                    'time'=>time(),
                    'id'=>Yii::$app->slink->model->getId(6),
                ];
                if(Yii::$app->slink->model->validateId($data_aff['id'])){
                    Yii::$app->db->createCommand()->insert(\izi\slink\ShortLinkModel::tableName(),$data_aff)->execute();                    
                }
                
                $aff_url = '/goto/' . $data_aff['id'] . '?delay=0';
                
                
                $f['expired_date'] = isset($offer['end_time']) && $offer['end_time'] != "" ? strtotime($offer['end_time']) : time() + 30 * 12 * 86400;
                
                $f['started_date'] = isset($offer['start_time']) && $offer['start_time'] != "" ? strtotime($offer['start_time']) : time();
                
               
                $biz = [
                    'info' => isset($offer['description']) ? trim($offer['description']) : '',
                    'aff_url'   =>  $aff_url,
                    'original_url'=>$offer['aff_link'],
                    'coupon_code'=>isset($offer['coupon_code']) ? $offer['coupon_code'] : '',
                    'coupons'=>isset($offer['coupons']) ? $offer['coupons'] : '',
                    
                    'promotion_id'=>$offer['id'],
                    'icon'=>$offer['image'],
                    'thumbnail'=>$offer['image'],
                    
                    'list_images'=>[
                        [
                            'image' =>  $offer['image'],
                            'main'  =>  1,
                            'info'  =>  '',
                            'title' =>  $f['title'],
                        ]
                    ]
                ];
                
                
                $content = [
                    'ctab'  =>  [
                        [
                            'title'=>'Chi tiết',
                            'style'=>0,
                            'text'=>
                            
                            '<a class="center" target="_blank" href="'.$aff_url.'"><img src="'.$offer['image'].'" class="mw100"/></a>
<p>&nbsp;</p>'.
                            $offer['content'] . '
<p>&nbsp;</p>
<p class="f16px">Xem chi tiết chương trình <a class="bold f18px " target="_blank" href="'.$aff_url.'"><i class="fa fa-hand-point-right"></i> tại đây.</a></p>
'
                        ]
                    ]
                ];
                
                $f['bizrule'] = json_encode($biz, JSON_UNESCAPED_UNICODE);
                $f['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                
                $f['url'] = unMark($f['title']);
                
                $f['url'] = \app\modules\admin\models\Slugs::getSlug(isset($f['url']) && $f['url'] != "" ? $f['url'] 
                    : unMark($f['title']), (new \app\modules\admin\models\Content())->getID());
                $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
                
                
                 
                Yii::$app->db->createCommand()->insert(\izi\frontend\models\Articles::tableName(), $f)->execute();
                
                $id = Yii::$app->db->lastInsertID;
                
                // Update category
                Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$promotion_id])->execute();
                
                
                
                \app\modules\admin\models\Slugs::updateSlug($f['url'],$id,$f['type'],1,$biz);
                
                
                
                
                
                
                
                
                
                
                
                
            }
        }
        
        return $i;
    }
    
 
    
//     public function getAllPromotion($params){
        
         
//     }
    
//     public function getAllPromotion2($params){
//         require_once Yii::getAlias('@common') . "/functions/simple_html_dom.php";
         
//     }
    
    
    
    
    
}