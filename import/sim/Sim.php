<?php
namespace izi\import\sim;
use Yii;

class Sim extends \yii\base\Component
{
    
    private $_simthanglong;
    
    public function getSimthanglong(){
        if($this->_simthanglong === null){
            $this->_simthanglong = Yii::createObject([
                'class' => 'izi\import\sim\SimThangLong',
                
            ]);
        }
        return $this->_simthanglong;
    }
    
    
    private $_boss;
    
    public function getBoss(){
        if($this->_boss === null){
            $this->_boss = Yii::createObject([
                'class' => 'izi\import\sim\Bosssim',
                
            ]);
        }
        return $this->_boss;
    }
    
    private $_xsim;
    
    public function getXsim(){
        if($this->_xsim === null){
            $this->_xsim = Yii::createObject([
                'class' => 'izi\import\sim\Xsim',
                
            ]);
        }
        return $this->_xsim;
    }
    
    
    
    public function inportFromFolder($directory)
    {
        $rs = $rs2 = [];
     
        if(!file_exists($directory)){
            return;
        }
        
        $files = scandir($directory); 
        
        if(!empty($files)){
            $i = 0;
            foreach ($files as $file) {
                
                
                
                switch ($file){
                    case '.': case '..': 
                        break;
                        
                    case 'package.json':
                        unlink("$directory/$file");
                        break;
                        
                    default:
                        
                        if($i++ > 99){
                            return ['executed' => $rs2, 'existed' => $rs];
                        }
                        
                        if(is_dir("$directory/$file")){
                            
                            break;
                        }
                        
                        $sims = json_decode(@file_get_contents("$directory/$file"), 1);
                        
                        if(count($sims) < 100 ){
                            view("$directory/$file");
                        }
 
                         
                        
                        if(!empty($sims)){
                            $columns = [];
                            
                            $existed = [];
                            
                            foreach ($sims as $sim){
                                
                                $sim['id'] = (int) str_replace(['.', ','], '', $sim['id']);
                                
                                if(strlen($sim['id']) < 9) continue;
                                
                                if(!in_array($sim['id'], $existed) && !!empty($a = SimModel::findOne(['id' => $sim['id']]))){
                                    
                                    $existed[] = $sim['id'];
                                    
                                    $sim['updated_at'] = time();
                                    
                                    $columns[] = $sim;
                                    
                                    $rs2[] = $sim['id'];
                                }
                                
                                //$rs[] = $sim['id'];
                            }
                            
                             
                            
                            if(Yii::$app->db->createCommand()->batchInsert(SimModel::tableName(), [
                                'id',
                                'display',
                                'price2',
                                'network_label',
                                'category_label',
                                'updated_at'
                            ], $columns)->execute()){
                                
                                
                                
                            }
                            unlink("$directory/$file");
                            
                        }
                        
                        break;
                }
            }
        }
        
        if(strpos($directory, '/simonline') !== false){
            rmdir($directory);
        }
        
        return ['executed' => $rs2, 'existed' => $rs];
        
    }
    
    
    public function importSim($url, $params = [])
    {
        
        $limit = isset($params['limit']) ? $params['limit'] : 0;
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $data = $this->getSimthanglong()->importData($url, $limit, $offset);
        
//         if(isset($data['first_load']) && $data['first_load'] == 1){
//             return;
//         }

        $executed = [];
        
        $data = isset($data['data']) && !empty($data['data']) ? $data['data'] : [];
        
        if(!empty($data)){
            
            $path = Yii::getAlias('@runtime/cache/simonline/' . ($filename = md5($url)));
            
            foreach ($data as $p){
                
                $sims = json_decode( @file_get_contents("$path/$p"), 1);
                
                if(count($sims) < 100){
                                         
                } 
                if(!empty($sims)){
                    $columns = [];
                    
                    $existed = [];
                    
                    foreach ($sims as $sim){
                        
                        $sim['id'] = (int) str_replace(['.', ','], '', $sim['id']);
                        
                        if(!in_array($sim['id'], $existed) && !!empty($a = SimModel::findOne(['id' => $sim['id']]))){
                            
                            $existed[] = $sim['id'];
                            
                            $executed[] = $sim['id'];
                            
                            $sim['updated_at'] = time();
                            
                            $sim['network_id'] = 0;
                            
                            switch ($sim['network_label']){
                                case 'viettel':
                                    $sim['network_id'] = 1;
                                    break;
                                case 'vinaphone':
                                    $sim['network_id'] = 2;
                                    break;
                                case 'mobifone':
                                    $sim['network_id'] = 3;
                                    break;
                                case 'vietnamobile':
                                    $sim['network_id'] = 4;
                                    break;
                                case 'gmobile':
                                    $sim['network_id'] = 5;
                                    break;
                                case 'itelecom':
                                    $sim['network_id'] = 6;
                                    break;
                            }
                            
                            $columns[] = $sim;
                            $columnsx = array_keys($sim);
                        }
                    }
                     
                     
                    Yii::$app->db->createCommand()->batchInsert(SimModel::tableName(), $columnsx, $columns)->execute();
                    
                }
            }
            
            
            
        }
        
        return $executed;
        
    }
    
