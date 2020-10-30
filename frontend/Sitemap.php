<?php 
namespace izi\frontend;

use Yii;

class Sitemap extends \yii\base\Component
{
    
    public $config_key = 'SITEMAP';
    
    
    
    public function getDomain($domain = ''){
        
        if($domain == ''){
            $domains = isset(Yii::$app->cfg->seo['domains']) ? Yii::$app->cfg->seo['domains'] : [];
            
            if(!empty($domains)){
                if(in_array(DOMAIN_NOT_WWW, $domains)){
                    $d = DOMAIN_NOT_WWW;
                }else{
                    $d = $domains[0];
                }
            }else{
                $d = DOMAIN_NOT_WWW;
            }            
            
        }else {
            $d = $domain;
        }
        
        if(strpos($d, '://') === false){
            $s = Yii::$app->cfg->seo;
            
            $w = str_replace('www.', '', $d);
            
            if(isset($s[$w]['ssl']) && $s[$w]['ssl'] == 1){
                $scheme = 'https';
            }else{
                $scheme = SCHEME;
            }
            
            $www = isset($s[$w]['www']) && $s[$w]['www'] == 1 ? 'www.' : '';
            
            $d = $scheme . '://' . $www . $w;
        }
        return $d;
    }
    
    
    public function renderSitemap(){
        // Lấy cấu hình sitemap
        
        $model = new \app\modules\admin\v1\models\Seo2();
        $seo = $model->getItem();
        
        //view($seo);
        
        $domains = $model->getDomains();
         
        
        if(empty($domains)){
            $domains = isset($seo['domain']) && $seo['domain'] != '' ? explode(',', $seo['domain']) : [];
        }
        
        $old = \app\models\SiteConfigs::getConfigs('SEO', null);
        
        $sitemap = [];
        
        
        
        
        foreach ($domains as $domain) {
            
            $domain = str_replace('www.', '', $domain);
            
            $v2 = isset($seo[$domain]) ? $seo[$domain] : (isset($old[$domain]) ? $old[$domain] : (isset($old["www.$domain"]) ? $old["www.$domain"] : null));
                        
            $lastmod = isset($v2['sitemap_option']['lastmod']) ? $v2['sitemap_option']['lastmod'] : '';
            
            if(isset($v2['sitemap_option']['turn_off']) && $v2['sitemap_option']['turn_off'] == 'on'){
                continue;
            }
            
             
            
            $lastmod = '';
            $freq = isset($v2['sitemap_option']['freq']) ? $v2['sitemap_option']['freq'] : '';
            //$priority = isset($v2['sitemap_option']['priority']) ? $v2['sitemap_option']['priority'] : '';
            
            $existed = [$this->getDomain($domain),$this->getDomain($domain) . '/'];
            
            $html = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
         
            
            $www = isset($v2['www']) ? $v2['www'] : (isset($old['www']) ? $old['www'] : -1);
            
            $domainName = $www == 1 ? 'www.' . $domain : $domain;
             
            
            $html .= '
<url>
  <loc>'.$this->getDomain($domainName).'</loc>'.($lastmod != "" ? '
  <lastmod>'.$lastmod.'</lastmod>' : '').''.($freq != "" ? '
  <changefreq>'.$freq.'</changefreq>' : '').'
</url>';
            // lấy toàn bộ link web
            
            $slugModel = (new \app\models\Slugs());
            
            $l = $slugModel->getAllSitemapUrl();
            
            if(!empty($l)){
                foreach ($l as $v){
                    $url = $slugModel->getDirectLink($v['url'], $v['item_id'], $v['item_type'],$domainName);
                    $url = rtrim($url, '/');
                    if(!in_array($url, $existed)){
                        $html .= '
<url>
  <loc>'.$url.'</loc>'.($lastmod != "" ? '
  <lastmod>'.$lastmod.'</lastmod>' : '').''.($freq != "" ? '
  <changefreq>'.$freq.'</changefreq>' : '').'
</url>';
                        $existed[] = $url;
                    }
                    
                }
            }
            
            $html .= '
</urlset>';
            
            
            $sitemap[$domain] = $html;
        }
        
        
        return $sitemap;
    }
    
    
    
    public function updateSitemap()
    {
        $data = $this->renderSitemap();
        
        $conditions = [
            'sid'   =>  __SID__,
            'code'  =>  $this->config_key,
            
        ];
        
        \app\models\SiteConfigs::updateData($data, $conditions, true);
    }
    
    
    
}