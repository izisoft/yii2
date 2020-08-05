<?php

namespace izi\sim\import;

use Yii;

class BaseImport extends \yii\base\Component
{

    /**
     * 
     * {@inheritDoc}
     */
    
    private $_db, $_tableCache = 'sim_cache';
    
    public function getDb()
    {
        return $this->_db;
    }
    
    public function init()
    {
        
        Yii::$app->setComponents([ 'dbs' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:' . APP_PATH . '/runtime/sqlite/sqlite_for_simonline.db',
            'charset' => 'utf8',
        ]]);
        
        $this->_db = Yii::$app->dbs;
        $this->_tableCache = 'sim_cache';
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
 
    }
    
    public function getTableCache()
    {
        return $this->_tableCache;
    }
    
    
    public function getLogId($params)
    {
        $log_id = md5(json_encode([
            'query' => $params['query'],
            'group_id' => $params['group_id'],
            'partner_id' => $params['partner_id'],
            'type_id'   =>  1,
        ]));
        return $log_id;
    }
    
    public function setLog($params)
    {
        $log_id = $this->getLogId($params);
        $row = $this->getCached($log_id);   
        
        if(!empty($row)){
            
            $columns = [
                'page'=>$params['page'],
                'json_data' => json_encode($params),
                'updated_time'=>time(),
            ];
            
            Yii::$app->dbs->createCommand()->update($this->_tableCache, $columns, ['id' => $log_id])->execute();
            
            
        }else{
            $columns = [
                'id' => $log_id,
                'partner_id'=>$params['partner_id'],
                'group_id'  => $params['group_id'],
                'page'  => $params['page'],
                'json_data' => json_encode($params),
                'updated_time'=>time(),
                'type_id'=>1
            ];
            
            Yii::$app->dbs->createCommand()->insert($this->_tableCache, $columns)->execute();
        }
    }
    
    public function removeLog($id, $partner_id = 0)
    {   
        if($partner_id > 0 && $id == null){
            return Yii::$app->dbs->createCommand()->delete($this->_tableCache, ['partner_id' => $partner_id])->execute();
        }
        return Yii::$app->dbs->createCommand()->delete($this->_tableCache, ['id' => $id])->execute();
    }
    
    
    public function buildParams($params)
    {
        
        $log_id = $this->getLogId($params);
        
        $item = $this->getCached($log_id);
   
        
        if(!empty($item)){
            $d = json_decode($item['json_data'],1);
            
            if(isset($d['page']) && $d['page'] > 0){
                $params['page'] = $d['page'];
            }else{
                $params['page'] = 1;
            }
            
            if(isset($d['reload_page'])){
                $params['reload_page'] = $d['reload_page'];
            }else{
                $params['reload_page'] = [];
            }
            
        }else{
            $params['page'] = 1;
        }
                
        return $params;
    }
    
    public function getLastCached($partner_id)
    {
        
    }
    
    public function getCached($id)
    {
        return (new \yii\db\Query())->from($this->_tableCache)->where(['id' => $id])->one($this->_db);
    }
    
    private $_cached;
    
    public function validateCached($id)
    {
        //if(isset($this->_cached[$id])) return $this->_cached[$id];
        
        $row = $this->getCached($id);
        
        $overTime = time() - 604800; // 7 days
        
        $st = true;
        
        if(!empty($row)){
            $st = $existed = true;
            
            if($row['updated_time'] < $overTime){
                $st = false;
            }
            
        }else{
            $st = $existed = false;
            
        }
        
        $this->_cached[$id] = [
            'existed' => $existed,
            'state' => $st,
        ];
        
        return $this->_cached[$id];
    }
     
    public function storeCache($id, $partner_id, $data, $params = [])
    {
        $st = $this->validateCached($id);                
        
        if(!$st['state']){
            if($st['existed']){
                
                $columns = [                    
                    'json_data' => json_encode($data),
                    'updated_time'=>time(),
                    'status'    =>  -1,
                ];
                
                Yii::$app->dbs->createCommand()->update($this->_tableCache, $columns,[
                    'id' => $id,
                    'partner_id'=>$partner_id,
                    'group_id'  => $params['group_id'],
                    'page'  => $params['page'],                    
                ])->execute();
                
            }else{
                
                $columns = [
                    'id' => $id,
                    'partner_id'=>$partner_id,
                    'group_id'  => $params['group_id'],
                    'page'  => $params['page'],
                    'json_data' => json_encode($data),
                    'updated_time'=>time(),
                    'status' => -1,
                ];
                
                Yii::$app->dbs->createCommand()->insert($this->_tableCache, $columns)->execute();
            }
        }
        
    }
    
    
    public function removeExpiredCache($params = []){
        $overTime = time() - 3 * 86400; // 7 days
        $conditon = ['and', ['type_id' => 0],[
            '<', 'updated_time', $overTime
        ]];
        
        $query = (new \yii\db\Query())->from($this->_tableCache)->where($conditon);
        
        $item = $query->orderBy(['page' => SORT_ASC])->one($this->_db);
        
//         view($item);
        
        if(!empty($item)){
            Yii::$app->dbs->createCommand()->delete($this->_tableCache, $conditon)->execute();   
            return $item['page'];
        }                       
        return -1;
    }
    
    
    public function setStatus($id, $value){
        Yii::$app->dbs->createCommand()->update($this->_tableCache, ['status' => $value],['id' => $id])->execute();
    }
    
    public function getItems($params = [])
    {
        $query = (new \yii\db\Query())->from($this->_tableCache);
        
        $query->where(['status' => -1]);
        
        if(isset($params['type_id'])){
            $query->andWhere(['type_id' => $params['type_id']]);
        }
        
        $limit = isset($params['limit']) ? $params['limit'] : 100;
        
        $query->limit($limit);
        
        return $query->all($this->_db);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}