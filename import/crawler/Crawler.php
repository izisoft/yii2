<?php
namespace izi\import\crawler;
use Yii;

class Crawler extends \yii\base\Component
{
    
    public $key;
    
    public function init()
    {
        
        if(!Yii::$app->user->can([DEV_USER])){
            Yii::$app->end(0);
        }
       
    }
    
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
        
        
         // ** Bước 4 (tuỳ chọn): Đóng request để giải phóng tài nguyên trên hệ thống
      
        curl_close($ch);
        return $output;
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
        
        return @file_get_contents($url,false,$context);
        
        $l = json_decode(@file_get_contents($url,false,$context),1);
        
        $data = isset($l['data']) ? $l['data'] : [];
        return $data;
    }
    
    public function downloadWebsite($url, $tempCode)
    {
        $files = $this->getStaticFiles($url);
         
        
        $files = array_merge($files['cssFiles'], $files['jsFiles']);
        
        $rs = [];
        
        if(!empty($files)){
            foreach ($files as $file){
                $rs[] = $this->crawlerFile($file, $tempCode);
            }
        }
        
        return $rs;
    }
    
    private function crawlerFile($file, $tempCode, $fixed_dest = true, $replace = false, $rs = []){
        if($file != ""){
            $subject= file_get_contents($file);
            $pattern= "/\burl\([^)]+\)/i";
            
            $path_parts = pathinfo($file);
             
            
            $themePath = $fixed_dest ? Yii::getAlias("@app/themes/$tempCode") : $tempCode;
            
            switch ($path_parts['extension']){
                case 'css' : case 'php':
                    
                    $dest = $fixed_dest ? $themePath .'/assets/css' : $themePath;
                    
                    if(!file_exists($dest)){
                        mkdir($dest, 0755, true);
                    }
                     
                    
                    if(!file_exists($filename = $dest .'/' . $path_parts['filename'] . '.css')){
                        downloadImage($file, $filename);
                        
                    }
                    break;
                case 'js':
                    
                    $dest = $fixed_dest ? $themePath .'/assets/js' : $themePath;
                    
                    if(!file_exists($dest)){
                        mkdir($dest, 0755, true);
                    }
                    
                    if(!file_exists($filename = $dest .'/' . $path_parts['basename'])){
                    
                        downloadImage($file, $filename);
                    
                    }
                    $rs[] = $file;
                    
                    return $rs;
                    
                    break;
            }
            
            $rsurl = dirname($file);
            
            preg_match_all($pattern, $subject,$result);
            
            
            if(!empty($result[0])){
                foreach ($result[0] as $url){
                    $du = $rsurl;
                    $url = str_replace(['url("',"url('",'url(','")',"')", '"', "'",')'], '', $url);
                    if(!validateAbsoluteUrl($url)){
                        
                        $cssPath = $fixed_dest ? $themePath . '/assets/css' : $themePath;
                        
                        $d = substr_count($url, '../');
                        if($d>0){
                            for($i=0;$i<$d;$i++){
                                $du= dirname($du);
                                $cssPath = dirname($cssPath);
                            }
                            $url = str_replace('../', '', $url);
                        }
                        $url = str_replace('//', '/', $url);
                                                 
                        //
                        if(!check_base64_image($url)){
                            
                            //
                            $urlPath = dirname($url);
                            
                            $filename = $du. (substr($url, 0,1) == '/' ? '' : '/'). $url;
                            
                            if(($pos = strpos($filename  , '?')) !== false){
                                $filename = substr($filename, 0, $pos);
                            }
                            
                            $path_parts = pathinfo($filename);
                            if(isset($path_parts['extension'])){
                            
                            switch ($path_parts['extension']){
                                case 'css' : case 'php':
                                      
                                    $filePath = $cssPath . '/' . $url;
                                    
                             
                                    
                                    $tempCode2 = dirname($filePath);                                    
                                   
                                    
                                    $rs = $this->crawlerFile($filename, $tempCode2, false, $replace, $rs);
                                    
                                    break;
                                case 'js':
                                    
                                    break;
                                    
                                default:
                                    
                             
                                    $filePath = $cssPath . '/' . ltrim($url,'/');
                                    
                                    if(!file_exists($path = dirname($filePath))){
                                        mkdir($path,0755,true);
                                    }
                                    
                                    $url = $du. (substr($url, 0,1) == '/' ? '' : '/'). $url;
                                     
                                    
                                    if(!file_exists($filePath)){
                                        downloadImage($url, $filePath);
                                    
                                    }
                                    break;
                            }
                            
                            
                            }
                            
                        }
                    }else{
                        
                    }
                    
                }
            }
        }
        
        
        $rs[] = $file;
        
        return $rs;
    }
    
    public function getStaticFiles($url)
    {
        $data  = $this->parseDataCurl($url);
         
        // match href literally, then use a named group called css
        $regex = "/href=['\"](?P<css>([^'\"]+?\.css)[^'\"]*)/";
        preg_match_all($regex, $data, $matches);
        $css = ($matches['css']);
        
        if(!empty($css)){
            foreach($css as $kc => $cs){
                // 
                                 
                if(substr($cs, 0, 2) == '//'){
                    $cs = "http:$cs";
                }elseif(substr($cs, 0, 5) == 'http:'){
                    
                }elseif(substr($cs, 0, 6) == 'https:'){
                    
                }else{
                    $dir = dirname($url);
                    
                    while(substr($cs, 0, 3) == '../'){
                        $cs = substr($cs, 3);
                        $dir = dirname($dir);
                    }
                    
                    $cs = "$dir/$cs";
                    
                }
                
                if(($pos = strpos($cs  , '?')) !== false){
                    $cs = substr($cs, 0, $pos);
                }
                
                $css[$kc] = $cs;
                
            }
        }
         
        
        
        $regex = "/src=['\"](?P<js>([^'\"]+?\.js)[^'\"]*)/";
        preg_match_all($regex, $data, $matches);
        $js = ($matches['js']);

        if(!empty($js)){
            foreach($js as $kc => $cs){
                //
                
                if(substr($cs, 0, 2) == '//'){
                    $cs = "http:$cs";
                }elseif(substr($cs, 0, 5) == 'http:'){
                    
                }elseif(substr($cs, 0, 6) == 'https:'){
                    
                }else{
                    $dir = dirname($url);
                    
                    while(substr($cs, 0, 3) == '../'){
                        $cs = substr($cs, 3);
                        $dir = dirname($dir);
                    }
                    
                    $cs = "$dir/$cs";
                    
                }
                
                
                if(($pos = strpos($cs  , '?')) !== false){
                    $cs = substr($cs, 0, $pos);
                }
                
                $js[$kc] = $cs;
                
            }
            
            
        }
        
        return [
            'cssFiles'  =>  $css,
            'jsFiles'   =>  $js
        ];
        
    }
    
    
    public function downloadContent($source, $dest, $rs = [])
    {
        if(substr($dest, 0, 1) == '@'){
            $dest = Yii::getAlias($dest);
        }
         
        
        $urls = $this->getUrlContent($source);
        
        if(!empty($urls)){
            foreach ($urls as $url){
                
                
                
                // folder
                if(substr($url, -1) == '/'){
                    
                    $source2 = rtrim($source, '/') .'/' . $url;
                    
                    $dest2 = rtrim($dest, '/') .'/' . $url;
                      
                    
                    $rs = $this->downloadContent($source2, $dest2, $rs);
                    
                }else{
                    // file
                    
                    $url = rtrim($source, '/') .'/' . $url;
                    
                    if(!file_exists($dest)){
                        mkdir($dest, 0755, true);
                    }
                    
                    $path_parts = pathinfo($url);
                    
                    if(isset($path_parts['extension'])){
                        
                        $filename = rtrim($dest, '/') .'/' . $path_parts['basename'];
                        
                        if(!file_exists($filename)){
                        
                            $rs[] = $url;
                            
                            
                            switch ($path_parts['extension']){
                                case 'css' : case 'php':
                                    downloadImage($url, rtrim($dest, '/') .'/' . $path_parts['filename'] . '.css');
                                    break;
                                case 'js':
                                    downloadImage($url, rtrim($dest, '/') .'/' . $path_parts['filename'] . '.js');
                                    break;
                                default:
                                    downloadImage($url, rtrim($dest, '/') .'/' . $path_parts['basename']);
                                    break;
                            }
                        }
                    }
                    
                }
            }
        }

        
        return $rs;
    }
    
    
    public function getUrlContent($url)
    {
        
        
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
 
        $data = [];

        $table = $html->find('table',0);
        
        if(!empty($table)){
        
            foreach($table->find('tr') as $k => $element){
                
                if($k > 2){ 
                
                    foreach($element->find('a') as $a){
                        $data[] =  $a->href;
                    }
                }
            }
        }
        
        return $data;    
        
    }
    
    
    
    
}