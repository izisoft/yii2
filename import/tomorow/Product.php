<?php
namespace izi\import\tomorow;
use Yii;

class Product extends \yii\base\Component
{
    
    public function getNewsLink($url)
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $content = $html->find('.lindo_news');
        
        if(!empty($content)){
            foreach ($content as $li){
                
                $href = "#";
                
                $a = $li->find('a',0);
                if(!empty($a)){
                    $href = $a->href;
                }
                
                $title = $li->find('h3',0)->plaintext;
                
                $info = $li->find('.title-news p',0) -> plaintext;
                
                $icc = $li->find('.anh-new img',0);
                
                if($href != "#"){
                    $data[] = [
                        'href' => $href,
                        'title' => $title,
                        'info' => $info,
                        'icon' => !empty($icc) ? $icc->src : '',
                    ];
                }
                
            }
        }  
        
        return $data;
    }
    
    public function getProductLink($url)
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $content = $html->find('ul.products',0);
        
        if(!empty($content)){
            foreach ($content->find('.tour-sale-item') as $li){
                $a = $li->find('a',0);
                if(!empty($a)){
                    $data[] = $a->href;
                }
            }
        }
         
        
        return $data;
    }
    
    public function parseData($url)
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $images = $html->find('img');
        
        if(!empty($images)){
            foreach ($images as $image){
                $toRemove = null;
                
                //view($image->getAllAttributes (),1,1);
                
                foreach ($image->getAllAttributes() as $attr => $val) {
                    if (!in_array($attr, ['src', 'alt', 'class','id'])) {
                        $toRemove[] = $attr;
                    }
                }
                
                if ($toRemove) {
                    foreach ($toRemove as $attr) {
                        $image->removeAttribute($attr);
                    }
                }
            }
            
        }
        
        $html->save();
        
        
        $content = $html->find('.single-product',0);
        
//         view($content->find('h1.product_title',0)->plaintext);
        
        $data['title']  =  $content->find('h1.product_title',0)->plaintext;
        
        $priceElm = $content->find('.price ins .woocommerce-Price-amount',0);
        
        if(empty($priceElm)){
            $priceElm = $content->find('.price .woocommerce-Price-amount',0);
        }
        
        $data['price2'] = str_replace([','],'', $priceElm->plaintext);
        
        if ( preg_match ( '/([0-9]+)/', $data['price2'], $matches ) )
        {
            $data['price2'] = ($matches[0]);
        }
        
        
        
        $priceElm = $content->find('.price del .woocommerce-Price-amount',0);
        
        if(!empty($priceElm)){
            $data['price1'] = str_replace([','],'', $priceElm->plaintext);
            
            if ( preg_match ( '/([0-9]+)/', $data['price1'], $matches ) )
            {
                $data['price1'] = ($matches[0]);
            }
        }
                        
        
         
        
        if ( preg_match_all ( '/([0-9]+)/', preg_replace('/\D/', ' ', unMark($data['title'])) , $matches ) )
        {
              
           
            $data['day'] = ($matches[0][0]);
            
            if(isset($matches[0][1])){
                $data['night'] = ($matches[0][1]);
            }
            
        }
        
        if(!isset($data['day'])){
            
            $x = $content->find('.sidebar .detail .tt',0);
            
            if(!empty($x)){
                 
                
                $pt = '/\d+ Ngày – \d+ Đêm/';
                
                preg_match ($pt, $x->plaintext , $matches );
                
                if(!empty($matches)){
                    preg_match_all ( '/([0-9]+)/', preg_replace('/\D/', ' ', unMark($matches[0])) , $matches );
                    
                    $data['day'] = ($matches[0][0]);
                    
                    if(isset($matches[0][1])){
                        $data['night'] = ($matches[0][1]);
                    }
                }
            }
            
        }
         
        
        // khởi hành
        
        $time = $content->find('.sidebar .detail .tt',0);
        
        $a = $time->find('.row',0);
        
        $a -> find('strong',0)->innertext = null;
        
        $t = explode(':', $a->plaintext);
        
        if(isset($t[1])){
            $data['departure_type'] = trim($t[1]);
        }else{
            $data['departure_type'] = trim($t[0]);
        }
        
        
        
        switch (unMark($data['departure_type'])) {
            case 'hang-ngay':
            case 'moi-ngay':
            case 'hang-ngay-tat-ca-cac-ngay-trong-tuan':
            
                $data['departure_type'] = 1;
                break;
                
                
            case 'thu-2-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [1];
                
                break;
            case 'thu-3-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [2];
                
                break;
            case 'thu-4-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [3];
                
                break;
            case 'thu-5-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [4];
                
                break;
            case 'thu-6-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [5];
                
                break;
            case 'thu-7-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [6];
                
                break;
                
            case 'chu-nhat-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [0];
                
                break;
            case 'thu-2-va-thu-6-hang-tuan':
            case 'thu-2-thu-6-hang-tuan':
                $data['departure_type'] = 2;
                
                $data['departure_selected'][$data['departure_type']] = [1,5];
                
                break;    
                
                
            
            default:
                $data['departure_type'] = 0;
                break;
        }
        
         
        // Khoi hanh tu
        $a = $time->find('.row',1);
        $t = explode(':', $a->plaintext);
        
        if(isset($t[1])){
            $data['departure_start_text'] = trim($t[1]);
        }else{
            $data['departure_start_text'] = trim($t[0]);
        }
        
        
        // Lich trinh tom tat
        // short_itinerary
        $short_itinerary = $content->find('.woocommerce-product-details__short-description',0);
        if(!empty($short_itinerary)){
            $data['short_itinerary'] = trim($short_itinerary->innertext);
        }
        
        
        // phuong tiện
