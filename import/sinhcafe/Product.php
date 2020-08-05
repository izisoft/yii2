<?php
namespace izi\import\sinhcafe;
use Yii;

class Product extends \yii\base\Component
{
    
    public function getProductLink($url)
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $content = $html->find('ul.products',0);
        
        if(!empty($content)){
            foreach ($content->find('li') as $li){
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
        
        $content = $html->find('article.entry-content',0);
        
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
                        
        
        $time = $content->find('p.truonghienthisanpham',0)->plaintext;
        
        
        if ( preg_match_all ( '/([0-9]+)/', $time, $matches ) )
        {
             
           
            $data['day'] = ($matches[0][0]);
            
            if(isset($matches[0][1])){
                $data['night'] = ($matches[0][1]);
            }
            
        }
        
        // khởi hành
        $a = $content->find('p.truonghienthisanpham',1);
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
        
        // khởi hành
        $a = $content->find('p.truonghienthisanpham',2);
        $a -> find('strong',0)->innertext = null;
        
        $t = explode(':', $a->plaintext);
        
        if(isset($t[1])){
            $data['full_text_vehicle'] = trim($t[1]);
        }else{
            $data['full_text_vehicle'] = trim($t[0]);
        }
        
        // Icon
        $icon = $content->find('.woocommerce-product-gallery__wrapper',0)->find('a',0);
        
//         view($icon,11,1);
        $data['icon'] = $icon->href;
        $data['list_images']    =  [
            [
                'title' =>  $data['title'],
                'info'  =>  '',
                'image'=>$data['icon'],
                'main'=>1,
                
            ],
        ];
        
        // content
        $contentHtml = $content->find('.woocommerce-Tabs-panel.woocommerce-Tabs-panel--description',0);
                
        $data['detail'] = trim( str_replace('<h2>Mô tả</h2>', '',$contentHtml->innertext() ));  
        
        return $data;
    }
}