<?php
namespace izi\import\kenh14;
use Yii;

class Kenh14 extends \yii\base\Component
{
    
    public function getProductLink($url)
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        // Top news
        $content = $html->find('.klw-fashion-topnews',0);
        
        if(!empty($content)){
            foreach ($content->find('a') as $a){
//                 $a = $li->find('a',0);
                if(!empty($a)){
                    $data[] = getAbsoluteUrl( $a->href, ['domain' => 'http://kenh14.vn/']);
                }
            }
        }
        
        // Top2
        $content2 = $html->find('.knswli-object-list',0);
        
        if(!empty($content2)){
            foreach ($content2->find('a') as $a){
//                 $a = $li->find('a',0);
                if(!empty($a)){
                    $href = getAbsoluteUrl( $a->href, ['domain' => 'http://kenh14.vn/']);
                    if(!in_array($href, $data)){
                        $data[] = $href;
                    }
                    
                }
            }
        }
        
        // Top3
        $content3 = $html->find('.kds-new-stream-wrapper ul.knsw-list',0);
        
        if(!empty($content3)){
            foreach ($content3->find('li.knswli') as $li){
                $a = $li->find('a',0);
                if(!empty($a)){
                    $href = getAbsoluteUrl( $a->href, ['domain' => 'http://kenh14.vn/']);
                    if(!in_array($href, $data)){
                        $data[] = $href;
                    }
                    
                }
            }
        }
        
        return $data;
    }
    
    public function getNewsData($url)
    {
        $data = [];
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $content = $html->find('.klw-new-content',0);
        
        if(!!empty($content)) return false;
        
        $content_header = $html->find('.kbwc-header',0);
        
        $image = $html->find('meta[property="og:image"]',0);
        
        $biz['icon'] = $image->content;
         
        
        $title = $html->find('meta[property="og:title"]',0);
        
        $data['title'] = trim_space(\yii\helpers\Html::decode($title->content));
         
        $description = $html->find('meta[property="og:description"]',0);
        $biz['info'] = trim_space(\yii\helpers\Html::decode($description->content));
        
        $biz['target'] = '_self';
         
        
        // content
        $contentHtml = $content->find('.knc-content',0);
        
        if(!empty($contentHtml)){
            //content[ctab][0][title]
            
            $ctab = [
                [
                    'title' => 'Chi tiết' ,
                    'style'=>0 ,
                    'text' => trim($contentHtml->innertext() )
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
        
        $f['url'] = $url = unMark($f['title']);
        
        $f['type'] = 'news';
        
        $f['sid'] = __SID__;
        
        $f['is_active'] = 1;
        
        $f['time'] = $f['updated_at'] = date('Y-m-d H:i:s');
        
        $f['updated'] = time();
        
        $f['url'] = \app\modules\admin\models\Slugs::getSlug(isset($f['url']) && $f['url'] != "" ? $f['url']
            : unMark($f['title']), (new \app\modules\admin\models\Content())->getID());
        $f['url_link'] = Yii::$app->izi->getUrl($f['url']);
        
       
        
        $item = Yii::$app->frontend->model->findItemByUrl($url);
        
        if(!empty($item)) return 0;
        
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