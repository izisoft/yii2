<?php
namespace izi\import\sim;
use Yii;

class SimThangLong extends \yii\base\Component
{
    
    public function getProductLink($url, $limit = 0)
    {
        $data = [$url];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $tr = $html->find('#main>article>table>tr',0);
        
         
        
        $total_records = str_replace([',', '.'],'', $tr->find('td',2)->plaintext);
        
        if ( preg_match ( '/([0-9]+)/', $total_records, $matches ) )
        {
            $total_records = ($matches[0]);
        }
        
         
        
        $total_page = ceil($total_records/50);
        
        if($total_page>1){
            for($p = 2; $p<$total_page+1;$p++){
                
                if($limit > 0 && $p > $limit){
                    //break;
                }
                
                $data[] = str_replace('.html', "-p$p.html", $url);
            }
        }
        
       
        return ['urls' => $data, 'total_records' => $total_records];
    }
    
    
    public function importData($url, $limit = 0, $offset = 0)
    {
        $path = Yii::getAlias('@runtime/cache/simonline/' . ($filename = md5($url)) . '/package.json');
        
        $path2 = dirname($path);
        
        
        
        if(file_exists($path) && !Yii::$app->request->isPost){
            
            
            $existed = true;
            
            
            $package = json_decode(file_get_contents($path), 1);
            
            
            
            
            if(isset($package['data']) && !empty($package['data'])){
            
                $total_records = isset($package['total_records']) ? $package['total_records'] : 0;
                
                if(ceil($total_records/1000) > count($package['data'])){
                    
                }else{
                
                    foreach ($package['data'] as $p){
                        $filename = $path2 . "/$p";
                        if(!file_exists($filename)){
                            $existed = false;
                            break;
                        }
                    }
                }
            }
            
            
            if($existed) return $package;
        }
        
        

        $cpage = 0;
        
        $links = $this->getProductLink($url, $limit);
         
        $urls = $links['urls'];
        
        
        $package = [
            'url' =>    $url,
            'total_records'=>$links['total_records']
        ]; 
        
        
        $block = 0; 
        
        $offset2 = 0;
        
        $last_block = 0;
        
        
        
        
        if(!empty($urls)){
            
            $package['data'] = [
                "p$block.json"
            ];
            
            $data3 = [];
            
            foreach($urls as $offset2 => $url2){
                
                $block = (int)($offset2 / 20);
                
                $xblock = (int)($offset / 1000);
                
                if($block< $xblock){
                    continue;
                }
                
                if($limit > 0 && ($xblock + $limit + 1 < $block)){
                    break;
                }
                
                
                if(file_exists($path2 . "/p$block.json")){
                    if(!in_array("p$block.json", $package['data'])){
                        $package['data'][] = "p$block.json";
                    }
                    $last_block = $block;
                    $data3 = [];
                    continue;
                }

                
                                
                
                $data3 = array_merge($data3, $this->getData($url2));
                
                if($block > $last_block){
                    
                    if($cpage++ > $limit && $limit > 0){
                        break;
                    }
                    
                    writeFile($path2 . "/p$last_block.json", json_encode($data3, JSON_PRETTY_PRINT));
                    
                    if(!in_array("p$last_block.json", $package['data'])){
                        $package['data'][] = "p$last_block.json";
                    }
                    
                    $last_block = $block;
    
                    
                    $data3 = [];
                    
                    continue;
                }
                
                
                
            }
            
             
            
            if(!empty($data3)){
               writeFile($path2 . "/p$last_block.json", json_encode($data3, JSON_PRETTY_PRINT));
               $data3 = [];
               
               if(!in_array("p$last_block.json", $package['data'])){
                   $package['data'][] = "p$last_block.json";
               }
               
               
            }
        }
        
        
        
        writeFile($path, json_encode($package, JSON_PRETTY_PRINT));
         
        $package['first_load'] = 1;
        
        return $package;
        
    }
    
    
    public function getData($url)
    {
        // https://simthanglong.vn/tim-sim/*6969.html
        
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $content = $html->find('.tblsim-res2',0);
        
        
        if(!empty($content)){
            foreach ($content->find('tr') as $tr){
                $a = $tr->find('a',0);
                
                if(!empty($a)){
                    $so['id'] = str_replace([',','.'], '', trim_space($a->href));                                        
                    
                    $so['display'] = trim_space($a->plaintext);
                    
                    $price2 = str_replace([',', '.'],'', $tr->find('td',2)->plaintext);
                    
                    if ( preg_match ( '/([0-9]+)/', $price2, $matches ) )
                    {
                        $price2 = ($matches[0]);
                    }
                    
                    $so['price2'] = $price2;
                    
                    $so['network_label'] = $tr->find('td',3)->plaintext;
                    
                    $so['category_label'] = $tr->find('td',4)->plaintext;
                    
                    $data[] = $so;
                }else{
                    $a = $tr->find('.simso',0);
                    if(!empty($a)){
                        
                        $so['id'] = str_replace([',','.'], '', trim_space($a->plaintext));
                        
                        $so['display'] = trim_space($a->plaintext);
                         
                         
                        $price2 = str_replace([',', '.'],'', $tr->find('td',2)->plaintext);
                        
                        if ( preg_match ( '/([0-9]+)/', $price2, $matches ) )
                        {
                            $price2 = ($matches[0]);
                        }
                        
                        $so['price2'] = $price2;
                        
                        $so['network_label'] = $tr->find('td',3)->plaintext;
                        
                        $so['category_label'] = $tr->find('td',4)->plaintext;
                        
                        $data[] = $so;
                    }else{
                        continue;
                    }
                     
                }
            }
        }                
        
        return $data;
    }
}