    public function insertSim($data)
    {
        if(isset($data['id'])){
                   
            $data['id'] = (int) str_replace(['.', ','], '', $data['id']);
            
            if(!!empty(SimModel::findOne(['id' => $data['id']]))){
                $sim = new SimModel();
                foreach($data as $k=>$v){
                    $sim->$k = $v;                
                }
                
                $sim->updated_at = time();
                 
                $sim->save(false);
            }
        }elseif(is_array($data) && !empty($data)){
            
            $columnsx = [];
            $columns = $executed = $existed = [];
            foreach ($data as $sim){
                
                
                
                if(isset($sim['id'])){
                    
                    $sim['id'] = (int) str_replace(['.', ','], '', $sim['id']);
                    
                    if(!in_array($sim['id'], $existed) && !!empty(SimModel::findOne(['id' => $sim['id']]))){
                        
                        $existed[] = $sim['id'];
                        
                        $executed[] = $sim['id'];
                        
                        $sim['updated_at'] = time();
                        
                        $sim['network_id'] = 0;
                        
                        switch ($sim['network_label']){
                            case 'viettel':
                                $sim['network_id'] = 1;
                                break;
                            case 'vinaphone':
                                $sim['network_id'] = 2;
                                break;
                            case 'mobifone':
                                $sim['network_id'] = 3;
                                break;
                            case 'vietnamobile':
                                $sim['network_id'] = 4;
                                break;
                            case 'gmobile':
                                $sim['network_id'] = 5;
                                break;
                            case 'itelecom':
                                $sim['network_id'] = 6;
                                break;
                        }
                        
                        $columns[] = $sim;
                        
                        $columnsx = array_keys($sim);
                        
                    }
                    
                }
            }
            
            Yii::$app->db->createCommand()->batchInsert(SimModel::tableName(), $columnsx, $columns)->execute();
            
            
            return $executed;
        }
    }
    
    
    public function boiSim($params)
    {
        
        $ns = isset($params['ns']) ? $params['ns'] : '28-05-1988';
        
        $gioitinh = isset($params['gioitinh']) ? $params['gioitinh'] : 'nam';
        
        $giosinh = isset($params['giosinh']) ? $params['giosinh'] : 7;
        
        $sosim = str_replace(['.', ','], '',  $params['sosim']);
        
        $url = "https://xsim.vn/boi-sim/?ns=$ns&gioitinh=$gioitinh&sosim=$sosim&giosinh=$giosinh";
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $html = file_get_html($url);
        
        $content = $html->find('.title_result .total_point', 0);
        
        $score = $content->plaintext;
        
        
        if(preg_match_all('!\d+\.*\d*!', $score, $matches)){
             
            
            $score = ($matches[0][0]);
            
            
        }
         
        
        return is_numeric($score) ? $score : 0;
    }
    
    
    public function importSimToMongoDb($data = [], $params = [])
    {
        if(empty($data)){
            $l1 = (new \yii\db\Query())->from('simonline')->where(['<','status',3])->limit(200000)->all();
        }else{
            $l1 = $data;
        }
        
        $collection = Yii::$app->mongodb->getCollection('simonline');
         
        
        if(!empty($l1)){
            
            $existed = [];
            $i = 0;
            foreach ($l1 as $sim){
                
                if(!is_string($sim['id'])){
                    $sim['id'] = (string) $sim['id'];
                }
                
                if(isset($sim['module_name'])){
                    unset($sim['module_name']);
                }
                
                $query = new \yii\mongodb\Query();
                $query->from('simonline')->where(['_id' => $sim['id']]);
                $s = $query->one();
                
                $sim['status'] = isset($sim['status']) && $sim['status'] > 0 ? $sim['status'] : -1;
                $sim['updated_at'] = time();
                              
                foreach ([
                    'price', 'price1','price2', 'partner_id','updated_at', 'score','network_id', 'category_id','category2_id','category3_id', 'status','type_id'
                ] as $field){
                    $sim[$field] = isset($sim[$field]) ? (int) $sim[$field] : 0;
                }
                
                 
                
                if(!empty($s)){
                    
                    $arrUpdate = $sim;
                     
                    if(!(isset($params['update']) && $params['update'] == false)){
                     
                        $collection->update(['_id' => $sim['id']],$arrUpdate); 
                         
                    }
                     
                    
                }else{
                    $s = new SimonlineMongodbModel();
                    $s->_id = $sim['id'];
                    foreach ($sim as $key => $value){
                        //if(!in_array($key, ['id'])){
                            $s->$key = $value;
                        //}
                    }
                    
                    $s->save();
                }
                
                //
                $existed[] = $sim['id'];
                
                if($i++ > 10000){
                    
//                     Yii::$app->db->createCommand()->update('simonline', ['status'=>3], ['id'=>$existed])->execute();
                    
                    $existed = [];
                    $i = 0;
                }
                
                 
                
            }
            
            if(!empty($existed)){
//                 Yii::$app->db->createCommand()->update('simonline', ['status'=>3], ['id'=>$existed])->execute();
            }
            
            return $existed;
        }
                
    }
    
    
}