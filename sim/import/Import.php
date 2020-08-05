<?php

namespace izi\sim\import;

use Yii;

class Import extends BaseImport
{

    /**
     * 
     * {@inheritDoc}
     */
    
    public function init()
    {
//         require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";

        parent::init();
    }
    
    /**
     * Setup partner
     * @var unknown
     */
    private $_bosssim;    
    public function getBosssim(){
        if($this->_bosssim === null){
            $this->_bosssim = Yii::createObject([
                'class' => 'izi\sim\import\Bosssim',
                
            ]);
        }
        return $this->_bosssim;
    }
    
    
    private $_simnha;
    public function getSimnha(){
        if($this->_simnha === null){
            $this->_simnha = Yii::createObject([
                'class' => 'izi\sim\import\Simnha',
                
            ]);
        }
        return $this->_simnha;
    }
    
    private $_phugia;
    public function getPhugia(){
        if($this->_phugia === null){
            $this->_phugia = Yii::createObject([
                'class' => 'izi\sim\import\SimPhuGia',
                
            ]);
        }
        return $this->_phugia;
    }
    
    private $_thudo;
    public function getThudo(){
        if($this->_thudo === null){
            $this->_thudo = Yii::createObject([
                'class' => 'izi\sim\import\SimThuDo',
                
            ]);
        }
        return $this->_thudo;
    }
    
    
    
    private $_thanglong;
    public function getThanglong(){
        if($this->_thanglong === null){
            $this->_thanglong = Yii::createObject([
                'class' => 'izi\sim\import\Simthanglong',
                
            ]);
        }
        return $this->_thanglong;
    }
    
    /**
     * 
     */
    private $_cacheFolder;
    
    public function getCacheFolder()
    {
        return $this->_cacheFolder;
    }
    
    
    public function setCacheFolder($value)
    {
        $this->_cacheFolder = $value;        
    }
    
    private $partners;
    public function getAgent($partner_id)
    {
        $partner = isset($this->partners[$partner_id]) ? $this->partners[$partner_id] : ($this->partners[$partner_id] = Yii::$app->customer->model->getItem($partner_id));
        if(!empty($partner)){
            
            $method = "get" . ucfirst($partner['code']);
            
            return $this->$method();
        }
    }
    
    /**
     * 
     */
    
    public function getDefaultProxyList()
    {
//         return [
//             '23.236.180.234' => '80',
//         ];
        return [

            '34.92.33.150' => '80',

            '183.91.33.41' => '80',
            
            
            '146.88.51.238' => '80',

            '146.88.51.234' => '80',

            '113.53.230.167'=>'80',

            
        ];
    }
    
    public function getProxy()
    {
        
        $proxy = $this->getDefaultProxyList();
        
        $array = array_keys($proxy);
        
        $k = $array[rand(0, count($array)-1)];
        
        $count = 0;
         
        
        return ['ip' => $k ,'port' => isset($proxy[$k]) && $proxy[$k]>0 ? $proxy[$k] : '80'];
    }
    
    
    /**
     * 
     */
    public function getProductPageLinks($scheme, $params = [])
    {
        return $scheme->getProductPageLinks($params);
    }
    
    
    
    /**
     *
     */
    public function getProducts($scheme, $params)
    {
        return $scheme->getProducts($params);
    }
    
