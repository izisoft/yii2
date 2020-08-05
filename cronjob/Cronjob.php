<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\cronjob;
use Yii;
class Cronjob extends \yii\base\Component
{
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject(['class'=>'izi\cronjob\CronjobModel']);
        }
        return $this->_model;
    }
    
    private $_type;
    public function getType(){
        $a = [
            'CRON_UPDATE_CLASS_STATUS' => 50,
            'CRON_CHANGE_CLASS_STATUS_ACTIVE' => 51,
            'CRON_CHANGE_CLASS_STATUS_READY' => 52,
            'CRON_CHANGE_CLASS_STATUS_SUCCESS' => 53,
            
            //
            'CRON_CHANGE_PRICE_STATE_TYPE1' => 60,
            
            //'CRON_UPDATE_EXCHANGE_RATE' => 80,
            'CRON_UPDATE_ITEM_PROMOTION_STATUS'=>101,
            'CRON_UPDATE_ALL_ITEM_PROMOTION_STATUS'=>102,
            
            'CRON_UPDATE_ITEM_STATUS'=>103,
            'CRON_UPDATE_ALL_ITEM_STATUS'=>104,
            
            'CRON_UPDATE_CURRENCY_EXCHANGE_RATE'=>105,
            
            'CRON_UPDATE_ITEM_ADVERT_STATUS'=>106,
            'CRON_UPDATE_ALL_ITEM_ADVERT_STATUS'=>107,
            
            'CRON_CHECK_DOMAIN'=>500,
            
            'CRON_IMPORT_DATA'=>502,
            
            'CRON_IMPORT_SIM_DATA'=>600,
            
        ];
        return (object) $a;
    }
    
    
    public function executeToday($params = []){
        
        $session_key = md5(__METHOD__);
        
        $next_time = Yii::$app->session->get($session_key, 0);       
                         
        if($next_time > __TIME__) return -1;     
        
//         view($this->getModel()->getTodayJob(), 1,1);
       
        foreach (($jobs = $this->getModel()->getTodayJob()) as $job) {
            $state = -1;
         
            switch ($job['type_code']) {
                
                // import sim data
                case $this->getType()->CRON_IMPORT_SIM_DATA:
                    
                     
                    
                    if(YII_DEBUG) {
                        
                        break;
                    }
                    
                    if($job['is_locked'] == 1){
                        break;
                    }
                    
                    if(!(substr(__DOMAIN__, 0, 5) == 'dev10')){
                        break;
                    }
                    
                    if($job['id'] == ""){
                        
                        Yii::$app->db->createCommand()->update($this->getModel()->tableName(),
                            [
                                'is_locked'=>1,
                                
                            ],
                            ['type_code'=>$job['type_code'],'item_id'=>$job['item_id'],'sid'=>$job['sid']])->execute();
                            
                             
                    }else{
 
                        Yii::$app->db->createCommand()->update($this->getModel()->tableName(),
                            ['is_locked'=>1],
                            ['id'=>$job['id']])->execute();
 
                    }
                    
                    
                    
                    $job['started_time'] += rand(60, 300);
                    $c = 1;
                    $c = Yii::$app->sim->updateDataFromTempdata(rand(5000, 8000));
                    
                    if($c == 0){
                        $job['started_time'] += 3600;
                    }else{
                        $job['started_time'] = time() + rand(60, 300);
                    }
                    
                    
                    if($job['id'] == ""){
                        
                        Yii::$app->db->createCommand()->update($this->getModel()->tableName(),
                            [
                                'is_locked'=>0,
                                
                            ],
                            ['type_code'=>$job['type_code'],'item_id'=>$job['item_id'],'sid'=>$job['sid']])->execute();
                            
                            
                    }else{
                        
                        Yii::$app->db->createCommand()->update($this->getModel()->tableName(),
                            ['is_locked'=>0],
                            ['id'=>$job['id']])->execute();
                            
                    }
                    
                    break;
                
                //
                case $this->getType()->CRON_IMPORT_DATA:
                    
                    $state = -1;
                    
                    $l = (new \yii\db\Query())->from('craw_data')
                    ->where(['>','state',-1])
                    ->andWhere(['sid' => __SID__])
                    ->andWhere(['<', 'started_time', __TIME__])
                    ->all();
                    
                    if(!empty($l)){
                        foreach ($l as $v){
                            switch ($v['source']) {
                                case 'kenh14':
                                
                                    $source_url = $v['source_url'];
                                    
                                    $links = Yii::$app->import->kenh14->getProductLink($source_url);                                    
                                    
                                    if(!empty($links)){
                                        foreach($links as $link){
                                            Yii::$app->import->kenh14->importNews($link, ['categories' => json_decode($v['category'], 1)]);
                                        }
                                    }
                                    
                                    switch ($v['frequency']) {
                                        case 'hourly':
                                            $delay = 3600;
                                            break;
                                        case 'daily':
                                            $delay = 86400;
                                            break;
                                        
                                        default:
                                            $delay = 7200;
                                        break;
                                    }
                                    
                                    if($v['started_time'] < __TIME__ - $delay){
                                        $v['started_time'] = __TIME__ - $delay;
                                    }
                                    
                                    $delay_time = $v['started_time'] + $delay;                                    
                                    
                                    Yii::$app->db->createCommand()->update('craw_data', [
                                        'started_time' => $delay_time
                                    ], ['id'=>$v['id']])->execute();
                                    
                                    
                                    $job['started_time'] = __TIME__ ;
                                    
                                    break;
                                
                            }
                        }
                    }else{
                        $job['started_time'] = __TIME__ + 3600;
                    }
                    
                    break;
                // 
                case $this->getType()->CRON_CHECK_DOMAIN:
                    
                    $state = -1; 
                    
                    $domains = isset($job['domain']) ? $job['domain'] : [];
                    
                    if(!is_array($domains)) $domain = [$domains];
                    
                    if(!empty($domains)){
                        foreach ($domains as $domain){
                            $status = \app\modules\admin\models\DomainWhois::checkDomain($domain);
                            
                            if(!$status){
                                
                                $form2 = '<p>Xin chào: <b>Admin</b></p>';
                                
                                $form2 .= '<p>Tên miền <b>'.$domain.'</b> đã có thể đăng ký.</p>';
                                $form2 .= '<p>Nhanh tay đăng ký trước khi người khác đăng ký mất.</p>';
                                $form2 .= '<p>Dữ liệu được check: '.date('d/m/Y H:i:s').'</p>';
                                
                                if(Yii::$app->mailer->sendEmail([
                                    'subject'=>'Đăng ký domain '.$domain,
                                    'body'=>$form2,
                                      
                                    'to'=>'zinzinx8@gmail.com'
                                ])){
                                    $state = 1;
                                }
                                
                            }else{
                                $job['started_time'] = __TIME__ + 300;
                            }
                        }
                    }
                    
                    break;
                
                case $this->getType()->CRON_UPDATE_ITEM_ADVERT_STATUS:
                    $table = $job['table'];
                    
                    if(isset($job['field'])){
                        switch ($job['field']){ 
                            default:
                                
                                Yii::$app->db->createCommand()->update($table, [
                                $job['field']=>$job['value']
                                ], ['id'=>$job['item_id']])->execute();
                                
                                $state = 1;
                                break;
                        }
                    }
                    
                    break;
                    
                case $this->getType()->CRON_UPDATE_CURRENCY_EXCHANGE_RATE:
                    
//                     view(Yii::$app->currencies->getExrate([
//                         'from'=>2, 'to'=>1, 'return'=>'last'
//                     ])); exit;
                    
                    Yii::$app->currencies->updateExchangeRate();
                    $job['started_time'] = __TIME__ + 300;
                    $job['checksum'] = md5(json_encode($job));
                  
                    break;
                
                case $this->getType()->CRON_UPDATE_ITEM_STATUS:
                    $table = \app\modules\admin\models\Content::tableName();
                    if(isset($job['field'])){
                        switch ($job['field']){ 
                            case 'is_active':
                                
                                Yii::$app->db->createCommand()->update($table, [
                                $job['field']=>$job['value'],
                                //'last_modify'=>date('Y-m-d H:i:s')
                                ], ['id'=>$job['item_id']])->execute();
                                
                                $state = 1;
                                break;
                        }
                    }
                    
                    break;
                case $this->getType()->CRON_UPDATE_ITEM_PROMOTION_STATUS:
                    $state = -1;
                    $table = \app\modules\admin\models\Content::tableName();
                    if(isset($job['field'])){
                        
                        $item = \app\modules\admin\models\Content::getItem($job['item_id']);
                        if(!empty($item)){
                            $is_invisibled = 1;
                            if($item['expired_date']<__TIME__){
                                $is_invisibled = 1;
                                $state = 1;
                            }elseif($item['started_date']<__TIME__ && $item['expired_date']>__TIME__){
                                $is_invisibled = 0;
                                $state = 1;
                            }
                            if($state == 1){
                                Yii::$app->db->createCommand()->update($table, [
                                    'is_invisibled'=>$is_invisibled,
                                    //'last_modify'=>date('Y-m-d H:i:s')
                                ], ['id'=>$job['item_id']])->execute();
                            }                                                                                    
                        }
                    }
                    
                    break;
//                 case $this->getType()->CRON_CHANGE_CLASS_STATUS_READY:
//                     Yii::$app->db->createCommand()->update('class', ['status'=>\app\modules\admin\models\ClassManage::$CLASS_STATUS_READY],['id'=>$v['item_id']])->execute();
//                     break;
//                 case $this->getType()->CRON_CHANGE_CLASS_STATUS_ACTIVE:
//                     Yii::$app->db->createCommand()->update('class', ['status'=>\app\modules\admin\models\ClassManage::$CLASS_STATUS_ACTIVE],['id'=>$v['item_id']])->execute();
//                     break;
//                 case $this->getType()->CRON_CHANGE_CLASS_STATUS_SUCCESS:
//                     Yii::$app->db->createCommand()->update('class', ['status'=>\app\modules\admin\models\ClassManage::$CLASS_STATUS_SUCCESS],['id'=>$v['item_id']])->execute();
//                     break;	
            }
            
            if($job['id'] == ""){

                Yii::$app->db->createCommand()->update($this->getModel()->tableName(),
                    [
                        'state'=>$state,
                        'last_modify'=>date('Y-m-d H:i:s'),
                        'started_time' => $job['started_time']
                    ],
                    ['type_code'=>$job['type_code'],'item_id'=>$job['item_id'],'sid'=>$job['sid']])->execute();
               
                if($state == 1) { // done
                    Yii::$app->db->createCommand()->delete($this->getModel()->tableName(),
                         ['type_code'=>$job['type_code'],'item_id'=>$job['item_id'],'sid'=>$job['sid']])->execute();
                }
            
            }else{
                $jk['last_modify'] = date('Y-m-d H:i:s');
                $jk['started_time'] = $job['started_time'];
                $jk['state'] = $state;
                Yii::$app->db->createCommand()->update($this->getModel()->tableName(),
                    $jk,
                    ['id'=>$job['id']])->execute();
                             
                if($state == 1) { // done
                     Yii::$app->db->createCommand()->delete($this->getModel()->tableName(),
                     ['id'=>$job['id']])->execute();
                }
            }
        }
        
        
        if(empty($jobs)){
            // get next time;
            $job = $this->getModel()->getNearestJob();
            
            if(!empty($job)){
                $time = $job['started_time'];
            }else{
                $time = __TIME__ + 2 * 3600;
            }
            
            Yii::$app->session->set($session_key, $time);
            
        }
         
    }
    
    
    public function updateItemCronjob($item_id, $params){
        
        $value = $params['value'] ;
             
        $field = [
            'field'=>$params['field'] , 
            'value'=>$value, 
            'action'=>isset($params['action']) ? $params['action'] : '',
            'table'=>isset($params['table']) ? $params['table'] : '',
            
        ];
        
        $params2 = [
            'checksum'=>md5(json_encode($field)),
            'sid'=>__SID__, 'item_id'=>$item_id, 'type_code'=>$params['type_code']];
        
        
        
        $job = $this->getModel()->getJobs($params2);
        
        $params2['started_time'] = $params['started_time'];
        $params2['bizrule'] = (json_encode($field));
        
        if(!empty($job)){
            if($params2['started_time'] != $job['started_time']){
                $params2['state'] = -1;
                $this->getModel()->updateJob($job['id'], $params2);
            }
            
        }else{
            
            $this->getModel()->createJob($params2);
        }
    }
    
    
}