//         $a = $content->find('p.truonghienthisanpham',2);
//         $a -> find('strong',0)->innertext = null;
        
//         $t = explode(':', $a->plaintext);
        
//         if(isset($t[1])){
//             $data['full_text_vehicle'] = trim($t[1]);
//         }else{
//             $data['full_text_vehicle'] = trim($t[0]);
//         }
        
        // Info
        $a = $time->find('.row',2);
        $t = $a->find('div > p',0);
        
        if(!empty($t)){
            $data['info'] = trim($t->innertext);
        }
         
        
        $img = $content->find('.woocommerce-product-gallery__wrapper img',0);
        
        if(!empty($img)){
            $fd = __SITE_NAME__ . "/images/" . date('Y/m');
            $icon = $img->src;
            $data['icon'] = Yii::file()->copyRemoteFile($icon, $fd);
            
            $data['list_images']    =  [
                [
                    'title' =>  $data['title'],
                    'info'  =>  '',
                    'image'=>$data['icon'],
                    'main'=>1,
                    
                ],
            ];
            
        }else{
            $data['icon'] = '';
        }
         
        
        // tabs tab tablindo_pro
        $tabs = $content->find('.tab.tablindo_pro',0);
        
        $tabs_detail = $tabs_detail2 =[];
        
        if(!empty($tabs)){
            $i = 0;
            foreach ($tabs->find('a') as $t){
                
                $tabs_detail[$i]['before'] = [];
                $tabs_detail[$i]['title'] = $t->plaintext;
                
                $tabs_detail[$i]['is_active'] = 'on';
                
                $tabs_detail2[$i]['title'] = $t->plaintext;
                
                $tabs_detail2[$i]['type'] = $t->plaintext == 'Lịch trình tour' ? 'program' : "text";
                
                $tabs_detail[$i++]['type'] = $t->plaintext == 'Lịch trình tour' ? 'program' : "text";
                
                
            }
        }
        
       
        
        $tabs = $content->find('.tab_item',0);              
        
        $replaces = [
//             ' XSIM ' => ' Sim Vàng ',
//             'XSIM.vn' => 'Simvang.vn',
//             'XSIM'      => 'Sim Vàng'
'https://tomorrow.vn/' => '/',
            'https://tomorrow.vn' => '',
        ];
        
        $z = $i;
        if(!empty($tabs)){
            $i = 0;
            for($i= 0; $i<$z;$i++){
                
                $t = $tabs->find("#tab".($i+1), 0);
                
                $contentText = str_replace(array_keys($replaces), array_values($replaces), trim($t->innertext() ));
                
                $pattern = '/< *img[^>]*src *= *["\']?([^"\']*)/i';
                
                preg_match_all($pattern, $contentText, $m);
                
                
                if(isset($m[1]) && !empty($m[1])){
                    $rpl = [];
                    foreach ($m[1] as $src){
                        $rpl[] = Yii::file()->copyRemoteFile($src, $fd);
                    }
                    
                    
                    $contentText = str_replace($m[1], $rpl, $contentText);
                }
                
                if($tabs_detail[$i]['type'] == 'program'){
                    $tabs_detail[$i]['text'] = '';
                    
                    
                    $tabs_detail[$i]['program']['before'] = [
                        ['title' => '', 'is_active' => 'on', 'text' => $contentText]
                    ];
                }else{
                
                    $tabs_detail[$i]['text'] = $contentText;
                }
            }
        }
        
        
        $data['tabs_title'] = $tabs_detail2;
        $data['tabs'] = $tabs_detail;
       
         
        