    public function importProductsFromDb($partner_id = 0)
    { 
        
        $cus = Yii::$app->customer->model->getItem($partner_id);
        
        if(empty($cus)) return;
        
        $code = $cus['code'];
        
        $children_id = isset(Yii::$app->sim->import->$code->children_id) && Yii::$app->sim->import->$code->children_id > 0 ? Yii::$app->sim->import->$code->children_id : 0;
        
        
        
        
        $l = (new \yii\db\Query())->from($this->tableCache)->where(['status' => -1, 'type_id' => 0])->limit(300)->all($this->db);
        
        view($l);
        
        if(!empty($l)){
            foreach ($l as $v){
                $data = json_decode($v['json_data'],1);
                
                if(!empty($data)){
                    foreach($data as $sim){
                        
                        $sim = array_merge($sim, Yii::$app->sim->getSimInfo($sim['display']));
                        
                        $sim['price2'] = Yii::$app->sim->getSellPriceFromAgentPrice($sim['price'] , [
                            'sim' => $sim,
                            'partner_id' => $v['partner_id'],
                            'group_name' => 'web_default',
                            'quotation_code'=>'web_default',
                        ]);
                        
                        Yii::$app->sim->updateSingleSimData($sim['id'], $v['partner_id'], $sim, ['children_id' => $children_id]);
                    }
                }
                
                $this->db->createCommand()->update($this->getTableCache(), ['status' => 1], ['id' => $v['id']])->execute();
            }
            
            
            
        }
        
    }
    
    
    public function importProducts($partner_id)
    {
        $cus = Yii::$app->customer->model->getItem($partner_id);
        
        if(empty($cus)) return;
        
        $code = $cus['code'];
        
        $directory = Yii::$app->sim->import->$code->supplierFolder;
        
        if(!file_exists($directory)) return;
        
        $children_id = isset(Yii::$app->sim->import->$code->children_id) && Yii::$app->sim->import->$code->children_id > 0 ? Yii::$app->sim->import->$code->children_id : 0;
         
        
        $folders = scandir($directory);
        
        $overTime = 2 * 86400;
        
        $list_index = 0; $break = false;
        
        $executed = 0;
        
        if(!empty($folders)){
            
            foreach ($folders as $folder) {
                
                if($break) break;
                
                switch ($folder){
                    case '.': case '..':
                        break;
                        
                    default:
                        
                        if(is_dir("$directory/$folder")){
                            $files = scandir("$directory/$folder");
                            
                            if(!empty($files)){
                                
                                foreach ($files as $file) {
                                    
                                    if($break) break;
                                    
                                    switch ($file){
                                        case '.': case '..':
                                            break;
                                            
                                        case 'log.json': case 'log2.json':case 'log3.json':
                                            
                                            $log2 = "$directory/$folder/$file";
                                            
                                            if(time() - filectime($log2) > $overTime){
                                                @unlink($log2);
                                            }
                                            
                                            break;
                                            
                                        default:
                                            
                                            $log2 = "$directory/$folder/log2.json";
                                            $log3 = "$directory/$folder/log3.json";
                                            
                                            
                                            $ex = [];
                                            
                                            if(file_exists($log2)){
                                                $ex = json_decode(file_get_contents($log2),1);
                                                
                                                if(!empty($ex)){
                                                    foreach ($ex as $f2){
                                                        $fnname = "$directory/$folder/$f2";
                                                        if(file_exists($fnname)){
                                                            @unlink($fnname);
                                                        }
                                                    }
                                                     
                                                }
                                            }
                                            
                                            
                                            $fnname = "$directory/$folder/$file";
                                            if(file_exists($fnname) && time() - filectime($fnname) > $overTime){
                                                @unlink($fnname);
                                                if(isset($ex[$file])){
                                                    unset($ex[$file]);
                                                }
                                            }
                                            
                                            
                                            if(!empty($ex) && in_array($file, $ex)){
                                                break;
                                            }
                                            
                                            if(!file_exists("$directory/$folder/$file")) break;
                                            
                                            $data = json_decode(file_get_contents("$directory/$folder/$file"),1);
                                            
                                            
                                            
                                            if(!empty($data)){
                                                foreach($data as $sim){
                                                    
                                                    $sim = array_merge($sim, Yii::$app->sim->getSimInfo($sim['display']));
                                                    
                                                    $sim['price2'] = Yii::$app->sim->getSellPriceFromAgentPrice($sim['price'] , [
                                                        'sim' => $sim,
                                                        'partner_id' => $partner_id,
                                                        'group_name' => 'web_default',
                                                        'quotation_code'=>'web_default',
                                                    ]);
                                                    
                                                    Yii::$app->sim->updateSingleSimData($sim['id'], $partner_id, $sim, ['children_id' => $children_id]);
                                                }
                                            }
                                            
                                            
                                            $ex [] = $file;
                                            
                                            //writeFile($log2, json_encode($ex, JSON_PRETTY_PRINT) );
                                            
                                            if(getParam('view') == 1){
//                                                 view(($executed += count($data) ). ' | ' . $cus['code']."/$folder/$file");
                                                $executed += count($data);
                                            }
                                            
                                            ///////////////////////
                                            @unlink($fnname);
                                            
                                            if($list_index++ > 400){
                                                $break = true;
                                            }
                                            break;
                                    }
                                    
                                    
                                }
                            }
                        }
                        
                        break;
                }
            }
        }
        
        
        if(getParam('view') == 1){
            view(number_format($executed) . ' bản ghi được cập nhật.');
        }
         
        exit;
    }
    
    public function importProductsV3()
    {
        $l = $this->getItems(['type_id' => 0]);
        view($l);
        exit;
    }
    
    public function importProductsV2()
    {
        $l = $this->getItems(['type_id' => 0]);
        

        $excuted = 0;
        
        if(!empty($l)){
            foreach ($l as $v){
                
                $partner_id = $v['partner_id'];
                
                $data = json_decode($v['json_data'],1);
                
                if(!empty($data)){
                    foreach($data as $sim){
                        $sim = array_merge($sim, Yii::$app->sim->getSimInfo($sim['display']));
                        
                        if($sim['price'] < 300000){
                            if($sim['category_id'] == 0){
                                continue;
                            }
                            
                            if($sim['network_id'] > 3){
                                continue;
                            }
                        }
                        
                        $price2 = Yii::$app->sim->getSellPriceFromAgentPrice($sim['price'] , [
                            'sim' => $sim,
                            'partner_id' => $partner_id,
                            'group_name' => 'web_default',
                            'quotation_code'=>'web_default',
                        ]);
                        
                        
                        if(isset($sim['price2']) && $sim['price2'] > $price2){
                            
                        }else{
                        
                            $sim['price2'] = $price2;
                        }
                         
                        
                       Yii::$app->sim->updateSingleSimData($sim['id'], $partner_id, $sim, []);
                       
                       if($excuted++ > 10000){
                           return $excuted;
                       }
                    }
                }
                
               $this->setStatus($v['id'], 1);
                
            }
        }
       
        
        return $excuted;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}