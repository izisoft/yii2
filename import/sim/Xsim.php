<?php
namespace izi\import\sim;
use Yii;

class Xsim extends \yii\base\Component
{
    
    private $context, $username, $password;
    
    public function init(){

    }
    
    public function getNewsLink($url)
    {                
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
       
        $html = str_get_html( file_get_contents($url) );
        
        $data = [];
        
        $items = $html->find('.posts-wrap article');
        
        
         
        
        if(!empty($items)){
            foreach ($items as $item){
                $href = $item->find('a', 0)->href;
                $data [] = $href;
            }
        }
        
        return $data;
    }
    
    
    public function getProductLink()
    {
        
        $url = 'https://xsim.vn/y-nghia-sim/';
        $url = 'https://xsim.vn/y-nghia-sim/page/2/';
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        
        $html = str_get_html( file_get_contents($url) );
        
        $data = [];
        
        $items = $html->find('.posts-wrap article');
        
        
        
        
        if(!empty($items)){
            foreach ($items as $item){
                $href = $item->find('a', 0)->href;
                $icon = $item->find('img', 0)->src;
                $data [] = [
                    'href' => $href,
                    'icon' => $icon,
                ];
            }
        }
        
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
                
                $href = preg_replace("/https:\/\/xsim.vn\/tag(.*)\//", "$1", $href);
                $new_href = preg_replace("/https:\/\/xsim.vn(.*)\//", "$1", $href);
                $a->href = $new_href;
            }
            
        }
        
        $ls = $html->find('.kk-star-ratings',0);
        if(!empty($ls)){
            $ls->innertext = '';
        }
        
        
        $html->save();
        $content = $html->find('#main article',0);
        
        
        
        if(!!empty($content)) return false;
        
        
        
        
         
        if(isset($params['icon']) && $params['icon'] != ""){
            $icon = $params['icon'];
        }else{
        
            $image = $content->find('img',0);
        
            $icon = $image->src;
        
        } 
        
        $fd = __SITE_NAME__ . "/images/" . date('Y/m');
        $biz['icon'] = Yii::file()->copyRemoteFile($icon, $fd);
 
        
        $title = $html->find('.entry-title',0); 
        
       
        
        $description = $html->find('meta[name="description"]',0);
        
        $replaces = [
            ' XSIM ' => ' Sim Vàng ',
            'XSIM.vn' => 'Simvang.vn',
            'XSIM'      => 'Sim Vàng'
        ];
        
        
        $data['title'] = str_replace(array_keys($replaces), array_values($replaces), trim_space($title->plaintext));
        
        
        
        if(!empty($description)){
            $biz['info'] = str_replace(array_keys($replaces), array_values($replaces), trim_space(\yii\helpers\Html::decode($description->content)));
        }else{
            $biz['info'] = '';
        }
        
        $biz['target'] = '_self';
        
       
        
        // content
        $contentHtml = $content->find('.entry-content',0);
        
        
        
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
    
    
    public function importNews($url, $params = [])
    {
        $category_id = isset($params['category_id']) ? $params['category_id'] : 0;
        
        $categories = isset($params['categories']) ? $params['categories'] : [];
        
        if($category_id > 0 && !in_array($category_id, $categories)){
            $categories[] = $category_id;
        }
        
        $f = $this->getNewsData($url);
        
        if(!empty($f)){
            
            $f['url'] = unMark($f['title']);
            
            $f['type'] = 'news';
            
            $f['sid'] = __SID__;
            
            $f['is_active'] = 1;
            
            $f['time'] = $f['updated_at'] = date('Y-m-d H:i:s');
            
            $f['updated'] = time();
            
            
            $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
            
            
            
            $item = Yii::$app->frontend->model->findItemByUrl($f['url']);
            
  
            
            if(!empty($item)){
                 
                
                if(isset($params['overwrite']) && $params['overwrite'] == true){
                    
                    Yii::$app->db->createCommand()->update(\izi\frontend\models\Articles::tableName(), $f, ['id' => $item['id']])->execute();
                    return 1;
                }
                
                return 0;
            }
            
            Yii::$app->db->createCommand()->insert(\izi\frontend\models\Articles::tableName(), $f)->execute();
            
            $id = Yii::$app->db->lastInsertID;
            
            // Update category
            if(!empty($categories)){
                foreach ($categories as $category_id){
                    if($category_id > 0)
                        Yii::$app->db->createCommand()->insert(\app\modules\admin\models\Content::tableToCategorys(),['item_id'=>$id,'category_id'=>$category_id])->execute();
                }
            }
            
            
            \app\modules\admin\models\Slugs::updateSlug($f['url'],$id,$f['type'],1,$f);
            
            return 1;
        }
        
        return 0;
    }
    
    
    
    
    
    
    
    
    
    
    
    
}