//         // content
//         $contentHtml = $content->find('.woocommerce-Tabs-panel.woocommerce-Tabs-panel--description',0);
                
//         $data['detail'] = trim( str_replace('<h2>Mô tả</h2>', '',$contentHtml->innertext() ));  
        
        return $data;
    }
    
    
    public function getNewsData($url, $params = [])
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        foreach($html ->find('.kksr-legend') as $item) {
            $item->outertext = '';
        }
        
        $images = $html->find('img');
        
        if(!empty($images)){
            foreach ($images as $image){
                $toRemove = null;
                
                //view($image->getAllAttributes (),1,1);
                
                foreach ($image->getAllAttributes() as $attr => $val) {
                    if (!in_array($attr, ['src', 'alt', 'class','id'])) {
                        $toRemove[] = $attr;
                    }
                }
                
                if ($toRemove) {
                    foreach ($toRemove as $attr) {
                        $image->removeAttribute($attr);
                    }
                }
            }
            
        }
        
        $as = $html->find('a');
        
        if(!empty($as)){
            foreach ($as as $a){
                $toRemove = null;
                
                $href = $a->href;
                
                $href = preg_replace("/https:\/\/tomorrow.vn\//", "$1", $href);
                $new_href = preg_replace("/https:\/\/tomorrow.vn(.*)\//", "$1", $href);
                $a->href = $new_href;
            }
            
        }
        
        $ls = $html->find('.kk-star-ratings',0);
        if(!empty($ls)){
            $ls->innertext = '';
        }
        
        
        $html->save();
        $content = $html->find('.dtour-news',0);
        
        
        
        if(!!empty($content)) return false;
        
        
        
        
        
        if(isset($params['icon']) && $params['icon'] != ""){
            $icon = $params['icon'];
        }else{
            
            $image = $content->find('img',0);
            if(!empty($image))
            $icon = $image->src;
            else $icon = '';
        }
        
        $fd = __SITE_NAME__ . "/images/" . date('Y/m');
        if ($icon != ""){
            $icon = Yii::file()->copyRemoteFile($icon, $fd);
        }else{
            $icon = '';
        }
        
        $biz['icon'] = $icon;
            
        $title = $html->find('.entry-title',0);
        
        
        
        
        
        $replaces = [
            ' XSIM ' => ' Sim Vàng ',
            'XSIM.vn' => 'Simvang.vn',
            'XSIM'      => 'Sim Vàng'
        ];
        
        
        $data['title'] = str_replace(array_keys($replaces), array_values($replaces), trim_space($title->plaintext));
        
        if(isset($params['info']) && $params['info'] != ""){
            $biz['info'] = $params['info'];
        }else{
            $description = $html->find('meta[name="og:description"]',0);
            if(!empty($description)){
                $biz['info'] = str_replace(array_keys($replaces), array_values($replaces), trim_space(\yii\helpers\Html::decode($description->content)));
            }else{
                $biz['info'] = isset($params['info']) ? $params['info'] : '';
            }
        }
        
        
        
        $biz['target'] = '_self';
        
        
        
        // content
        $contentHtml = $content->find('.dtour-news-main',0);
        
        
        
        if(!empty($contentHtml)){
            //content[ctab][0][title]
            
            $contentText = str_replace(array_keys($replaces), array_values($replaces), trim($contentHtml->innertext() ));
            
            $pattern = '/< *img[^>]*src *= *["\']?([^"\']*)/i';
            
            preg_match_all($pattern, $contentText, $m);
            
            
            if(isset($m[1]) && !empty($m[1])){
                $rpl = [];
                foreach ($m[1] as $src){
                    $rpl[] = Yii::file()->copyRemoteFile($src, $fd);
                }
                
                
                $contentText = str_replace($m[1], $rpl, $contentText);
            }
            
            
            
            $ctab = [
                [
                    'title' => 'Chi tiết' ,
                    'style'=>0 ,
                    'text' => $contentText
                    . ''
                    ,
                    
                ]
            ];
            
            $data['content'] = json_encode(['ctab' => $ctab], JSON_UNESCAPED_UNICODE);
            
        }else{
            $data['content'] = '';
        }
        
        
        $data['bizrule'] = json_encode($biz, JSON_UNESCAPED_UNICODE);
        
        return $data;
    }
    
    
    
    
}