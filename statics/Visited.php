<?php 
namespace izi\statics;

use Yii;

class Visited extends \yii\base\Component
{
    
    public $statics;
    
    public function updateVisited(){
        $sesision_id = session_id();
        
        $id = md5(FULL_URL); 
        
        if(substr(__DOMAIN__, 0, 3) == 'dev'){
            return;
        }
        
        if(isset($_SESSION[$sesision_id]['last_viewed'][$id]['time1']) && time()-$_SESSION[$sesision_id]['last_viewed'][$id]['time1'] < 120){
            
        }elseif(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET'){
            $_SESSION[$sesision_id]['last_viewed'][$id]['time1'] = time();
            $i = [
                'ajax',
                'sajax',
                'error',
                'error404',
                'sitemap',
                'robots'
            ];
            if(!YII_DEBUG && __SID__ >0 && !DOMAIN_INVISIBLED && !(Yii::$app->request->method == 'POST') 
                && !in_array(Yii::$app->controller->id, $i)
                && !in_array(Yii::$app->controller->action->id, $i)
                ){
                if((new \yii\db\Query())->from('{{%analytics}}')->where([
                    'sid'=>__SID__,
                    'time'=>date('Y-m-d'),
                    'domain'=>__DOMAIN__
                    
                ])->count(1) == 0){
                    Yii::$app->db->createCommand()->insert('{{%analytics}}',[
                        'sid'=>__SID__,
                        'time'=>date('Y-m-d'),
                        'value'=>1,
                        'domain'=>__DOMAIN__
                        
                    ])->execute();
                }else{
                    Yii::$app->db->createCommand("update {{%analytics}} set value=value+1 where sid=".__SID__ . " and domain='".__DOMAIN__."' and time='".date('Y-m-d')."'")->execute();
                }
                if(__IS_DETAIL__ && __ITEM_ID__>0){
                    Yii::$app->db->createCommand("update {{%articles}} set viewed=viewed+1 where sid=".__SID__ . " and id='".__ITEM_ID__."'")->execute();
                }
                
            }
        }
        
    }
}