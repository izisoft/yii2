<?php
namespace izi\router;

use Yii;
 

class BackendUrlManager extends BaseUrl
{
    
    public function beforeRequest($request){
        
        $modules = array_keys(Yii::$app->modules);
        
        $pattern = '/('.str_replace('/','\\/',implode('|', $modules)).')\/?([\w\/\-\+]+)?/i';
        
        preg_match($pattern, trim(URL_PATH, DS), $modules);
        
        if(!empty($modules)){
            
            $fp = Yii::getAlias(implode('/', [
                "@module",
                $modules[1],
                'config',
                'UrlRule.php'
            ]));
                        
            if(file_exists($fp)){
                $this->addRules(require $fp);
            }
        }
        
    }
    
    public function parseRequest($request)
    {        
        
        $this->beforeRequest($request);
        
        $parentRequest = parent::parseRequest($request);
               
        $this->setLanguage();
        
        return $parentRequest;
    }
    
    
    public function setLanguage()
    {
        defined('ROOT_LANG') or define("ROOT_LANG",'vi-VN');
        
        defined('SYSTEM_LANG') or define("SYSTEM_LANG",ROOT_LANG);
        
        $key = md5('config_language_' . Yii::$app->id);
        
        $lang = Yii::$app->session->get($key, []);
        
        if(empty($lang)){
            
            if(defined('DOMAIN_LANGUAGE') && DOMAIN_LANGUAGE != ""){
                $lang = Yii::$app->l->getItem(DOMAIN_LANGUAGE, true);
            }else{
                $lang = Yii::$app->l->getDefault();
            }
            
            
            if(!empty($lang)){
                Yii::$app->session->set($key, $lang);
            }
        }
        
        
        if(!empty($lang)){
            $language = $lang['code'];
        }else{
            $language = Yii::$app->l->initDefaultLanguage();
            
        }
        
        defined('__LANG__') or define("__LANG__", $language);
        
        defined('ADMIN_LANG') or define("ADMIN_LANG",__LANG__);
        
    }
}
