<?php
namespace izi\import\qm;
use Yii;

class Qm extends \yii\base\Component
{

    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\import\qm\Model']);
        }
        return $this->_model;
    }
    
    public function updateStaticFiles(){
        $sid = 7;
        $l = (new \yii\db\Query())->from('articles')->where(['and',[
            'like','bizrule','%_upload_/%',false
        ],['sid'=>$sid]])->limit(10);
        
        view($l->createCommand()->getRawSql());
        
        //view($l->all());
    }
    private $_db;

    public function getDb(){
        if($this->_db === null){
            $this->_db = Yii::createObject([
                'class' => 'izi\db\Connection',
                'dsn' => 'mysql:host='.dString('WXBtZFdYQnRaR3h2WTJGc2FHOXpkQT09').';dbname=database_qmbk',
                'username' => dString('M0g3WU0wZzNXV1JoZEdGaVlYTmxYMnh1ZG00PQ=='),
                'password' => dString('SWtTb1NXdFRiMU5pUWtKSVUwaFI='),
                'charset' => 'latin1',
            ]);
        }
        return $this->_db;
    }
    /**
    * Update url_link
    */

    public function updateUrl(){
      $l = (new \yii\db\Query())->from('articles')->where(['sid'=>__SID__])
      ->andWhere(['not like','url_link','%.html',false])->all();
      if(!empty($l)){
        foreach($l as $v){
          if(!(isset($v['manual_link']) && $v['manual_link'] == 1)){
            //view($v['url_link']);
            $url = Yii::$app->izi->getUrl($v['url']);
            view($url);
            Yii::$app->db->createCommand()->update('articles',['url_link'=>$url],['id'=>$v['id'],'sid'=>__SID__])->execute();
          }

        }
      }
    }

    /**
     * news
     */

    private function getNewsIcon($item_id){
        $query = "SELECT * FROM images WHERE item_id=$item_id and main=1 and uid=1";
        $v = $this->getDb()->createCommand($query)->queryOne();

        if(!empty($v)){
            return str_replace('../upload/', '//statics.iziweb.vn/e2/', $v['local']) . '/' . $v['name'];
        }
    }

    private function getNewsLang($item_id, $text_id, $category_id,$lang = 'VI'){
        $query = "SELECT * FROM lang_news WHERE pid=$item_id and id=$text_id and type=$category_id and lang='$lang' and uid=1";
        return $this->getDb()->createCommand($query)->queryOne();
    }

    private function getNews(){
        $query = "SELECT * FROM news WHERE active=1 and uid=1 and time>" . strtotime('2016-10-01')
        . "";

        $l = $this->getDb()->createCommand($query)->queryAll();
        /**
         * group: root category
         * type: category
         */

        $rs = [];

        if(!empty($l)){
            foreach ($l as $k => $v){
                $biz = $content = [];
                $rs[$k]['sid'] = 7;
                $rs[$k]['time'] = date("Y-m-d H:i:s", $v['time']);
                $rs[$k]['updated_at']= date('Y-m-d H:i:s');

                // Name
                $t = $this->getNewsLang($v['id'], $v['name'], $v['type'], $v['lang']);
                $rs[$k]['type'] = 'news';
                $rs[$k]['is_active'] = 1;
                $rs[$k]['title'] = $t['name'];
                $rs[$k]['url'] = $t['url'];
                $rs[$k]['url_link'] = '/'. $t['url'];
                $rs[$k]['category_id'] = 0;
                switch ($v['type']){
                    case 392: $rs[$k]['category_id'] = 192;  break;

                    case 439: $rs[$k]['category_id'] = 253;  break;
                    case 486: $rs[$k]['category_id'] = 257;  break;
                    case 485: $rs[$k]['category_id'] = 256;  break;
                    case 487: $rs[$k]['category_id'] = 258;  break;
                    case 440: $rs[$k]['category_id'] = 254;  break;
                    case 488: $rs[$k]['category_id'] = 259;  break;
                    case 489: $rs[$k]['category_id'] = 260;  break;
                    case 490: $rs[$k]['category_id'] = 261;  break;
                    case 491: $rs[$k]['category_id'] = 262;  break;
                    case 492: $rs[$k]['category_id'] = 263;  break;
                    case 493: $rs[$k]['category_id'] = 264;  break;
                    case 531: $rs[$k]['category_id'] = 265;  break;
                    case 502: $rs[$k]['category_id'] = 255;  break;
                    case 504: $rs[$k]['category_id'] = 266;  break;

                }


                $biz['icon'] = $biz['image'] = $this->getNewsIcon($v['id']);

                // Info
                $t = $this->getNewsLang($v['id'], $v['info'], $v['type'], $v['lang']);
                if(!empty($t)){
                    $biz['info'] = uh($t['name']);
                }
                // detail
                $t = $this->getNewsLang($v['id'], $v['detail'], $v['type'], $v['lang']);
                if(!empty($t)){
                    $content['ctab'][0]['title'] = 'Chi tiết';
                    $content['ctab'][0]['style'] = 0;
                    $content['ctab'][0]['text'] = str_replace(['"/upload/'], ['"//statics.iziweb.vn/e2/'], uh($t['name']));
                }


                $rs[$k]['bizrule'] = json_encode($biz);
                $rs[$k]['content'] = json_encode($content);
            }
        }

        return $rs;
    }


    private function getText(){
        $query = "SELECT * FROM news WHERE active=1 and uid=1 and time>" . strtotime('2016-10-01')
        . "";

        $l = $this->getDb()->createCommand($query)->queryAll();
        /**
         * group: root category
         * type: category
         */

        $rs = [];

        if(!empty($l)){
            foreach ($l as $k => $v){
                $biz = $content = [];
                $rs[$k]['sid'] = 7;
                $rs[$k]['time'] = date("Y-m-d H:i:s", $v['time']);
                $rs[$k]['updated_at']= date('Y-m-d H:i:s');

                // Name
                $t = $this->getNewsLang($v['id'], $v['name'], $v['type'], $v['lang']);
                $rs[$k]['type'] = 'news';
                $rs[$k]['is_active'] = 1;
                $rs[$k]['title'] = $t['name'];
                $rs[$k]['url'] = $t['url'];
                $rs[$k]['url_link'] = '/'. $t['url'];
                $rs[$k]['category_id'] = 0;



                $biz['icon'] = $biz['image'] = $this->getNewsIcon($v['id']);

                // Info
                $t = $this->getNewsLang($v['id'], $v['info'], $v['type'], $v['lang']);
                if(!empty($t)){
                    $biz['info'] = uh($t['name']);
                }
                // detail
                $t = $this->getNewsLang($v['id'], $v['detail'], $v['type'], $v['lang']);
                if(!empty($t)){
                    $content['ctab'][0]['title'] = 'Chi tiết';
                    $content['ctab'][0]['style'] = 0;
                    $content['ctab'][0]['text'] = str_replace(['"/upload/'], ['"//statics.iziweb.vn/e2/'], uh($t['name']));
                }


                $rs[$k]['bizrule'] = json_encode($biz);
                $rs[$k]['content'] = json_encode($content);
            }
        }

        return $rs;
    }

    public function importNews(){
        $l = $this->getNews();
        if(!empty($l)){
            foreach ($l as $v){
                if($v['title'] != ""){
                if((new \yii\db\Query())->from('articles')->where(['sid'=>$v['sid'],'url'=>$v['url']])->count(1) == 0){
                    //
                    $id = Yii::$app->zii->insert('articles',$v);
                    // update category
                    if($v['category_id']>0){
                    $category_id =  [$v['category_id']];
                    if(!is_array($category_id)) $category_id = array($category_id);
                    //Yii::$app->db->createCommand()->delete(self::tableToCategorys(),['item_id'=>$id])->execute();
                    if(!empty($category_id)){
                        foreach ($category_id as $c){
                            Yii::$app->db->createCommand()->insert('items_to_category',['item_id'=>$id,'category_id'=>$c])->execute();
                        }
                    }
                    }

                    // Update slug
                    Yii::$app->db->createCommand()->insert('slugs',[
                        'url'=>$v['url'],
                        'item_id'=>$id,
                        'item_type'=>1,
                        'route'=>$v['type'],
                        //'rel'=>'',
                        //'redirect'=>json_encode($redirect),
                        'lang'=>__LANG__,
                        //'checksum'=>$checksum,
                        'sid'=>$v['sid']]
                        )->execute();

                }else{
                    Yii::$app->db->createCommand()->update('articles', ['is_active'=>1],['sid'=>$v['sid'],'url'=>$v['url']])->execute();
                }

                }
            }
        }
    }
}
