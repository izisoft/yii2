<?php
namespace izi\import\fplus;
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
        
        $content = $html->find('#content',0);
        $content = $html->find('.summary.entry-summary',0);
        
        $images = $html->find('div.images',0);
         
        
        $data['title']  =  $content->find('h1.product_title',0)->plaintext;
        
        
        
        
        $priceElm = $content->find('.price ins .woocommerce-Price-amount',0);
        
        if(empty($priceElm)){
            $priceElm = $content->find('.price .woocommerce-Price-amount',0);
        }
        
        if(!empty($priceElm)){
        $data['price2'] = str_replace([',','.'],'', $priceElm->plaintext);
        
        if ( preg_match ( '/([0-9]+)/', $data['price2'], $matches ) )
        {
            $data['price2'] = ($matches[0]);
        }
        }else{
            $data['price2'] = 0;
        }
         
        
        
        $priceElm = $content->find('.price del .woocommerce-Price-amount',0);
        
        if(!empty($priceElm)){
            $data['price1'] = str_replace([',','.'],'', $priceElm->plaintext);
            
            if ( preg_match ( '/([0-9]+)/', $data['price1'], $matches ) )
            {
                $data['price1'] = ($matches[0]);
            }
        }
                        
        
         
        
        // Icon
        $icon = $images->find('.wp-post-image',0);
                
        if(!empty($icon)){
            $data['icon'] = $icon->src;
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
            $data['list_images'] = [];
        }
        
        // content
        $contentHtml = $content->find('div[itemprop="description"]',0);
        
        if(!empty($contentHtml)){
                
        $data['detail'] = trim( str_replace('<h2>Mô tả</h2>', '',$contentHtml->innertext() ));
        
        }else{
            $data['detail'] = '';
        }
         
        return $data;
    }
}