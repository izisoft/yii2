<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\ads;
use Yii;
class Ads extends \yii\base\Component
{
    public $blocked_cookie_name = 'ads_cookie_blocked';
    private $_model;
    
    private $_state;
    
    
    public function getState (){ 
        if($this->_state === null){
            $status = isset(Yii::$app->settings['ads_blocked_my_ip']) && Yii::$app->settings['ads_blocked_my_ip']  ? 1 : 0;
            
            $this->_state = $status == 1 ? false : true;
        }
        return $this->_state;        
    }
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\ads\models\Ads']);
        }
        return $this->_model;
    }
    
    private $_advert;
    public function getAdvert(){
        if($this->_advert === null){
            $this->_advert = Yii::createObject(['class'=>'izi\ads\models\Advert']);
        }
        return $this->_advert;
    }
    
    public function show($ads, $allow_html = false, $display = true){
        $text = '';
        if($allow_html){
            if($this->validateAllowAds()){
                $text = "<div data-show=\"1\" class=\"ads-container\">$ads</div>";
            }
        }else{
            $a = $this->getModel()->getAdsById($ads);
            if(!empty($a)){
                $text = "<div data-show=\"1\" class=\"ads-container\">".uh($a->text,2)."</div>";
            }
        }
        if($display){
            echo $text;
        }else{
            return $text;
        }
        return null;
    }
    
    public function getAds($ads, $allow_html = false){
        if($allow_html){
            if($this->validateAllowAds()){
                return "<div data-show=\"1\" class=\"ads-container\">$ads</div>";
            }
        }else{
            return "<div data-show=\"1\" class=\"ads-container\">".uh($this->getModel()->getAdsById($ads)->text,2)."</div>";
        }
        return null;
    }
    
    public function validateAllowAds(){
        //
        if(YII_DEBUG) return true;
        //
        if($this->getState()){ 
            return $this->validateAllowIp();
        }
        return ($this->validateAllowIp() && $this->validateAllowBrowser() ? true : false);
    }
    
    public function validateAllowIp(){
        $ip = getClientIP();
        $ckp = "ckip_" . unMark($ip,'');
        
        $session = Yii::$app->session;
        $initIp = $session->get('initBlockedIp');
        
        if($initIp !== true){
            $l = ($this->getListBlockedIp());
            if(!empty($l)){
                $cookies2 = Yii::$app->response->cookies;
                foreach ($l as $ip){
                    $ckp = "ckip_" . unMark($ip,'');
                    $cookies2->add(new \yii\web\Cookie([
                        'name' => $ckp,
                        'value' => true,
                    ]));                    
                }
            }
            $session->set('initBlockedIp',true);
        }
        
        $cookies = Yii::$app->request->cookies;
        $ip_checked = $cookies->getValue($ckp,false);
        
        if(!$ip_checked){
            
        }
        
        return !$ip_checked;
    }
    
    public function validateAllowBrowser(){
        $cookies = Yii::$app->request->cookies;
        $ads_blocked = $cookies->getValue($this->blocked_cookie_name,false);
        return !$ads_blocked;
    }
    
    public function getListBlockedIp(){ 
        $fp = Yii::getAlias('@runtime/ads/blacklist/' . __SID__ . '.log');
        $data = @file($fp); // chuyển đổi file sang mảng
        $r = [];
        if(!empty($data)){
            foreach ($data as $value) {
                $r[] = trim($value);
            }
        }
        
        return $r;
    }
    
    private function checkStateIpBlocked(){
        $status = isset(Yii::$app->settings['ads_blocked_my_ip']) && Yii::$app->settings['ads_blocked_my_ip'] == 1 ? 1 : 0;
        
        return $status == 1 ? true : false;
    }
    
    public function initIpBlocked(){      
        $ip = getClientIP();
        $session = Yii::$app->session;
        $initIp = $session->get('initBlockedIp');
        
        if($initIp !== true){
            $l = ($this->getListBlockedIp());
            if(!empty($l)){
                $cookies2 = Yii::$app->response->cookies;
                foreach ($l as $ip2){
                    $ckp = "ckip_" . unMark($ip2,'');
                    $cookies2->add(new \yii\web\Cookie([
                        'name' => $ckp,
                        'value' => true,
                    ]));                    
                }
                if($ip == $ip2){
                    $cookies2->add(new \yii\web\Cookie([
                        'name' => $this->blocked_cookie_name,
                        'value' => true,
                    ]));
                }
            }
            $session->set('initBlockedIp',true);
        }
        
        if(!Yii::$app->user->isGuest && $this->checkStateIpBlocked()){
            $fp = Yii::getAlias('@runtime/ads/blacklist/' . __SID__ . '.log');
            // Blocked cookies
            $cookies = Yii::$app->request->cookies;
            
            
            
            $ads_blocked = $cookies->getValue($this->blocked_cookie_name,false);
            if(!$ads_blocked){
                $cookies2 = Yii::$app->response->cookies;
                
                // add a new cookie to the response to be sent
                $cookies2->add(new \yii\web\Cookie([
                    'name' => $this->blocked_cookie_name,
                    'value' => true,
                ]));
            }
            
            
            $ckp = "ckip_" . unMark($ip,'');
            $ip_checked = $cookies->getValue($ckp,false);
            
            
            
            if(!$ip_checked){
                if(!isset($cookies2)){
                    $cookies2 = Yii::$app->response->cookies;
                }
                $cookies2->add(new \yii\web\Cookie([
                    'name' => $ckp,
                    'value' => true,
                ]));
                
                $l = ($this->getListBlockedIp());
                if(!in_array($ip, $l)){
                    $ip .= PHP_EOL;
                    $str = <<<EOF
$ip
EOF;
                    writeFile($fp, $str, 'a');
                }
                
            }
        

        }
    }
    
}