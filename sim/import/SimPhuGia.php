<?php
namespace izi\sim\import;
use Yii;


class SimPhuGia extends BaseImport
{
 
    private $supplier = 'phugia', $partner_id = 1498 ;
    
    private $context, $username, $password;
    
    private $urls;
    
    private $domain = 'http://simphugia.com';
    
    private $_cacheFolder, $_supplierFolder; 
    
    public function init()
    {
        
        $cus = Yii::$app->customer->model->getItem($this->partner_id);
        
        $this->supplier = $cus['code']; 
        
//         $this->username = $username = 'sim';
//         $this->password = $password = 'giagoc';
        
//         $this->context = stream_context_create(array(
//             'http' => array(
//                 'header'  => "Authorization: Basic " . base64_encode("$username:$password")
//             )
//         ));
        
        $this->urls = [
            'Sim-So-Dep-Viettel.html',
            'Sim-So-Dep-MobiFone.html',
            'Sim-So-Dep-VinaPhone.html',
            'Sim-So-Dep-VietNamobile.html',
        ];
        
        parent::init();
    }
     
     
    
    public function setUrlQuery($params)
    {
//         $url_query = $this->domain . '/'.$params['query'] . '?trang=' .$params['page'];
        
        $url_query = $this->domain . '/'.$params['query'] . '?page=' .$params['page'];     
        
        return $url_query;
    }
    
    
    
    public function getAllProducts($params = [])
    {
        $random_index = isset($params['index']) ? $params['index'] : ( getParam('index', rand(0,count($this->urls)-1)));
        
        
        $q = $this->urls[$random_index];
        
        $stop = false; $index = 0;
        
        $params = $this->buildParams([
            //'page' => getParam('p', 1),
            'query' =>  $q,
            'group_id'=>$random_index,
            'partner_id'=>$this->partner_id
        ]);
      
        $page = $params['page'];
        
        while(!$stop){
            
            
            $url_query = $this->setUrlQuery(['query' => $q, 'page' => $params['page']]);
            
            $cache_id = md5($url_query);
            
            $st = $this->validateCached($cache_id);
            
            if(!$st['state']){
                
                $data = $this->getProductsFromUrl($url_query);
                
                if($index++ > 25)
                {
                    $stop = true;
                }
                
                if(!empty($data)){
                    $this->storeCache($cache_id, $this->partner_id, $data, $params);
                    $params['page'] ++;
                    
                }else{
                    $stop = true;
                }
                
            }else{
                $params['page'] ++;
            }
        }
        
        if($page == $params['page']){
            if(($page = $this->removeExpiredCache()) > 0){
                $params['page'] = $page;
            }
        }
        
        $this->setLog($params);
        return $params;
    }
    
    public function getProductsFromUrl($url)
    {
        
        
        $html = @str_get_html( file_get_contents($url) );        
        
        if(!$html){
            
            $proxy = Yii::$app->sim->import->getProxy();
            
            
            $html = get_web_page($url, [
                //'username' => $this->username,
                //'password' => $this->password,
                //'proxy'     =>  $proxy['ip'],
                //'proxy_port'     =>  $proxy['port'],
            ]);           
            
            if(isset($html['content'])) {
                
                
                $html = @str_get_html($html['content']);
            }
            
        }
        
        if(empty($html)) return [];
         
        
        $content = $html->find('#left .panel-default table',0);
         
        $data = [];
        
        if(!empty($content)){
            foreach ($content->find('tr') as $tr){
                $a = $tr->find('a',0);
                
                
                if(!empty($a)){
                    
                    $sim['display'] = trim(trim_space($a->plaintext), '.,');
                    
                    $sim['id'] = Yii::$app->sim->getSimId($sim['display']);
                    
                    if(!(strlen($sim['id']) == 9)){
                        continue;
                    }
                    
                    $td2 = $tr->find('td',2);
                    
                    $price = -1;
                    
                    
                    if(empty($td2)){
                        $price = $this->getSinglePrice($sim['id']);
                        
                    }else{
                        $price = str_replace([',', '.'],'', $tr->find('td',2)->plaintext);
                    }
                    
                    if ( preg_match ( '/([0-9]+)/', $price, $matches ) )
                    {
                        $price = ($matches[0]);
                    }
                    
                    $sim['price1'] = $price;
                    
                    $price = Yii::$app->sim->getAgentPriceFromSellPrice($price, ['partner_id' => $this->partner_id]);
                    
                    if($price < 300000){
                        $price = min(300000,$sim['price1']-40000);
                    }
                    
                    $sim['price'] = (float) $price; 
                    
                    $sim['partner_id'] = $this->partner_id;
                     
                    
                    if($sim['price'] < 800000 && (substr($sim['id'], 0, 1) == 5 || substr($sim['id'], 0, 2) == 92)){
                        continue;
                    }
                    
                    $data [] = $sim;
                }
            }
        }
         
        
        return $data;
        
    }
    
    
    
    public function getSinglePrice($sosim)
    {
        
        return -1;
        
        $sosim = makePhoneNumber($sosim);
        
        $sim_id = preg_replace('/\D/', '', $sosim);
        
        $net = Yii::$app->sim->findNetworkBySim($sosim);
        
        
        $url = $this->domain . '/'.$sosim.'.html';
        
  
        $html = @str_get_html( file_get_contents($url) );
        
        
        $t1 = microtime(true);
        
        $blacklist = Yii::getAlias('@runtime/proxy/blacklist.txt');
        
        if(!empty($html)){
            
        }else{
            
            $proxy = Yii::$app->sim->import->getProxy();
            
            
            $html = get_web_page($url, [
                //'username'      =>  $this->username,
                //'password'      =>  $this->password,
                'proxy'         =>  $proxy['ip'],
                'proxy_port'    =>  $proxy['port'],
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
        
        
        //view($proxy, "Delay : $delay");
        
        if(!$html) {
            
            
            if(isset($proxy['ip'])){
                //writeFile($blacklist, $proxy['ip'] . PHP_EOL, 'a+');
            }
            return -1;
        }
        
//         $tested = Yii::getAlias('@runtime/proxy/tested.txt');
//         writeFile($tested, $proxy['ip'] . PHP_EOL, 'a+');
        
        
        
        
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
                
                //view('Added ' . $proxy['ip'] . ' to blacklist.');
            }
            
        }
        
        $content = $html->find('#ordered table',0);
         
        
        if(!empty($content)){
            
            $p = $content->find('tr',1);
            
            view($p->find('td',0)->find('input',1)->value, __METHOD__);
            
            if(!empty($p)){
                $price = preg_replace('/\D/', '',$p->find('input',1)->value);
                if((int)$price > 0){
                    return (int)$price;
                }
                return 0;
            }else{
                return -1;
            }
        }else{
            
            
//             $content = $html->find('#d_',0);
            
//             if(!empty($content)){
//                 $p = $content->find('input',0);
//                 if(!empty($p)){
//                     $price = preg_replace('/\D/', '',$content->find('input',0)->value);
//                     if((int)$price > 0){
//                         return (int)$price;
//                     }
//                     return 0;
//                 }else{
//                     return -1;
//                 }
//             }
            
//             if(!empty(preg_match('/403 Forbidden/', $html->plaintext))){
//                 return -1;
//             }
            
//             if(!empty(preg_match('/404 Not Found/', $html->plaintext))){
//                 return -1;
//             }
            
            
            $price = -1;
        }
        
        return (int)$price;
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}