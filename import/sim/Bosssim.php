<?php
namespace izi\import\sim;
use Yii;

class Bosssim extends \yii\base\Component
{
    
    private $context, $username, $password;
    
    public $default_proxy;
    
    public $proxy_list = [
    '146.88.51.238' => '80',
    '146.88.51.235' =>	'80',        
    '146.88.51.234' => '80',      
    '165.22.154.157'=>'80', 
//     '91.188.223.137' => '80',
//     '105.19.49.178' =>	'80',        
//     '103.105.197.177' => '80',          
//     '176.10.127.133' =>	'80',        
    '178.128.50.210' => '80',      
//     '103.105.197.68'=>'80',         
//     '103.105.197.67'=>'80', 
//     '103.105.197.66'=>'80', 
//     '103.105.197.179'=>'80', 
    '113.53.230.167'=>'80', 
        '146.88.51.238'=>'80', 
    '212.17.117.221'=>'80', 
    '81.90.189.223'=>'80', 
    '185.34.52.202'=>'80', 
        '146.88.51.234'=>'80',  
        
    ];
    
    public $black_list = [];
    
    public function init(){
        
        $this->default_proxy = $this->proxy_list;
        
//         $list_proxy = '';
        
//         $xs = explode(PHP_EOL, trim($list_proxy));
        
//         $ls = [];
//         foreach ($xs as $ip){
//             if($ip != "")
//             $ls[] = [trim($ip) => '80'];
//         }


// $a2 = [];

// foreach ($this->proxy_list as $k => $ip){
//     $keys = array_keys($ip);
//     if(!in_array($keys[0], array_keys($a2))){
//         $a2[$keys[0]] = '80';
//     }
// }


// $proxyFile = Yii::getAlias('@runtime/proxy/whitelist.json');
// writeFile($proxyFile, json_encode($a2, JSON_PRETTY_PRINT));
        
        $blacklistFile = Yii::getAlias('@runtime/proxy/blacklist.txt');

        $blacklist = [];
        
        if(file_exists($blacklistFile)){
            $bl = explode(PHP_EOL, trim(@file_get_contents($blacklistFile)));
            if(!empty($bl)){
                foreach ($bl as $ip){
                    $blacklist[] = trim($ip);
                }
            }
            
             
        }
        
//         $testedFile = Yii::getAlias('@runtime/proxy/tested.txt');

//         $tested = [];
        
//         if(file_exists($testedFile)){
//             $bl = explode(PHP_EOL, trim(@file_get_contents($testedFile)));
//             if(!empty($bl)){
//                 foreach ($bl as $ip){
//                     $tested[] = trim($ip);
//                 }
//             }
            
             
//         }
        
         
        
        
        $this->black_list = $blacklist;
        
        $proxyFile = Yii::getAlias('@runtime/proxy/whitelist.json');
        
        if(file_exists($proxyFile)){
            $this->proxy_list = json_decode(@file_get_contents($proxyFile),1);             
        }                 
         
        if(count($this->black_list) > 10){
            foreach ($this->black_list as $ip){
                if(isset($this->proxy_list[$ip]))
                    unset($this->proxy_list[$ip]);
            }
            writeFile($proxyFile, json_encode($this->proxy_list, JSON_PRETTY_PRINT));
            writeFile($blacklistFile, '');
        }
        
//         if(!empty($tested)){
//             foreach ($tested as $ip){
//                 if(isset($this->proxy_list[$ip]))
//                     unset($this->proxy_list[$ip]);
//             }
//         }

if(empty($this->proxy_list)){
    $this->proxy_list = $this->default_proxy;
}
        
        $this->username = $username = 'sim';
        $this->password = $password = 'giagoc';
        
        $this->context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$username:$password")
            )
        ));
        
        
    }
    
    public function getProxy()
    {
        
        
     
        $array = array_keys($this->proxy_list);
        
        $k = $array[rand(0, count($array)-1)];
        
        $count = 0;
        
        while (in_array($k, $this->black_list) && $count++ < 20){
            $k = $array[rand(0, count($array)-1)];
        }
        
        return ['ip' => $k ,'port' => isset($this->proxy_list[$k]) && $this->proxy_list[$k]>0 ? $this->proxy_list[$k] : '80'];
    }
    
    public function getProductLink($url, $limit = 0)
    {
        
        $username = 'sim';
        $password = 'giagoc';
        
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$username:$password")
            )
        ));
 
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
       
        $html = str_get_html( file_get_contents($url, false, $this->context) );
        
        
        
        $tr = $html->find('#tables>tr',0);
        
         
        
        $total_records = 10000000;
        
         
        
        $total_page = ceil($total_records/50);
        
        if($total_page>1){
            for($p = 2; $p<$total_page+1;$p++){
                
                if($limit > 0 && $p > $limit){
                    //break;
                }
                
//                 $data[] = str_replace('.html', "-p$p.html", $url);
                $data[] = preg_replace('/page=\d+/', "page=$p", $url);
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
    
    public function getSinglePrice($sosim)
    {
        $sosim = makePhoneNumber($sosim);
        
        $sim_id = preg_replace('/\D/', '', $sosim);
        
        $net = Yii::$app->frontend->simonline->findNetworkBySim($sosim);
        
        
        $url = 'http://bosssim.com/Mua-Sim-So-Dep-'.ucfirst($net['name']).'-'.$sosim.'.html';
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        
        $html = @str_get_html( file_get_contents($url, false, $this->context) );
        
        
        $t1 = microtime(true);
       
        $blacklist = Yii::getAlias('@runtime/proxy/blacklist.txt');
        
        if(!empty($html)){
             
        }else{
            
            $proxy = $this->getProxy();
             
            
            $html = get_web_page($url, [
                'username' => $this->username, 
                'password' => $this->password,
                'proxy'     =>  $proxy['ip'],
                'proxy_port'     =>  $proxy['port'],
            ]);
 
          
            
            if(isset($html['content'])) {
                 
                if(!empty(preg_match('/403 Forbidden/', $html['content']))){
                    return -1;
                }
                
                if(!empty(preg_match('/404 Not Found/', $html['content']))){
                    return -1;
                }
                  
                
                $html = @str_get_html($html['content']);
                
                 
            }else{
         
                writeFile($blacklist, $proxy['ip'] . PHP_EOL, 'a+'); 
                return -1;
            }
        }
        
        $t2 =  microtime(true);
        
        $delay = $t2  - $t1;
        
        //$filename = Yii::getAlias('@runtime/cache/proxy/list1.txt');
        
       // writeFile($filename, "" .  $proxy['ip'] . ': ' . $delay . PHP_EOL, 'a+'); 
         
    
        view($proxy, "Delay : $delay");
        
        if(!$html) {
             
            
            if(isset($proxy['ip'])){
                writeFile($blacklist, $proxy['ip'] . PHP_EOL, 'a+');
            }
            return -1;
        }
        
        $tested = Yii::getAlias('@runtime/proxy/tested.txt');
        writeFile($tested, $proxy['ip'] . PHP_EOL, 'a+'); 
         
        
      
        
        $ig = [
            'Origin DNS error',
            'Error Error',
            'ErrorSomething',
            '500 Internal Server Error',
            'The requested URL could not be retrieved',
            'getElementsByTagName',
            'Cache Access Denied',
            'We\'ve reported it to the team',
            '502 Bad Gateway  502 Bad Gateway',
            'Welcome To Zscaler Directory',
            'ERROR: El URL solicitado no se ha podido',
            'An internal server error occurred',
            
        ];
         
        
        if(!empty(preg_match('/('.implode(')|(', $ig).')/', $html->plaintext))){
            
            if(isset($proxy['ip'])){
                writeFile($blacklist, $proxy['ip'] . PHP_EOL, 'a+');
                
                view('Added ' . $proxy['ip'] . ' to blacklist.');
            }
            
        }
        
        $content = $html->find('#d_'.$sim_id,0);
       
        
        if(!empty($content)){
        
            $p = $content->find('input',0);
            
            if(!empty($p)){
                $price = preg_replace('/\D/', '',$content->find('input',0)->value);
                if((int)$price > 0){
                    return (int)$price;
                }
                return 0;
            }else{
                return -1;
            }
        }else{
             
            
            $content = $html->find('#d_',0);
            
            if(!empty($content)){
                $p = $content->find('input',0);
                if(!empty($p)){
                    $price = preg_replace('/\D/', '',$content->find('input',0)->value);
                    if((int)$price > 0){
                        return (int)$price;
                    }
                    return 0;
                }else{
                    return -1;
                }
            }
            
            if(!empty(preg_match('/403 Forbidden/', $html->plaintext))){
                return -1;
            }
            
            if(!empty(preg_match('/404 Not Found/', $html->plaintext))){
                return -1;
            }
            
            
            $price = -1;
        }
        
        return (int)$price;
        
    }
    
    public function getData($url, $params = [])
    {
        
        
        $filename = md5($url);
        
        $cache_file  = Yii::getAlias('@runtime/cache/simonline/boss/'.$filename .'.json');
        
        $cached = isset($params['cached']) && !$params['cached'] ? false : true;
        
        $cached = false;
        
        if($cached && file_exists($cache_file) && filectime($cache_file) < time() - 86400){
            
            $d = @file_get_contents($cache_file);
            
            if($d !== false){
                $d = json_decode($d, 1);
                if(!empty($d)) return $d;
            }            
        }
        
        $data = [];
        
         
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        
        $html = @str_get_html( file_get_contents($url, false, $this->context) );
 
        if(!$html){
            
            $proxy = $this->getProxy();
            
            
            $html = get_web_page($url, [
                'username' => $this->username,
                'password' => $this->password,
                'proxy'     =>  $proxy['ip'],
                'proxy_port'     =>  $proxy['port'],
            ]);
            
            if(isset($html['content'])) {
                
                
                $html = @str_get_html($html['content']);
            }
         
        }
        
        if(empty($html)) return [];
        
        $content = $html->find('#tables',0);
        
        $trs = $html->find('#tables>tr');
        
        
        if(!empty($content)){
            foreach ($content->find('tr') as $tr){
                $a = $tr->find('a',0);
                 
                
                if(!empty($a)){
                    
                    $so['id'] = ltrim(preg_replace('/[\D]/', '', trim_space($a->href)), '0');
                    
                    $td2 = $tr->find('td',2);
                    
                    if(empty($td2)){
                        
                        $price2 = $this->getSinglePrice($so['id']);
                                              
                    }else{
                        $price2 = str_replace([',', '.'],'', $tr->find('td',2)->plaintext);
                    }
                    
                                                            
                    
                    $so['display'] = str_replace([' '], '', trim_space($a->plaintext));
                                        
                    
                    if ( preg_match ( '/([0-9]+)/', $price2, $matches ) )
                    {
                        $price2 = ($matches[0]);
                    }
                    
                    $so['price'] = (float) $price2; 
                    
                    $so['price2'] = Yii::$app->frontend->simonline->getSalePrice($price2);
                    
                    $so = array_merge($so, Yii::$app->frontend->simonline->getSiminfo($so['display']));
                    
                    
                    $data[] = $so;
                }else{
                    $a = $tr->find('.simso',0);
                    if(!empty($a)){
                        
                        $so['id'] = ltrim(preg_replace('/[\D]/', '', trim_space($a->plaintext)),'0');
                        
                        $so['display'] = str_replace([' '], '', trim_space($a->plaintext));
                         
                         
                        $price2 = str_replace([',', '.'],'', $tr->find('td',2)->plaintext);
                        
                        if ( preg_match ( '/([0-9]+)/', $price2, $matches ) )
                        {
                            $price2 = ($matches[0]);
                        }
                         
                        
                        $so['price'] = (float) $price2;
                        
                        $so['price2'] = Yii::$app->frontend->simonline->getSalePrice($price2);
                        
                        $so = array_merge($so, Yii::$app->frontend->simonline->getSiminfo($so['display']));
                        
                        $so['network_label'] = $tr->find('td',3)->plaintext;
                        
                        $so['category_label'] = $tr->find('td',4)->plaintext;
                        
                        $so = array_merge($so, Yii::$app->frontend->simonline->getSiminfo($so['display']));
                        
                        $data[] = $so;
                    }else{
                        continue;
                    }
                     
                }
            }
        }                
        
       // writeFile($cache_file, json_encode($data,JSON_UNESCAPED_UNICODE));
        
        return $data;
    }
}