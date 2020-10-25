<?php 

namespace izi\frontend\sim;
use Yii; 

class Simonline extends \yii\base\Component
{
    public $frontend;
    
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class' =>  'izi\\frontend\\sim\\SimonlineModel',
                'frontend' => $this,
                'box'  =>  Yii::$app->box
            ]);
        }
        
        return $this->_model;
    }
    
    
    public function initPromotionSimVip($params = [])
    {
        $module_name = isset($params['module_name']) ? $params['module_name'] : 'index_vip';
        $limit = isset($params['limit']) ? $params['limit'] : 30;
        
        $max_price = isset($params['max_price']) ? $params['max_price'] : 0;
        
        $min_price = isset($params['min_price']) ? $params['min_price'] : 3500000;
        
        
        $query = SimonlineModel::find();
        
        if($min_price > 0){
            $query->andWhere(['>','price2',$min_price]);
        }
        
        if($max_price > $min_price){
            $query->andWhere(['<','price2',$max_price]);
        }
        
        $query->andWhere(['>','price2',3500000]);
        $query->orderBy(new \yii\db\Expression('rand()'));
        
        $l = $query->limit($limit)->asArray()->all();
        
        return $l;
        
        if(!empty($l)){
            foreach ($l as $v){
                if(empty(SimonlineModuleModel::findOne(['id'=>$v['id']]))){
                    $sim = new SimonlineModuleModel();
                    
                    $sim->module_name = $module_name;
                    
                    
                    foreach ($v as $field=>$value){
                        $sim->$field = $value;
                    }
                    
                    
                    $sim->save(false);
                    
                }
                
            }
        }
        
        return $l;
    }
      
    
    public function initPromotionSim($params = [])
    {
        $module_name = isset($params['module_name']) ? $params['module_name'] : 'index_promotion';
        
        $max_price = isset($params['max_price']) ? $params['max_price'] : 1500000;
        
        $min_price = isset($params['min_price']) ? $params['min_price'] : 0;
        
        $limit = isset($params['limit']) ? $params['limit'] : 30;
        
        if(isset($params['clear_data']) && $params['clear_data'] === true){
            Yii::$app->db->createCommand()->delete(SimonlineModuleModel::tableName(), ['module_name' => $module_name]) ->execute();
        }
        
        $query = SimonlineModel::find();
        
        
        $query->andWhere(['<','price2',$max_price]);
        
        if($min_price > 0){
            $query->andWhere(['>','price2',$min_price]);
        }
        
        if($max_price > $min_price){
            $query->andWhere(['<','price2',$max_price]);
        }
        
        $query->orderBy(new \yii\db\Expression('rand()'));
        
        $l = $query->limit($limit)->asArray()->all();
        
        return $l; 
        
        if(!empty($l)){
            foreach ($l as $v){
                if(empty(SimonlineModuleModel::findOne(['id'=>$v['id']]))){
                    $sim = new SimonlineModuleModel();
                    
                    $sim->module_name = $module_name;
                    
                    
                    foreach ($v as $field=>$value){
                        $sim->$field = $value;
                    }
                    
                  
                    $sim->save(false);
                    
                }
                
            }
        }
        
        return $l;
    }
    
    
    public function getItem($sosim, $params = [])
    {
        $sosim =  (string) ltrim(str_replace(['.', ',', ' '], '', $sosim),'0 ');
         
        
        
        $driver = isset($params['driver']) ? $params['driver'] : 'mongodb';
        
        switch ($driver){
            case 'mongodb':
                 
//                 $query = new \yii\mongodb\Query();
//                 $query->from('simonline')->where(['_id' => "$sosim"]);
                
                $sim['id'] = (String)$sosim;
                 
                
                $query = new \yii\mongodb\Query();
                $query->from('simonline')->where(['_id' => $sim['id']]);
                  
                $item = $query->one();
                
                if(!empty($item) && isset($params['module_name']) && $params['module_name'] == true){
                    
                    $v2 = SimonlineModuleModel::findOne(['id' => $sosim]);
                    
                    if(!empty($v2)){
                        $item['module_name'] = $v2['module_name'];
                    }else{
                        $item['module_name'] = '';
                    }
                    
                }
                
                return $item;
                break;
                
            
        }
        
        
        return SimonlineModel::findOne(['id' => $sosim]);
    }
    
    public function getRegex($params)
    {
        if(isset($params['regex']) && $params['regex'] != ""){
            switch ($regex = $params['regex']){
                
                //             case '[^a]aaaaaa$':
                //             case '[^a]aaaaa$':
                //             case '[^a]aaaa$':
                //             case '[^a]aaa$':
                //             case '.*[^a]aaaaaa[^a].*$':
                //             case '.*[^a]aaaaa[^a].*$':
                //             case '.*[^a]aaaa[^a].*$':
                
                
                //                 break;
                case '_DAI_CAT_': break;
                
                case '[^a]bcdef$':
                case '[^a]bcde$':
                case '[^a]bcd$':
                    
                    $rx = [];
                    
                    $len = 10 - strlen($regex) + 6;
                     
                    
                    for($b=0;$b<$len;$b++){
                        $a = $b -1;
                        $c = $b + 1;
                        $d = $b + 2;
                        $e = $b + 3;
                        $f = $b + 4;
                        
                        $rx[] = str_replace(['a','b','c','d','e','f', '$'], [$a,$b,$c,$d,$e,$f,''], $regex);
                    }
                    
                    $regex = '('.implode('|', $rx).')$';
                    
                    
                    
                    return $regex;
                    
                    break;
                case '[ababab|abcabc]$': // sim taxi
                    
                    
                    $rx = [];
                    
                    for($a = 0; $a<10;$a++){
                        for($b = 0; $b<10;$b++){
                            if($a != $b)
                                $rx[] = str_replace(['a','b', '$'], [$a,$b,''], 'ababab');
                        }
                    }
                    
                    for($a = 0; $a<10;$a++){
                        for($b = 0; $b<10;$b++){
                            for($c = 0; $c<10;$c++){
                                if($a != $b || $b != $c || $a != $c){
                                    $rx[] = str_replace(['a','b','c', '$'], [$a,$b,$c,''], 'abcabc');
                                }
                            }
                        }
                    }
                    
                    
                    $regex = '('.implode('|', $rx).')$';
                    
                    return $regex;
                    break;
                
                default:
                    preg_match_all('(a|b|c|d|e|f|g|h)', $regex, $matches);
                    
                    $m = [];
                    
                    if(!empty($matches) && !empty($matches[0])){
                        foreach ($matches[0] as $char){
                            if(!in_array($char, $m)){
                                $m[] = $char;
                            }
                        }
                    }
                    
                    $rx = [];
                    
                    switch (count($m)){
                        case 1:
                            for($i = 0; $i<10;$i++){
                                $rx[] = str_replace(['a', '$'], [$i,''], $regex);
                            }
                            break;
                            
                        case 2:
                            for($i = 0; $i<10;$i++){
                                for($j = 0; $j<10;$j++){
                                    if($i != $j)
                                    $rx[] = str_replace(['a','b', '$'], [$i,$j,''], $regex);
                                }
                            }
                            break;
                            
                        case 3:
                            for($a = getParam('a0',0); $a<getParam('a1',10);$a++){
                                for($b = getParam('b0',0); $b<getParam('b0',10);$b++){
                                    for($c = 0; $c<10;$c++){
                                        if($b != $c &&($a != $b && $a != $c)){
                                            $rx[] = str_replace(['a','b','c', '$'], [$a,$b,$c,''], $regex);
                                        }
                                    }
                                }
                            }
                            break;
                            
                        case 4:
                            for($a = getParam('a0',0); $a<getParam('a1',10);$a++){
                                for($b = getParam('b0',0); $b<getParam('b0',10);$b++){
                                    for($c = getParam('c0',0); $c<getParam('c0',10);$c++){
                                        for($d = 0; $d<10;$d++){
                                            if($b != $c &&($a != $b && $a != $c) && $d != $a && $d != $c && $d != $b){
                                                $rx[] = str_replace(['a','b','c','d', '$'], [$a,$b,$c,$d,''], $regex);
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                            
                            
                            default: return $regex;break;
                    }
                    
                    $regex = '('.implode('|', $rx).')$';
                    
                    return $regex;
                    break;
            }}
        
    }
    
    
    public function findSim($sosim)
    {
        $sosim = ltrim(preg_replace('/\D/', '', $sosim), '0');
        
        $query = new \yii\mongodb\Query();
        $query->from('simonline')->where(['_id' => $sosim]);
        return $query->one();
    }
    
    
    public function getProfitPrice($price)
    {
        
    }
    
    
    
    public function getItemsFromMongoDb($params)
    
    {
         
         
        $limit = isset($params['limit']) ? $params['limit'] : 30;
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $p = isset($params['p']) && $params['p'] > 1 ? $params['p'] : 1;
        
        $offset = ($p - 1) * $limit;
        
        $count = isset($params['count']) && $params['count'] === true ? true : false;
        
         
        
        $min_price = isset($params['min_price']) ? $params['min_price'] : 0;
        
        $max_price = isset($params['max_price']) ? $params['max_price'] : 0;
        
        $query = new \yii\mongodb\Query();
        $query->from(SimonlineMongodbModel::collectionName());
        
        
        // Conditions
        
        $validate_min_price = false;
        
        if(isset($params['sosim']) && ($sosim = trim($params['sosim'])) != ""){
            $sosim = ltrim($sosim, '0^ ');
            $sosim = rtrim($sosim, '$ ');
            
            $a = isset($params['a']) ? $params['a'] : rand(0,9);
            
            $b = isset($params['b']) ? $params['b'] : rand(0,9);
            
            $c = isset($params['c']) ? $params['c'] : rand(0,9);
            $d = isset($params['d']) ? $params['d'] : rand(0,9);
            $e = isset($params['e']) ? $params['e'] : rand(0,9);
            $f = isset($params['f']) ? $params['f'] : rand(0,9);
            
            $x = isset($params['x']) ? $params['x'] : rand(0,9);
            $y = isset($params['y']) ? $params['y'] : rand(0,9);
            
            
            $rg = [
                '.' => '',
                '*' => '.*',
                '_' => '[0-9]',
                '?' => '[0-9]',
                'a' => $a,
                'b' => $b,
                'c' => $c,
                'd' => $d,
                'e' => $e,
                'f' => $f,
                'x' => $x,
                'y' => $y
            ];
            
            $sosim = str_replace(array_keys($rg),array_values($rg), $sosim);
            
            if(is_numeric($sosim)){
                if(strlen($sosim) < 9){
                    $sosim = ".*$sosim.*";
                }elseif(strlen($sosim) > 9){
                    $sosim = substr($sosim, 0, 9);
                }
            }
            
             
            
            if(strlen($sosim) == 9 && !empty(preg_match('/[1-9]\d{8}/', $sosim))){
                 
                $query->andWhere(['_id' => $sosim]);
            }else{
            
                if(preg_match('/\d+/', $sosim, $m) && $m[0] == $sosim){
                    
                    $query->andWhere(['regex' ,'_id',  "/$sosim/i"]);
                    
                }else{
                    $query->andWhere(['regex' ,'_id',  "/^$sosim$/i"]);
                }
            }
              
        }
        
        
        if(isset($params['regex']) && $params['regex'] != ""){
            $query->andWhere(['regex' ,'_id',  $params['regex']]);
        }
        
        // Filter1
        
        $filters = isset($params['filters']) ? $params['filters'] : [];
        
        $sim_filter = isset($params['sim_filter']) ? $params['sim_filter'] : [];
        
        if(isset($params['category_id']) && $params['category_id']>0){
            $sim_filter['category_id'] = $params['category_id'];
        }
        
        if(isset($params['category2_id']) && $params['category2_id']>0){
            $sim_filter['category2_id'] = $params['category2_id'];
        }
        
        if(isset($params['category3_id']) && $params['category3_id']>0){
            $sim_filter['category3_id'] = $params['category3_id'];
        }
        
        if(isset($params['partner_id']) && $params['partner_id']>0){
            $sim_filter['partner_id'] = $params['partner_id'];
        }
        if(isset($params['network_id']) && $params['network_id']>0){
            $sim_filter['network_id'] = $params['network_id'];
        }
        
        if(isset($params['type_id']) && $params['type_id']>0){
            $sim_filter['type_id'] = $params['type_id'];
        }
       
        
        // simfilter
        if(!empty($sim_filter)){
            
            //
//             view($sim_filter);
            
            
            //
            if(isset($sim_filter['network_id']) && $sim_filter['network_id']>0){
                $query->andWhere(['network_id' => (int)$sim_filter['network_id']]);
            }
            
            if(isset($sim_filter['category_id']) && $sim_filter['category_id']>0){
                $query->andWhere(['category_id' => (int)$sim_filter['category_id']]);
            }
            
            if(isset($sim_filter['partner_id']) && $sim_filter['partner_id']>0){
                $query->andWhere(['partner_id' => (int)$sim_filter['partner_id']]);
            }
            
            if(isset($sim_filter['category2_id']) && $sim_filter['category2_id']>0){
                $query->andWhere(['category2_id' => (int)$sim_filter['category2_id']]);
            }
            
            if(isset($sim_filter['category3_id']) && $sim_filter['category3_id']>0){
                $query->andWhere(['category3_id' => (int)$sim_filter['category3_id']]);
            }
            
            if(isset($sim_filter['type_id']) && $sim_filter['type_id']>0){
                $query->andWhere(['type_id' => (int)$sim_filter['type_id']]);
            }
            
            if(isset($sim_filter['regex']) && ($regex = $sim_filter['regex']) != ""){
                
                $val = explode('|', $regex);
                 
                
                switch ($val[0]){
                    
                    case 'price':
                        $val1 = max($min_price, isset($val[1]) && is_numeric($val[1]) ? $val[1] : 0);
                        $val2 = max($max_price, isset($val[2]) && is_numeric($val[2]) ? $val[2] : 0);
                        
                        if($val2 > $val1-1){
                            $query->andWhere(['between', 'price2', (float)$val1, (float)$val2]);
                            $validate_min_price = true;
                        }
                        break;
                        
                    default:
                        $query->andWhere(['REGEX' ,'_id',   '/' . $sim_filter['regex'] . '/i']);
                        
                        break;
                }
                
                
            }
            
        }
        
        if(isset($params['explode']) && !empty($params['explode'])){
            
            $a = [0,1,2,3,4,5,6,7,8,9];
            
            $array_diff = array_diff($a, $params['explode']);
             
            $query->andWhere(['REGEX' ,'_id',   "/^[".implode('', $array_diff)."]+$/i"]);
            
             
        }
        
        
        if(!$validate_min_price){
            if($min_price>0 && $max_price>0){
                $query->andWhere(['between', 'price2', (float)$min_price, (float)$max_price]);
            }elseif ($min_price > 0){
                $query->andWhere(['between', 'price2', (float)$min_price, 100000000000]);   
            }elseif ($max_price > 0){
                $query->andWhere(['between', 'price2',0, (float)$max_price]);
            }
                      
        }
        
        if(isset($params['select'])){
            $query->addSelect($params['select']);
        }
        
         
        
        
         
        if($count){
            
            $total_records = $query->count(1);
            
            if($offset>0){
                $query->offset($offset);
            }
            
            if($limit>0){
                $query->limit($limit);
            }
            
            if(isset($params['sort']) && ($sort = $params['sort']) != "")
            {
                switch ($sort){
                    case 1: // price inc
                        
                        $field = 'price2';
                        
                        if(!Yii::$app->collaborator->isGuest){
                            $field = 'price';                            
                        }
                        
                        $query->orderBy([$field=>SORT_ASC]);
                        break;
                    case 2: // price inc
                        
                        $field = 'price2';
                        
                        if(!Yii::$app->collaborator->isGuest){
                            $field = 'price';
                        }
                        $query->orderBy([$field=>SORT_DESC]);
                        break;
                        
                    case 3: // price inc
                        $query->orderBy(['network_label'=>SORT_ASC]);
                        break;
                    case 4: // price inc
                        $query->orderBy(['network_label'=>SORT_DESC]);
                        break;
                        
                    case 5: // price inc
                        $query->orderBy(['_id'=>SORT_ASC]);
                        break;
                    case 6: // price inc
                        $query->orderBy(['_id'=>SORT_DESC]);
                        break;
                    case 7: // price inc
                        $query->orderBy(['category_id'=>SORT_ASC]);
                        break;
                    case 8: // price inc
                        $query->orderBy(['category_id'=>SORT_DESC]);
                        break;
                        
                    case 9: // price inc
                        $query->orderBy(['partner_id'=>SORT_ASC]);
                        break;
                    case 10: // price inc
                        $query->orderBy(['partner_id'=>SORT_DESC]);
                        break;
                        
                    case 11: // updated_at inc
                        $query->orderBy(['updated_at'=>SORT_ASC]);
                        break;
                    case 12: // updated_at desc
                        
                        $query->orderBy(['updated_at'=>SORT_DESC]);
                        break;
                        
                }
            }
             
            
            return [
                'total_records'=>$total_records,
                'total_items'=>$total_records,
                'total_pages'=>ceil($total_records/$limit),
                'offset'=>$offset,
                'limit'=>$limit,
                'p'=>$p,
                'list_items' => $query->all(),
                
            ];
        }
        
        return $query->limit($limit)->all();
    }
    
    public function getItems($params)
    {
        $module_name = isset($params['module_name']) ? $params['module_name'] : null;
        
                         
        
        $limit = isset($params['limit']) ? $params['limit'] : 30;
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $p = isset($params['p']) && $params['p'] > 1 ? $params['p'] : 1;

        $offset = ($p - 1) * $limit;
         
        $count = isset($params['count']) && $params['count'] === true ? true : false;
        
        $cache = isset($params['cache']) && $params['cache'] === true ? true : false;
        
        $min_price = isset($params['min_price']) ? $params['min_price'] : 0;
        
        $driver = isset($params['driver']) ? $params['driver'] : 'mysql';
        
        switch ($driver){
            case 'mongodb':
                return $this->getItemsFromMongoDb($params);
                break;
                
            default:
                
                if($module_name !== null){
                    $query = SimonlineModuleModel::find();
                    $query->andWhere(['module_name'=>$module_name]);
                }else{
                    $query = SimonlineModel::find();
                }
                
                break;
        }
        
        
        
        // Conditions
        
        $validate_min_price = false;
        
        if(isset($params['sosim']) && ($sosim = trim($params['sosim'])) != ""){
            $sosim = ltrim($sosim, '0^ ');
            $sosim = rtrim($sosim, '$ ');
            
            $a = isset($params['a']) ? $params['a'] : rand(0,9);
            
            $b = isset($params['b']) ? $params['b'] : rand(0,9);
            
            $c = isset($params['c']) ? $params['c'] : rand(0,9);
            $d = isset($params['d']) ? $params['d'] : rand(0,9);
            $e = isset($params['e']) ? $params['e'] : rand(0,9);
            $f = isset($params['f']) ? $params['f'] : rand(0,9);
            
            $x = isset($params['x']) ? $params['x'] : rand(0,9);
            $y = isset($params['y']) ? $params['y'] : rand(0,9);
            
            
            $rg = [
                '.' => '',
                '*' => '.*',
                '_' => '[0-9]',
                '?' => '[0-9]',
                'a' => $a,
                'b' => $b,
                'c' => $c,
                'd' => $d,
                'e' => $e,
                'f' => $f,
                'x' => $x,
                'y' => $y
            ];
            
            $sosim = str_replace(array_keys($rg),array_values($rg), $sosim);
            
            if(is_numeric($sosim)){
                if(strlen($sosim) < 9){
                    $sosim = ".*$sosim.*";
                }elseif(strlen($sosim) > 9){
                    $sosim = substr($sosim, 0, 9);                     
                }
            }
            
            $query->andWhere(['REGEXP' ,'id',  "^$sosim\$"]);
        }
        
        
        if(isset($params['regex']) && $params['regex'] != ""){
            $query->andWhere(['REGEXP' ,'id',  $params['regex']]);
        }
        
        // filters
        $filters = isset($params['filters']) ? $params['filters'] : [];
        
        if(!empty($filters)){            
            foreach ($filters as $field => $val){
                switch ($field){
                    case 'price':
                        $val1 = max($min_price, isset($val['value1']) && is_numeric($val['value1']) ? $val['value1'] : 0);
                        $val2 = isset($val['value2']) && is_numeric($val['value2']) ? $val['value2'] : 0;
                        
                        if($val2 > $val1-1){                        
                            $query->andWhere(['between', 'price2', (float)$val1, (float)$val2]);                        
                            $validate_min_price = true;
                        }
                        break;
                        
                    case 'category1':
                        $val1 = isset($val['value1']) ? $val['value1'] : '';
                        
                        if($val1 != "" && is_numeric($val1)){
                            $query->andWhere(['network_id' =>$val1]);  
                        }elseif($val1 != "" ){
                            $query->andWhere(['network_label' => $val1]); 
                        }
                        
                        break;
                        
                    case 'category2':
                        $val1 = isset($val['value1']) ? $val['value1'] : '';
                        
                        if($val1 != "" && is_numeric($val1)){
                            $query->andWhere(['category_id' =>$val1]);
                        }elseif($val1 != "" ){
                            $query->andWhere(['category_label' => $val1]);
                        }
                        
                        break;
                }
                
                // regex
                if(($regex = trim($val['regex'])) != ""){
                    
                    switch ($regex){
                        case 'aabb$':
                            $rx = [];
                            
                            for($i = 0; $i<10; $i++){
                                $rx [] = "$i{2}";
                            }
                            
                            $regex = implode('|', $rx);
                             
                            $query->andWhere(['REGEXP' ,'id',  "($regex)\$"]); 
                            $query->andWhere(['REGEXP' ,'id',  "($regex).{2}\$"]);                                                         
                            
                            break;
                        case 'aabbcc$':
                            $rx = [];
                            
                            for($i = 0; $i<10; $i++){
                                $rx [] = "$i{2}";
                            }
                            
                            $regex = implode('|', $rx);
                            
                            $query->andWhere(['REGEXP' ,'id',  "($regex)\$"]);
                            $query->andWhere(['REGEXP' ,'id',  "($regex).{2}\$"]);
                            $query->andWhere(['REGEXP' ,'id',  "($regex).{4}\$"]);
                            
                            break;
                            
                            // luc quy
                        case 'aaaaaa':
                        case '^aaaaaa':
                        case '^aaaaaa$':                            
                        case 'aaaaaa$':
                            
                            // ngu quy
                        case 'aaaaa':
                        case '^aaaaa':
                        case '^aaaaa$':
                        case 'aaaaa$':
                            
                            // tu quy
                        case 'aaaa':
                        case '^aaaa':
                        case '^aaaa$':
                        case 'aaaa$':
                            
                            // tam hoa
                        case 'aaa':
                        case '^aaa':
                        case '^aaa$':
                        case 'aaa$':
                            
                            $rx = [];
                            
                            $r1 = str_replace(['^','$'], '', $regex);
                            
                            $len = strlen($r1);
                            
                            for($i = 0; $i<10; $i++){
                                
                                $x = '';
                                
                                if(substr($regex, 0,1) != '^'){
                                    $x .= "[^$i]";
                                }
                                
                                $x .= "$i{{$len}}";
                                
                                if(substr($regex, -1) != '$'){
                                    $x .= "[^$i]";
                                }
                                
                                $rx [] = $x;
                            }
                             
                            
                            $regex = str_replace($r1, '(' . implode('|', $rx) . ')', $regex);
 
                            
                            $query->andWhere(['REGEXP' ,'id',  $regex]); 
                            
                            break;
                            
                        case 'abba$':
                        case 'abab$':
                            $rx = [] ;
                            $r1 = str_replace(['^','$'], '', $regex);
                            
                            
                            for($a = 0; $a<10; $a++){
                                
                                for($b = 0; $b<10; $b++){
                                    if($b != $a){
                                        $rx[] = str_replace(['a','b'], [$a,$b], $r1);
                                    }
                                }
                            }
                            $regex = str_replace($r1, '(' . implode('|', $rx) . ')', $regex);
                            $query->andWhere(['REGEXP' ,'id',  $regex]); 
                        
                        break;
                        
                        
                        case 'abccba$':
                            $rx = [] ;
                            $r1 = str_replace(['^','$'], '', $regex);
                            
                            
                            for($a = 0; $a<10; $a++){
                                
                                for($b = 0; $b<10; $b++){
                                    if($a != $b){
                                        for($c = 0; $c<10; $c++){
                                            if($a != $c && $b != $c){
                                                $rx[] = str_replace(['a','b','c'], [$a,$b,$c], $r1);
                                            }
                                        }
                                    }
                                }
                            }
                             
                            $regex = str_replace($r1, '(' . implode('|', $rx) . ')', $regex);
                            $query->andWhere(['REGEXP' ,'id',  $regex]);
                            
                             
                            
                            break;
                            
                        case 'ababab$':
                            $rx = [] ;
                            $r1 = str_replace(['^','$'], '', $regex);
                            
                            
                            for($a = 0; $a<10; $a++){
                                
                                for($b = 0; $b<10; $b++){
                                    if($a != $b){
                                         
                                        $rx[] = str_replace(['a','b' ], [$a,$b], $r1);
                                             
                                        
                                    }
                                }
                            }
                             
                            
                            $regex = str_replace($r1, '(' . implode('|', $rx) . ')', $regex);
                            $query->andWhere(['REGEXP' ,'id',  $regex]);
                            
                            
                            
                            break;
                            
                            
                        case 'ababab|abcabc$':
                            $rx = [] ;
                            $r1 = str_replace(['^','$'], '', $regex);
                            
                            
                            for($a = 0; $a<10; $a++){
                                
                                for($b = 0; $b<10; $b++){
                                    if($a != $b){
                                        
                                        $rx[] = str_replace(['a','b' ], [$a,$b], $r1);
                                        
                                        
                                    }
                                }
                            }
                            
                            $regex = '(' . implode('|', $rx) . ')$';
                            
                            // abc.abc
                            $rx = [];
                            for($a = 0; $a<10; $a++){
                                
                                for($b = 0; $b<10; $b++){
                                    
                                        for($c = 0; $c<10; $c++){
                                            if($b != $c || $a!=$c || $a != $b){
                                                $rx[] = "$a$b$c$a$b$c";
                                            }
                                        }
                                   
                                }
                            }
                             
                            $regex2 = '(' . implode('|', $rx) . ')$';
                            
                            $query->andWhere(['or', ['REGEXP' ,'id',  $regex],['REGEXP' ,'id',  $regex2]]);
                            
                            
                            
                            break;
                        
                            
                        case 'abc$':
                        case 'abcd$':
                        case 'abcde$':
                        case 'abcdef$':
                        case 'abceefg$':
                            $rx = [] ;
                            $r1 = str_replace(['^','$'], '', $regex);
                            
                            
                            for($a = 0; $a<11 - strlen($r1); $a++){
                                
                                $so = [$a];
                                
                                for($b = 1; $b<strlen($r1); $b++){
                                    $so[] = $so[count($so)-1] + 1; 
                                }
                                
                                $rx[] = '([^'.($so[0]-1).']'. implode('', $so).')';
                            }
                             
                            $regex = str_replace($r1, '(' . implode('|', $rx) . ')', $regex);
                            $query->andWhere(['REGEXP' ,'id',  $regex]);
                            
                             
                            
                            break;
                            
                            
                            
                        default:
                             
                            
                            $query->andWhere(['REGEXP' ,'id',  $regex]); 
                            break;
                    }
                    
                    
                    
                    
                }
            }
        }
        
        if(!$validate_min_price && $min_price > 0){
            //$query->andWhere(['>' , 'price2', $min_price]);
        }
        
        // simfilter 
        if(isset($params['sim_filter']) && !empty($sim_filter = $params['sim_filter'])){
            
            //
            if(isset($sim_filter['network_id']) && $sim_filter['network_id']>0){
                $query->andWhere(['network_id' => $sim_filter['network_id']]);
            }
            
            if(isset($sim_filter['category_id']) && $sim_filter['category_id']>0){
                $query->andWhere(['category_id' => $sim_filter['category_id']]);
            }
            
            if(isset($sim_filter['partner_id']) && $sim_filter['partner_id']>0){
                $query->andWhere(['partner_id' => $sim_filter['partner_id']]);
            }
            
            if(isset($sim_filter['category2_id']) && $sim_filter['category2_id']>0){
                $query->andWhere(['category2_id' => $sim_filter['category2_id']]);
            }
            
            if(isset($sim_filter['category3_id']) && $sim_filter['category3_id']>0){
                $query->andWhere(['category3_id' => $sim_filter['category3_id']]);
            }
            
            if(isset($sim_filter['regex']) && ($regex = $sim_filter['regex']) != ""){
                
                $val = explode('|', $regex);
                 
                
                switch ($val[0]){
                    
                    case 'price':
                        $val1 = max($min_price, isset($val[1]) && is_numeric($val[1]) ? $val[1] : 0);
                        $val2 = isset($val[2]) && is_numeric($val[2]) ? $val[2] : 0;
                        
                        if($val2 > $val1-1){
                            $query->andWhere(['between', 'price2', (float)$val1, (float)$val2]);
                            $validate_min_price = true;
                        }
                        break;
                    
                    default:
                        $query->andWhere(['REGEXP' ,'id',   $sim_filter['regex']]);
                        
                        break;
                }
                
                
            }
             
        }
        
        if(isset($params['select'])){
            $query->addSelect($params['select']);
        }
          
//         view($query->createCommand()->getRawSql(),1,1);
        // count

        if($count){
            
            $total_records = $query->count(1);
            
            if($offset>0){
                $query->offset($offset);
            }
            
            if($limit>0){
                $query->limit($limit);
            }
             
            
            return [
                'total_records'=>$total_records,
                'total_items'=>$total_records,
                'total_pages'=>ceil($total_records/$limit),
                'offset'=>$offset,
                'limit'=>$limit,
                'p'=>$p,
                'list_items' => $query->asArray()->all(),
                
            ];
        }
        
        if($offset>0){
            $query->offset($offset);
        }
        
        if($limit>0){
            $query->limit($limit);
        }
        
        if(isset($params['orderBy'])){
            $query->orderBy($params['orderBy']);
        }
        
        return $query->asArray()->all();
        
    }
    
    
    //public function 
    
    
    public function getStaticTotalSim()
    {        
        $filename = Yii::getAlias('@runtime/cache/s'.__SID__.'/simonline/thongke_sim.json');
        
        $time = YII_DEBUG ? getParam('__over_time', 7200) : 43200;

        if(file_exists($filename) && __TIME__ - @filemtime($filename) < $time){
          
            
            return json_decode(file_get_contents($filename),1);
        }
               
        $networks = [
            1 => 'viettel',
            2 => 'vinaphone',
            3 => 'mobifone',
            4 => 'vietnamobile',
            5 => 'gmobile',
//             'sfone'
        ];
        
        $data = []; 
        
        $total = 0;
        
        foreach ($networks as $network_id => $network_label){
            
            $query = new \yii\mongodb\Query();
            $query->from('simonline')->where(['network_id' => $network_id]);
            
            $data[$network_label] = $query->count(1) ;
            
            
//             $data[$network] = SimonlineModel::find()->where(['network_label'=>$network])->count(1) ;
            $total += $data[$network_label];
        }
      
        
        writeFile($filename, json_encode($result = ['data' => $data, 'total'=>$total]));
        
        return $result;
    }
    
    
    public function getAllType($params = [])
    {
        $query = SimonlineTypeModel::find();
        
        if(isset($params['status'])){
            $query->andWhere(['status'=>$params['status']]);
        }
        
        if(isset($params['type_id'])){
            $query->andWhere(['type_id'=>$params['type_id']]);
        }else{
            $query->andWhere(['type_id'=>1]);
        }
        
        if(isset($params['orderBy'])){
            $query->orderBy($params['orderBy']);
        }else{
            $query->orderBy(['priority'=>SORT_ASC]);
        }
        
        return $query->asArray()->all();
        
    }
    
    
    private $_types2;
    public function getType($id)
    {
        
        if(!isset($this->_types2[$id])){
            $query = SimonlineTypeModel::find();
            
            $query->andWhere(['id'=>$id]);
            
            $this->_types2[$id] = $query->asArray()->one();
        }
        
        return $this->_types2[$id];
    }
    
    
    public function getAllNetwork($params = [])
    {
        $query = SimonlineModel::find()->from('simonline_network');
        
        if(isset($params['status'])){
            $query->andWhere(['status'=>$params['status']]);
        }
        
        return $query->orderBy(['id'=>SORT_ASC])->asArray()->all();
    }
    
    
    public function getNetwork($id)
    {
        $query = SimonlineModel::find()->from('simonline_network');
         
        $query->andWhere(['id'=>$id]);
         
        
        return $query->asArray()->one();
    }
    
     
    public function findNetworkBySim($id)
    {
        $sosim = ltrim($id, '0 ');
        $network = '';
        switch (substr($sosim, 0,2)){
            case '96':
            case '97':
            case '98':
            case '86':
            case '32':
            case '33':
            case '34':
            case '35':
            case '36':
            case '37':
            case '38':
            case '39':
                $network_id = 1;
                $network = 'viettel';
                break;
                // mobile
            case '90':
            case '93':
            case '89':
            case '70':
            case '79':
            case '77':
            case '76':
            case '78':
                $network_id = 3;
                $network = 'mobifone';
                break;
                // vina
            case '91':
            case '94':
            case '88':
            case '81':
            case '82':
            case '83':
            case '84':
            case '85':
                $network_id = 2;
                $network = 'vinaphone';
                break;
                // vietnammobile
            case '92':
            case '56':
            case '58':
            case '52':
                $network_id = 4;
                $network = 'vietnamobile';
                break;
                // gmobile
            case '99':
            case '59':
                $network_id = 5;
                $network = 'gmobile';
                break;
                
            case '87':
                $network_id = 6;
                $network = 'iTelecom';
                break;
        }
        
        if(!isset($network_id)){
            return ['id' => 0, 'name' => 'unknown'];
        }
        
        return ['id' => $network_id, 'name' => $network];
    }
    
    public function buildPattern($pattern, $string)
    {

        $string = preg_replace('/\D/', '', $string);
        
        switch ($pattern){
            
            case '_DAI_CAT_':
                return $this->validateDaiCat($string);
                break;
            case '[^a]aaa$':
            case '[^a]aaaa$':
            case '[^a]aaaaa$':
            case '[^a]aaaaaa$':
            case '.*[^a]aaaa[^a].*$':
            case '.*[^a]aaaaa[^a].*$':
            case '.*[^a]aaaaaa[^a].*$':
            case '(19[5-9][0-9])|(20[0-1][0-9])$':
            case '[^a]bcd$':
            case '[^a]bcde$':
            case '[^a]bcdef$':
            case '[^a]bcdefg$':
            case '[^a]bcdefgh$':
            case '(68|86|88|66)$':
            case '(39|79)$':
            case '(38|78)$':
             
           
                
                
                
                $regex = $this->getRegex(['regex' => $pattern]);

                if($regex != ""){

                    $rxs = explode('|', $regex);

                    foreach ($rxs as $rg){

                        $rg = trim($rg, '($)');

                        preg_match('/'.$rg.'$/', $string,$matche);

                        if(!empty($matche)){

                            return true;
 
                            break;
                        }
                    }

                }
                
                break;
                
                
            case '__KEP4__':
                
                /**
                 *  aabbccdd
                 *  aabbaacc
                 *  aabbbbaa
                 *
                 * */
                $s1 = substr($string, -2);
                $s2 = substr($string, -4,-2);
                $s3 = substr($string, -6,-4);
                $s4 = substr($string, -8,-6);
                
                if($s1 % 11 == 0 
                    && $s2 % 11 == 0 
                    && $s3 % 11 == 0 
                    && $s4 % 11 == 0
                    && $s3 != $s4
                    && ($s1 . $s2) != ($s3 . $s4)
                    && !($s1 == $s2 && $s1 == $s3) 
                    ){
                    return true;
                }
                                
                
                break;
            
            default:
                
                $state = $break = false;
                
               
                switch ($pattern) {
                    case '[ababab|abcabc]$':
                    case '__TAXI__':
                        
                        $s1 = substr($string, -2);
                        $s2 = substr($string, -4,-2);
                        $s3 = substr($string, -6,-4);
                         
                        
                        if( $s1 == $s2 && $s1 == $s3) return true;
                        
                        $s1 = substr($string, -3);
                        $s2 = substr($string, -6,-3);
                        
                        if( $s1 == $s2) return true;
                        
                        $s1 = substr($string, -4);
                        $s2 = substr($string, -8,-4);
                        if( $s1 == $s2) return true;
                        
                        break;
                     
                }
                
                if($pattern == 'abcabc$'){
                    
                    $s1 = substr($string, -3);
                    $s2 = substr($string, -6,-3);
                                                             
                    return $s1 == $s2;
                    
                     
                }
                
                if($pattern == 'abcdabcd$'){
                    
                    $s1 = substr($string, -4);
                    $s2 = substr($string, -8,-4); 
                    return $s1 == $s2;
                }
                 
                
                if($pattern == 'abbacc$'){ 
                     
                }
                
                
                $patterns = explode('|', $pattern);
                
                foreach ($patterns as $pattern){
                    
                    $pattern = preg_replace('/\W/i', '', $pattern);
                    
                    
                    
                    $len = strlen($pattern);
                    
                    $str = substr($string, $len * -1);
                    
                     
                    
                    $s1 = [];
                    
                    $s2 = '';
                    
                    $daxet = [];
                    
                    for ($i = 0;  $i< $len; $i++){
                        
                        
                        if(!isset($s1[$pattern[$i]])){
                            
                            if(in_array($str[$i], $daxet)){
                                break;
                            }
                            
                            $s1[$pattern[$i]] = $str[$i];
                            
                            $s2 .= $str[$i];
                            
                            $daxet [] = $str[$i] ;
                            
                            
                            
                            
                        }else{
                            
                            $s2 .= $s1[$pattern[$i]];
                            
                        }
                        
                        
                    }
                    
                    //if($break) break;
                     
                    
                    if ($s2 == $str) return true;
                    
                    
                }
                break;
        }
 
        
        
        return false;
         
    }
    
    
    public function validateRegex($pattern, $string){
       
        $state = false;
         

        switch ($pattern){                            
            default:                
                return $this->buildPattern($pattern, $string);
                break;
                
        }
        
        return $state;
    }
    
    private $_types;
    
    public function findSimType($id, $limit = -1, $t = [1,2,3])
    {
        if(!isset($this->_types[1])){
            $this->_types[1] = $this->getAllType(['type_id'=>[1], 'orderBy' => ['priority' => SORT_ASC]]);
        }
        
        $types = $this->_types[1];
        
        if(!isset($this->_types[2])){
            $this->_types[2] = $this->getAllType(['type_id'=>[2], 'orderBy' => ['priority' => SORT_ASC]]);
        }
        
        $types2 = $this->_types[2];
        
        if(!isset($this->_types[3])){
            $this->_types[3] = $this->getAllType(['type_id'=>[3], 'orderBy' => ['priority' => SORT_ASC]]);
        }
        
        $types3 = $this->_types[3];
        
//         $types2 = Yii::$app->frontend->simonline->getAllType(['type_id'=>[2], 'orderBy' => ['priority' => SORT_ASC]]);
        
        $rs = [];
        
        if(in_array(1, $t)){
            foreach ($types as $k => $type){
                
                $break = false;
                
                if($this->validateRegex($type['regex'], $id)){
                    if($limit == 1) return $type;
                    $rs[] = $type;
                    $break = true;
                    break;
                } 
                
                if($break) break;
                
            }
        }
        
        if(in_array(2, $t)){
            foreach ($types2 as $type){
                
                $break = false;
                
                if($this->validateRegex($type['regex'], $id)){
                    if($limit == 1) return $type;
                    $rs[] = $type;
                    $break = true;
                    break;
                } 
                
                if($break) break;
                
            }
        }
        
        
        if(in_array(3, $t)){
            foreach ($types3 as $type){
                
                $break = false;
                
                if($this->validateRegex($type['regex'], $id)){
                    if($limit == 1) return $type;
                    $rs[] = $type;
                    $break = true;
                    break;
                } 
                
                if($break) break;
                
            }
        }
        
        return $rs;
    }
    
    
    public function getSiminfo($id)
    {
        $sosim = ltrim(str_replace(['.', ' ', '-', '[', ']', '(',')'], '', $id),'0');
        
        $type = $this->findSimType($sosim,1, [1]);
        
         
        $category_id = !empty($type) ? $type['id'] : 0;
        
        $categorylabel = !empty($type) ? $type['name'] : '';
        
        $type = $this->findSimType($id, 1 , [2]); 
        
        $category2_id = 0;
        
        if(!empty($type)){
            $category2_id = $type['id'];
        }
        
        $type3 = $this->findSimType($id, 1 , [3]);
        
        $category3_id = 0;
        
        if(!empty($type3)){
            $category3_id = $type3['id'];
        }
        
        
        return [
            'id' => $sosim,
            'display'=>makePhoneNumber($id),
            'network_id'=>(int)$this->findNetworkBySim($sosim)['id'],
            'network_label'=>$this->findNetworkBySim($sosim)['name'],
            'category_id'=>(int)$category_id,
            'category_label'=>$categorylabel,
            'category2_id'=>(int)$category2_id,
            'category3_id'=>(int)$category3_id
        ];
    }
    
    
    public function phonePattern()
    {
        $space = '[\-\. ]';
        
        
        $patterns = [
        
        // Dòng 0 chấm
        '([0][1-9][0-9]{8})',                                                   // 0912345678
        '([35789][0-9]{8})',                                                   // 0912345678
        // Dòng 1 chấm
        '([0][1-9]'.$space.'[0-9]{8})',                                           // 09.12345678
        '([0][1-9][0-9]'.$space.'[0-9]{7})',                                      // 091.2345678
        '([0][1-9][0-9]{2}'.$space.'[0-9]{6})',                                   // 0912.345678
        '([0][1-9][0-9]{3}'.$space.'[0-9]{5})',                                   // 09123.45678
        '([0][1-9][0-9]{4}'.$space.'[0-9]{4})',                                   // 091234.5678
        '([0][1-9][0-9]{5}'.$space.'[0-9]{3})',                                   // 0912345.678
        '([0][1-9][0-9]{6}'.$space.'[0-9]{2})',                                   // 09123456.78
        '([0][1-9][0-9]{7}'.$space.'[0-9]{1})',                                   // 091234567.8
        
        // Dòng 2 chấm
        '([0]'.$space.'[1-9][0-9]{7}'.$space.'[0-9]{1})',                           // 0.98888889.0
        '([0]'.$space.'[1-9][0-9]{6}'.$space.'[0-9]{2})',                           // 0.9888888.99
        '([0]'.$space.'[1-9][0-9]{5}'.$space.'[0-9]{3})',
        '([0]'.$space.'[1-9][0-9]{4}'.$space.'[0-9]{4})',
        '([0]'.$space.'[1-9][0-9]{3}'.$space.'[0-9]{5})',
        '([0]'.$space.'[1-9][0-9]{2}'.$space.'[0-9]{6})',                           // 0.988.888899
        '([0]'.$space.'[1-9][0-9]{1}'.$space.'[0-9]{7})',
        
        '([0][1-9]'.$space.'[0-9]{7}'.$space.'[0-9]{1})',                           // 09.8888889.0
        '([0][1-9]'.$space.'[0-9]{6}'.$space.'[0-9]{2})',
        '([0][1-9]'.$space.'[0-9]{5}'.$space.'[0-9]{3})',
        '([0][1-9]'.$space.'[0-9]{4}'.$space.'[0-9]{4})',
        '([0][1-9]'.$space.'[0-9]{3}'.$space.'[0-9]{5})',
        '([0][1-9]'.$space.'[0-9]{2}'.$space.'[0-9]{6})',
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{7})',
        
        '([0][1-9][0-9]'.$space.'[0-9]{6}'.$space.'[0-9]{1})',                      // 098.888889.0
        '([0][1-9][0-9]'.$space.'[0-9]{5}'.$space.'[0-9]{2})',
        '([0][1-9][0-9]'.$space.'[0-9]{4}'.$space.'[0-9]{3})',
        '([0][1-9][0-9]'.$space.'[0-9]{3}'.$space.'[0-9]{4})',
        '([0][1-9][0-9]'.$space.'[0-9]{2}'.$space.'[0-9]{5})',
        '([0][1-9][0-9]'.$space.'[0-9]{1}'.$space.'[0-9]{6})',
        
        '([0][1-9][0-9]{2}'.$space.'[0-9]{5}'.$space.'[0-9]{1})',                      // 0988.88889.0
        '([0][1-9][0-9]{2}'.$space.'[0-9]{4}'.$space.'[0-9]{2})',
        '([0][1-9][0-9]{2}'.$space.'[0-9]{3}'.$space.'[0-9]{3})',
        '([0][1-9][0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{4})',
        '([0][1-9][0-9]{2}'.$space.'[0-9]{1}'.$space.'[0-9]{5})',
        
        '([0][1-9][0-9]{3}'.$space.'[0-9]{4}'.$space.'[0-9]{1})',                      // 09888.8889.0
        '([0][1-9][0-9]{3}'.$space.'[0-9]{3}'.$space.'[0-9]{2})',
        '([0][1-9][0-9]{3}'.$space.'[0-9]{2}'.$space.'[0-9]{3})',
        '([0][1-9][0-9]{3}'.$space.'[0-9]{1}'.$space.'[0-9]{4})',
        
        '([0][1-9][0-9]{4}'.$space.'[0-9]{3}'.$space.'[0-9]{1})',                      // 098888.889.0
        '([0][1-9][0-9]{4}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',
        '([0][1-9][0-9]{4}'.$space.'[0-9]{1}'.$space.'[0-9]{3})',
        
        '([0][1-9][0-9]{5}'.$space.'[0-9]{2}'.$space.'[0-9]{1})',                      // 0988888.89.0
        '([0][1-9][0-9]{5}'.$space.'[0-9]{1}'.$space.'[0-9]{2})',
        
        
        
        // Dòng 3 chấm
        
        
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{6})',   // 09 1 1 123456
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{5})',
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{3}'.$space.'[0-9]{4})',
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{4}'.$space.'[0-9]{3})',
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{5}'.$space.'[0-9]{2})',
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{6}'.$space.'[0-9]{1})',
        
        '([0][1-9]'.$space.'[0-9]{1}'.$space.'[0-9]{5}'.$space.'[0-9]{2})',
        '([0][1-9]'.$space.'[0-9]{2}'.$space.'[0-9]{4}'.$space.'[0-9]{2})',
        '([0][1-9]'.$space.'[0-9]{3}'.$space.'[0-9]{3}'.$space.'[0-9]{2})',   // 08.555.222.83
        '([0][1-9]'.$space.'[0-9]{4}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',
        '([0][1-9]'.$space.'[0-9]{5}'.$space.'[0-9]{1}'.$space.'[0-9]{2})',
        
        '([0][1-9]'.$space.'[0-9]{3}'.$space.'[0-9]{2}'.$space.'[0-9]{3})',
        '([0][1-9]'.$space.'[0-9]{2}'.$space.'[0-9]{3}'.$space.'[0-9]{3})',
        '([0][1-9]'.$space.'[0-9]{4}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',
        
        '([0][1-9][0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{5}'.$space.'[0-9]{1})',     // 085.222.111.6
        '([0][1-9][0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{4}'.$space.'[0-9]{1})',
        '([0][1-9][0-9]{1}'.$space.'[0-9]{3}'.$space.'[0-9]{3}'.$space.'[0-9]{1})',
        '([0][1-9][0-9]{1}'.$space.'[0-9]{4}'.$space.'[0-9]{2}'.$space.'[0-9]{1})',
        '([0][1-9][0-9]{1}'.$space.'[0-9]{5}'.$space.'[0-9]{1}'.$space.'[0-9]{1})',
        
        '([0][1-9][0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{3})',   // 091 22 33 333
        '([0][1-9][0-9]{3}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{3})',   // 09123 3 3 333
        '([0][1-9][0-9]{3}'.$space.'[0-9]{2}'.$space.'[0-9]{3})',                   // 09123 33 333
        '([0][1-9]'.$space.'[0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{4})',           // 09 66 88 6666
        
        
        
        '([0][1-9][0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',   // 0912 12 12 12
        '([0][1-9]'.$space.'[0-9]{4}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',           // 09 1234 12 12
        '([0][1-9][0-9]{1}'.$space.'[0-9]{3}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',   // 091 123 12 12
        '([0][1-9][0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{1}'.$space.'[0-9]{4})',   // 091.24.7.1981
        
        
        '([0][1-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{4})',       // 03.5.7.99.1234
        '([0][1-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{2}'.$space.'[0-9]{2})',       // 08.14.15.16.11
        
        
        
        '([0][1-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{4})',       // 03.5.7.9.9.1234
        
        '([0][1-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{1}'.$space.'[0-9]{2})',    // 08.4.5.6.07.8.19
        '([0][1-9]{3}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{1}'.$space.'[0-9]{2}'.$space.'[0-9]{1})',    // 0848.7.8.9.10.1	
        
        //             '[0-9]{2,4}[\.][0-9]{6,8}',
        
        //             '[0-9]{3}[\s.][0-9]{2}[\s.][0-9]{2}[\s.][0-9]{3}', // 091.66.35.111
        //             '[0-9]{2,4}[\s.][0-9]{2}[\s.][0-9]{2}[\s.][0-9]{2,4}', // 09.12.16.0111
        //             '[0-9]{5}[\s.][0-9]{1}[\s.][0-9]{1}[\s.][0-9]{2,4}', // 09166.3.2.111
        
        //             '[0-9]{2,4}[\s][0-9]{2,3}[\s][0-9]{2,4}[\s][0-9]{0,2}',
        //             '[0-9]{2,4}[\s][0-9]{2,3}[\s][0-9]{2,4}',
        //             '[0-9]{2,6}[\s.][0-9]{2,4}[\s.][0-9]{2,4}',
        
        
        //             '[0-9]{10}',
        //             '[0-9]{3}[\-][0-9]{3}[\-][0-9]{4}',
        ];
        
        
        return '/'.implode('|', $patterns).'/';
    }
    
    
    public function validatePhoneNumber($string)
    {
        $pattern = $this->phonePattern();
        preg_match($pattern, $string, $match);
        
        if(!empty($match)){
            return true;
        }
        return false;
    }
    
    public function extractSimFormString($string, $params = [])
    {
        
//         $string = \yii\helpers\Html::encode(str_replace(['%0A','\n\r','\r','\n','
// '], ['***'], $string));
        
        // extract sim
        
       
        
         
        $pattern = $this->phonePattern();
        
       
        
        preg_match_all($pattern, $string, $match);
         
        
        $string = preg_replace($pattern, 'xxx', $string);
         
//        view($string);
        
        $sims = [];
        if(!empty($match[0])){
            foreach ($match[0] as $k => $sim){
                $sims[$k] = str_replace([' ',','], ['.'], trim($sim));
            }
        }
          
        
        // extract giá
        $patterns = [
            '[0-9]{1,4}[\s]triệu|[0-9]{1,4}triệu',
            '[0-9]{1,4}[\s]triệu|[0-9]{1,4}[\s]triệu',
            '[0-9]{1,4}[\s]tr|[0-9]{1,4}tr',
            '[0-9]{1,4}[\s]tr|[0-9]{1,4}[\s]tr',
            '[0-9]{1,6}k',
            '[0-9]{1,6}[\s]k',
            
            '[1-9][0-9]{0,3}[,\.][0-9]{3}[,\.][0-9]{3}[,\.][0-9]{3}',
            '[1-9][0-9]{0,3}[,\.][0-9]{3}[,\.][0-9]{3}',
            '[1-9][0-9]{0,3}[,\.][0-9]{3}',        
            '[1-9][0-9]{0,3}[,\.][0-9]{0,3}',
            '[1-9][,\.][0-9]{0,3}',
            '[1-9][0-9]{0,16}',
            
            
            
//             '[0-9]{1,16}',
//             '[0-9]{1,16}[\n\r]',
            
            
            
            
            
            
        ];
//         view($string);
        
        $pattern = '/'.implode('|', $patterns).'/';
        
        preg_match_all($pattern, str_replace(['.','.'], '.', $string), $match);
          
       
        
        $gia = [];
        if(!empty($match[0])){
            foreach ($match[0] as $k => $sim){
                
                $price = (float) preg_replace('/[\D^\.,]/', '', $sim);
                 
                preg_match('/[\d\.,].*/', $sim, $m2);
                
                if(!empty($m2)){
                    
                    if($price > 1000){
                        
                    }else{
                    
                        $price = (float) trim(str_replace(',', '.', $m2[0]));
                    }
                }
                 
                
                preg_match('/\D.*|\D.*[\s]|[\D\.].*|[\D\.].*[\s]/', $sim, $m2);
                  
                 
                
                $ext = 1;
                if(isset($params['unit'])){
                    switch ($params['unit']) {
                        case 'tr':
                            $ext = 1000000;
                            break;
                        
                        case 'k':
                            $ext = 1000;
                            break;
                        case 'd': case '₫': case 'đ': case 'vnd': case 'vnđ':
                        case 'D': case 'Đ': case 'VND': case 'VNĐ':
                            $ext = 1;
                            break;
                    }
                }
                
                if(!empty($m2)){
                    switch (trim($m2[0])){
                        case 'tr':
                        case 'triệu':
                            $price *= 1000000;
                            break;
                        case 'k':
                            $price *= 1000;
                            break;
                        case 'd': case '₫': case 'đ': case 'vnd': case 'vnđ':
                        case 'D': case 'Đ': case 'VND': case 'VNĐ':
                            $price *= 1;
                            break;
                        default:
                            $price = trim(str_replace(['\r\n'], '', $price));
                           
                            
                            
                            
                            if(is_numeric($price)){
                                
                                if($ext > 1){
                                    $price *= $ext;
                                }elseif($price<300){
                                    $price = $price * 1000000;
                                }elseif($price<100000){
                                    $price = $price * 1000;
                                }
                            }
                            break;
                    }
                }else{
                    
                    $price = trim(str_replace(['\r\n'], '', $price));
                    
                    
                    
                    if(is_numeric($price)){
                        if($ext > 1){
                            $price *= $ext;
                        }elseif($price<300){
                            $price = $price * 1000000;
                        }elseif($price<100000){
                            $price = $price * 1000;
                        }
                    }
                }
                 
                
                $gia[$k] = $price; //str_replace([' ',','], [' '], trim($sim));
                 
            }
        } 
        
        $state = count($sims) === count($gia);
        
        if(!$state && empty($gia)){
            $state = true;
        }
        
        $data = [];
        if(!empty($sims)){
            foreach($sims as $k => $sim){
                $data[] = array_merge([
                    'id' =>  str_replace(['.'], '', ltrim($sim,'0')),
                    'display'=>makePhoneNumber($sim),
                    'price2' => isset($gia[$k]) ? $gia[$k] : 0,
                ], $this->getSiminfo($sim));
            }
        }
        
        return [
            'state' => $state,
            'data'  => $data
        ];
        
    }
    
    
    public function updateSimToModule($sosim, $module_name)
    {
        
        $sim_id = ltrim(preg_replace('/\D/', '', $sosim),'0');
        
        $query = new \yii\mongodb\Query();
        $query->from('simonline')->where(['_id' =>$sim_id]);
        $sim = $query->one();
        
        Yii::$app->db->createCommand()->delete(SimonlineModuleModel::tableName(), ['id' => $sim_id])->execute();
         
        
        if(!empty($sim)){
            $sim['module_name'] = $module_name;
            
            if(!isset($sim['id'])){
                $sim['id'] = $sim['_id'];
            }
            
            unset($sim['_id']);
            Yii::$app->db->createCommand()->insert(SimonlineModuleModel::tableName(), $sim)->execute();
        }
        
    }
    
    
    public function updateSimmodule($data, $params){
        
        $module_name = isset($params['module_name']) ? $params['module_name'] : '';
        
        $existed = [];
        
        $collection = Yii::$app->mongodb->getCollection('simonline');
        
        if($module_name != ""){
            
            $price_public = isset($params['price_public']) ? $params['price_public'] : false;
            
            $l = isset($data['data']) ? $data['data'] : $data;
            $state = isset($data['state']) ? $data['state'] : true;
            
            $profit = isset($params['profit']) ? $params['profit'] : 0;
            
            
            if(!empty($l)){
                
                
                if(isset($params['remove_old']) && $params['remove_old'] === true){
                    Yii::$app->db->createCommand()->delete(SimonlineModuleModel::tableName(), ['module_name' => $module_name])->execute();
                }
                
                foreach ($l as $sim){
                    
                    
                    if(isset($sim['price2']) && $sim['price2'] == 0){
                        unset($sim['price2']);
                        
                        if(isset($sim['price1'])){
                            unset($sim['price1']);
                        }
                        if(isset($sim['price'])){
                            unset($sim['price']);
                        }
                        
                    }elseif(isset($sim['price2'])){
                        
                        if(!$price_public && $profit > 0){
                            $sim['price'] = $sim['price2'];
                            
                            $sim['price2'] *= (1 + ($profit/100));
                        }else {
                           // $sim['price'] = $sim['price2'] * 0.9;
                        }
                    
                    }
                    
                    $sim['updated_at'] = time();
                    
                    if(isset($params['partner_id'])){
                        $sim['partner_id'] = $params['partner_id'];
                    }
                    
                    if(isset($params['partner_label'])){
                        $sim['partner_label'] = $params['partner_label'];
                    }
                    
 
                    
                    $sim['id'] = (string) $sim['id'];
                    
                    $query = new \yii\mongodb\Query();
                    $query->from('simonline')->where(['_id' => $sim['id']]);
                    $s = $query->one();
                    
                     
                    
                    // 
                    if(isset($sim['module_name'])){
                        
                        unset($sim['module_name']);
                    }
                    
                     
                    
                    $aa =[
                        'price', 'price1','price2', 'partner_id','updated_at', 'score','network_id', 'category_id','category2_id','category3_id', 'status','type_id'
                    ];
                    
                    foreach ($aa as $field){
                        $sim[$field] = isset($sim[$field]) ? (int) $sim[$field] : 0;
                    }
                    
                    if(!empty($s)){
                        
                        $arrUpdate = $sim;
                        
                        foreach ($aa as $field){
                            if(!(isset($arrUpdate[$field]) && $arrUpdate[$field]>0)){
                                if(isset($arrUpdate[$field])) unset($arrUpdate[$field]);
                            }
                        }
                        
                        if(!(isset($arrUpdate['partner_id']) && $arrUpdate['partner_id'] > 0)){
                            if(isset($arrUpdate['partner_label'])) unset($arrUpdate['partner_label']);
                        }
                        
                        if(isset($params['rewrite_duplicate']) && $params['rewrite_duplicate'] == true){
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
                        $s->created_time = time();
                        
                        $s->save();
                    }
                    
                    
                    if($module_name != ""){
                        
                        $this->updateSimToModule($sim['id'], $module_name);
                        
                    }
                    
                    
                }
            }
            
        }
        
        return $existed;
    }
    
    
    public function updateSimData($data, $params){
        
        $module_name = isset($params['module_name']) ? $params['module_name'] : '';
        
        $existed = [];
        
      
        $l = isset($data['data']) ? $data['data'] : $data;
        $state = isset($data['state']) ? $data['state'] : true;
        
        $profit = isset($params['profit']) ? $params['profit'] : 0;
        
        $collection = Yii::$app->mongodb->getCollection('simonline');
        
        $price_public = isset($params['price_public']) ? $params['price_public'] : false;
        if(!empty($l)){
                                
            
            foreach ($l as $sim){
                
                
                if(isset($sim['module_name'])){
                    
                    unset($sim['module_name']);
                }
                if(isset($sim['price2']) && $sim['price2'] == 0){
                    unset($sim['price2']);
                    
                    if(isset($sim['price1'])){
                        unset($sim['price1']);
                    }
                    if(isset($sim['price'])){
                        unset($sim['price']);
                    }
                    
                }elseif(isset($sim['price2'])){
                    
                    if(!$price_public && $profit > 0){
                        $sim['price'] = $sim['price2'];
                        
                        $sim['price2'] *= (1 + ($profit/100));
                    }else {
                        // $sim['price'] = $sim['price2'] * 0.9;
                    }
                    
                }
                $sim['updated_at'] = time();
                
                if(isset($params['partner_id'])){
                    $sim['partner_id'] = $params['partner_id'];
                }
                
                if(isset($params['partner_label'])){
                    $sim['partner_label'] = $params['partner_label'];
                }
                

                $sim['id'] = (string) $sim['id'];
                $query = new \yii\mongodb\Query();
                $query->from('simonline')->where(['_id' => $sim['id']]);
                $s = $query->one();
                
                $aa = [
                    'price',
                    'price1',
                    'price2',
                    'partner_id',
                    'updated_at',
                    'score',
                    'network_id',
                    'category_id',
                    'category2_id',
                    'category3_id',
                    'status',
                    'type_id',
                    'fixed_price',
                    
                ];
                 
//                 $aa =[
//                     'price', 'price1','price2', 'partner_id','updated_at', 'score','network_id', 'category_id','category2_id','category3_id', 'status','type_id'
//                 ];
                
                foreach ($aa as $field){
                    $sim[$field] = isset($sim[$field]) ? (int) $sim[$field] : 0;
                }
                
                if(!empty($s)){
                    
                    $arrUpdate = $sim;
                    
                    foreach ($aa as $field){
                        if(!(isset($arrUpdate[$field]) && $arrUpdate[$field]>0)){
                            if(isset($arrUpdate[$field]) && isset($s[$field])) unset($arrUpdate[$field]);
                        }
                    }
                     
                    if(isset($params['rewrite_duplicate']) && $params['rewrite_duplicate'] == true){                     
                        $collection->update(['_id' => $sim['id']],$arrUpdate);
                        
                        Yii::$app->db->createCommand()->update('simonline_module', [
                            'price' => $sim['price'],
                            'price2'=> $sim['price2'],
                        ], ['id' => $sim['id']])->execute();
                        
                    }
                    
                    
                }else{
                    $s = new SimonlineMongodbModel();
                    $s->_id = $sim['id'];
                    
                    
                    foreach ($sim as $key => $value){
                        //if(!in_array($key, ['id'])){
                            $s->$key = $value;
                        //}
                    }
                    
                    $s->created_time = time();
                    
                    if(!isset($sim['status'])){
                        $s->status = -1;
                    }
                    
                    $s->save();
                }
                
                if($module_name != ""){
                    
                    $this->updateSimToModule($sim['id'], $module_name);
                    
                }
                
                $existed[] = "${sim['display']} - ".number_format( $sim['price2'])." / ".number_format( $sim['price']);
                
            }
        }
            
         
        
        return $existed;
    }
    
    public function validateDaiCat($so)
    {
        $cat = [0,3,5 ,13, 16, 18,23,24,25, 28,30,31,38,40, 46,47, 66, 78];
        
        $d = $this->checkDaiCat($so);
        
        if(isset($cat[$d])) return true;
        
        return false;
    }
    
    public function simDaiCat($so){
        $data = [];
        $filename = Yii::getAlias('@runtime/simonline/que_dat_cat.json');
        if(file_exists($filename)){
            $data = json_decode(file_get_contents($filename), 1);
        }
          
        
        $d = $this->checkDaiCat($so);
        
        if(isset($data[$d])) return $data[$d];
        
    }
    
    public function checkDaiCat($so)
    {
        $so = preg_replace('/\D/','',  $so);
        
        $num = substr($so, -4);
        $du = ($s = $num / 80) - (int)$s;
        
        return (int)($du * 80);
        
    }
    
    
    public function getSimTypeId($id = -1)
    {
        $a = [
            
            ['id' => 1, 'name' => 'TT'], 
            ['id' => 3, 'name' => 'TS'], 
        ];
        
        if($id != -1){
        foreach($a as $b){ 
            if($b['id'] == $id) return $b;
        }
        return [];
        }
        return $a;
    }
    
    public function findNetworkById($network_id)
    {
        $networks = [
            1 => 'Viettel',
            2 => 'Vinaphone',
            3 => 'Mobifone',
            4 => 'Vietnamobile',
            5 => 'Gmobile',
            6 => 'iTelecom'
        ];
        if(isset($networks[$network_id])) return $networks[$network_id];
        return 'unknown';
    }
    
    
    public function checkGiaDoiThu($sosim, $params = [])
    {
        
        require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
        
        $sites= isset($params['sites']) ? (array)$params['sites'] : [];
        
        $ls =  [
            [
                'name'  =>  'simthanglong.vn',
                'url'   =>  'https://simthanglong.vn/{SO_SIM}',                
            ],
            [
                'name'  =>  'simthanhcong.net',
                'url'   =>  'https://simthanhcong.net/so-{SO_SIM}.html',
            ],
            [
                'name'  =>  'chosim24h.com',
                'url'   =>  'https://chosim24h.com/{SO_SIM}',
            ],
            [
                'name'  =>  'khosim.com',
                'url'   =>  'https://khosim.com/{SO_SIM}',
            ],
            [
                'name'  =>  'sodepami.vn',
                'url'   =>  'https://sodepami.vn/{SO_SIM}',
            ],
//             [
//                 'name'  =>  'simphongthuy.com',
//                 'url'   =>  'https://www.simphongthuy.com/{SO_SIM}.html',
//             ],
            [
                'name'  =>  'simdayroi.vn',
                'url'   =>  'https://simdayroi.vn/{SO_SIM}.html',
            ],
            
            
            [
                'name'  =>  'sim3mc.com',
                'url'   =>  'http://sim3mc.com/{SO_SIM}.html',                
            ],
            [
                'name'  =>  'simmobicantho.com',
                'url'   =>  'http://simmobicantho.com/{SO_SIM}.html',
            ],
            [
                'name'  =>  'simvietteldep.com',
                'url'   =>  'http://simvietteldep.com/dai-ly-viettel/{SO_SIM}.html',
            ],
            [
                'name'  =>  'chonsodep.vn',
                'url'   =>  'http://chonsodep.vn/{SO_SIM}.html',
            ],
            [
                'name'  =>  'simvidan.vn',
                'url'   =>  'https://simvidan.vn/{SO_SIM}.html',
            ],
            [
                'name'  =>  'khosimbinhduong.vn',
                'url'   =>  'https://khosimbinhduong.vn/{SO_SIM}.html',
            ],
            [
                'name'  =>  'sim3mien.com',
                'url'   =>  'https://sim3mien.com/{SO_SIM}',
            ],
            
            
            [
                'name'  =>  'simcuatuis.com',
                'url'   =>  'http://simcuatuis.com/mua-sim-dep-{SO_SIM}.html',
            ],
            [
                'name'  =>  'simthaibinhduong.com',
                'url'   =>  'http://simthaibinhduong.com/{SO_SIM}.html',
            ],
            [
                'name'  =>  'sim47.vn',
                'url'   =>  'https://sim47.vn/{SO_SIM}.html',
            ],
            [
                'name'  =>  'simdepgiaxau.com',
                'url'   =>  'http://simdepgiaxau.com/{SO_SIM}.html',
                'note'  =>  'giong sim47'
            ],
//             [
//                 'name'  =>  'apviet.com',
//                 'url'   =>  'http://apviet.com/{SO_SIM}-sim-mobifone.ap',
//             ],
            [
                'name'  =>  'bansodep.com',
                'url'   =>  'http://bansodep.com/{SO_SIM}/',
            ],
            [
                'name'  =>  'simviettel.com',
                'url'   =>  'https://simviettel.com/sim-so-dep-{SO_SIM}.html',
            ],
            
//             [
//                 'name'  =>  'simbongsen.com',
//                 'url'   =>  'https://simbongsen.com/{SO_SIM}.html',
//             ],
            
            
            
            
            
            
            
        ];
        
        $sosim = makePhoneNumber(preg_replace('/\D/', '', $sosim));
        
        $min = $max = 0;
        $max_partner = $min_partner = [];
        $data = [];
        
        foreach ($ls as $s){
            
            if(!empty($sites)){
                if(!in_array($s['name'], $sites)) continue;
            }
            
            $price = 0;
            
            $url = preg_replace('/\{SO_SIM\}/', $sosim, $s['url']);
            
            $s['url'] = $url;
            
            
            
            switch ($s['name']){
                
                case 'simthanglong.vn':
                    
                    $html = @file_get_html($url);
                    if(!empty($html)){
                        $content = $html->find('#giaban',0);
                      
                        if(!empty($content)){
                            $price = $content->value;
                        }
                    }
                    break;
               case 'simthanhcong.net':
                    
                    $html = @file_get_html($url); 
                     
                    if(!empty($html)){
                        $content = $html->find('.info-sim .price',0);
                      
                        if(!empty($content)){
                            $price = $content->plaintext;
                        }
                    }
                    break;  
               case 'chosim24h.com':
                    
                    $html = @file_get_html($url);
                    if(!empty($html)){
                        $content = $html->find('.box-xemsim .thongtin .info .txt',1);
                      
                        if(!empty($content)){
                            $price = $content->plaintext;
                        }
                    }
                    break;     
                case 'khosim.com':
                    
                    $html = @file_get_html($url);
                    if(!empty($html)){
                        $content = $html->find('.sim-detail .sim-detail-price',0);
                      
                        if(!empty($content)){
                            $price = $content->plaintext;
                        }
                    }
                    break;        
                case 'sodepami.vn':
                    
                    $html = @file_get_html($url);
                    if(!empty($html)){
                        $content = $html->find('#dathang input[name=gia]',0);
                        
                        if(!empty($content)){
                            $price = $content->value;
                        }
                    }
                    break;
                 case 'simphongthuy.com':
                    
                    $html = @file_get_html($url);
                      
                    
                    if(!empty($html)){
                        $content = $html->find('.x-box-thongtinsim .gia',0);
                         
                        
                        if(!empty($content)){
                            $price = $content->plaintext;
                        }
                    }
                    break;   
                    
                  case 'simdayroi.vn':
                    
                    $html = @file_get_html($url);
                      
                    
                    if(!empty($html)){
                        $content = $html->find('#buy .panel-body .price1',0);
                         
                        
                        if(!empty($content)){
                            $price = $content->plaintext;
                        }
                    }
                    break;    
                    
                  case 'sim3mc.com':
                  case 'simmobicantho.com':
                  case 'simthaibinhduong.com':
                  case 'sim47.vn':
                  case 'simdepgiaxau.com':
                    
                    $html = @file_get_html($url);
                       
                    
                    if(!empty($html)){
                        $content = $html->find('#giaban',0);
                         
                        
                        if(!empty($content)){
                             
                            
                            $price = $content->value;
                        }
                    }else{
                        
                        $html = str_get_html(get_web_page($url)['content']);
                        if(!empty($html)){
                            $content = $html->find('#giaban',0);
                            
                            
                            if(!empty($content)){
                                
                                
                                $price = $content->value;
                            }
                        }
                        
                    }
                    break;   
                 case 'chonsodep.vn':
                    
                    $html = @file_get_html($url);
                       
                    
                    if(!empty($html)){
                        $content = $html->find('#ordered tr',1);
                         
                        
                        if(!empty($content)){
                             
                            
                            $price = $content->find('td',2)->plaintext;
                        }
                    }else{
                        
                        $html = str_get_html(get_web_page($url)['content']);
                        if(!empty($html)){
                            $content = $html->find('#ordered tr',1); 
                            
                            
                            if(!empty($content)){
                                
                                
                                $price = $content->find('td',2)->plaintext;
                            }
                        }
                        
                    }
                    break;  
                  
                  
                  case 'simvidan.vn':
                    
                    $html = @file_get_html($url);
                       
                    
                    if(!empty($html)){
                        $content = $html->find('#ordered tr',1);
                         
                        
                        if(!empty($content)){
                             
                            
                            $price = $content->find('td',0)->find('input',1)->value;
                            if(!empty($price)){
                                
                            }
                        }
                    }else{
                        
                        $html = str_get_html(get_web_page($url)['content']);
                        if(!empty($html)){
                            $content = $html->find('#ordered tr',1); 
                            if(!empty($content)){
                                
                                
                                $price = $content->find('td',0)->find('input',1)->value;
                                if(!empty($price)){
                                    
                                }
                            }
                        }
                        
                    }
                    break;  
                    
                   case 'khosimbinhduong.vn':
                    
                       $html = @file_get_html($url);
                       
                       
                       if(!empty($html)){
                           $content = $html->find('#giaban',0);
                           
                           
                           if(!empty($content)){
                               
                               
                               $price = $content->value;
                           }
                       }else{
                           
                           $html = str_get_html(get_web_page($url)['content']);
                           if(!empty($html)){
                               $content = $html->find('#giaban',0);
                               
                               
                               if(!empty($content)){
                                   
                                   
                                   $price = $content->value;
                               }
                           }
                           
                       }
                    break; 
                    
                case 'sim3mien.com':
                    
                       $html = @file_get_html($url);
                       
                       
                       if(!empty($html)){
                           $content = $html->find('.box-xemsim .tab-sim tr',1);
                           
                           
                           if(!empty($content)){
                               $p = $content->find('td',1);
                               if(!empty($p))
                               $price = $p->plaintext;
                           }
                       } 
                    break; 
                    
                   case 'simcuatuis.com':
                    
                       $html = @file_get_html($url);
                       
                       
                       if(!empty($html)){
                           $content = $html->find('.sim_detail_info .sim_detail_infol .sim_gia',1);
                           
                           
                           if(!empty($content)){
                               $p = $content->find('.price',0);
                               if(!empty($p))
                               $price = $p->plaintext;
                           }
                       } 
                    break;   
                    
                 case 'bansodep.com':
                    
                     $html = str_get_html(get_web_page($url)['content']);
                       
                       if(!empty($html)){
                           $content = $html->find('.bangsimdep tr',1);
                            
                           
                           if(!empty($content)){
                               $p = $content->find('td',1);
                               if(!empty($p))
                                   $price = $p->plaintext;
                           }
                       } 
                    break;     
                    
                   
                 case 'simviettel.com':
                    
                     $html = str_get_html(get_web_page($url)['content']);
                       
                       if(!empty($html)){
                           $content = $html->find('.box-xemsim tr',1);
                            
                           
                           if(!empty($content)){
                               $p = $content->find('td',1);
                               if(!empty($p))
                                   $price = $p->plaintext;
                           }
                       } 
                    break;     
                case 'simbongsen.com':
                    
                     $html = str_get_html(get_web_page($url)['content']);
                     
                     view($url);
                     
                       if(!empty($html)){
                           $content = $html->find('#ordered table tr',1);
                            
                           view($content->plaintext,1,1);
                           
                           if(!empty($content)){
                               $p = $content->find('td',2);
                               if(!empty($p))
                                   $price = $p->plaintext;
                           }
                       } 
                    break;       
                    
                default:
                    
                    
                    break;
            }
            
            $s['price'] = ($price = preg_replace('/\D/', '', $price));
            
            if($price > $max) {
                $max = $price;
                $s['price'] = $price;
                $max_partner = $s;
            }
            
            if($min == 0 || ($min > $price && $price > 0)){
                $min = $price;
                $s['price'] = $price;
                $min_partner = $s;
            }
             
            
            $data[] = $s;
        }
        

        return [
            
            'min' => $min,
            'max' => $max,
            'partner_min' => $min_partner,
            'partner_max' => $max_partner,
            'data' => $data
            
        ];
        
    }
    
    public function getSalePrice($price, $params = [])
    {
        if($price == 0) return 0;
        // Price : giá thu
        $profit = 25;
        
        $profit_price = $price * $profit / 100;
        
        $profit_price = max($profit_price, 200000);
        
        $max_price = 100000000;
        
        
        
        if($price < 300000){
            $max_price = 200000;
        }elseif($price < 600000){
            $max_price = 230000;
            
            $profit_price = max($profit_price, $max_price);
            
        
        
        }elseif($price < 1000000){
            $max_price = 240000;
            
            $profit_price = max($profit_price, $max_price);
            
        }elseif($price < 1200000){
            $max_price = 260000;
            
            $profit_price = max($profit_price, $max_price);
            
        }elseif($price < 1600000){
            $max_price = 290000;
        }elseif($price < 2000000){
            $max_price = 330000;
        }elseif($price < 4000000){
            $max_price = 465000;
        }elseif($price < 5000000){
            $max_price = 585000;
        }elseif($price < 8000000){            
            $max_price = 800000;
        }elseif($price < 10000000){
            $max_price = 850000;
        }elseif($price < 1500000){
            $max_price = 900000;
        }elseif($price < 20000000){
            $max_price = 1000000;
        }elseif($price < 30000000){
            $max_price = 1500000;
        }elseif($price < 50000000){
            $max_price = 3000000;
        }elseif($price < 80000000){
            $max_price = 3500000;
        }elseif($price < 100000000){
            $max_price = 4000000;
        }elseif($price < 150000000){
            $max_price = 5000000;
        }elseif($price < 800000000){
            $max_price = 10000000;
        }elseif($price < 1500000000){
            $max_price = 20000000;
        }else{
            $max_price = 50000000;
        }
        
        
        $profit_price = min($profit_price, $max_price);
        
        $price = $price + $profit_price;
        
        return max($price, 380000);
        
    }
    
    
    public function getSalePrice2($price)
    {
        if($price == 0) return 0;
        
        // Price : giá thu
        $profit = 30;
        
        $profit_price = $price * $profit / 100;
        
        $profit_price = max($profit_price, 200000);
        
        $max_price = $profit_price;
        
        
        
        if($price < 300000){
            $max_price = 200000;
        }elseif($price < 600000){
            $max_price = 250000;
        }elseif($price < 2000000){
            $max_price = 400000;
        }elseif($price < 4000000){
            $max_price = 650000;
        }elseif($price < 5000000){
            $max_price = 700000;
        }elseif($price < 8000000){
            $max_price = 1200000;
        }elseif($price < 10000000){
            $max_price = 1400000;
        }elseif($price < 1500000){
            $max_price = 1600000;
        }elseif($price < 20000000){
            $max_price = 2000000;
        }elseif($price < 30000000){
            $max_price = 3000000;
        }elseif($price < 50000000){
            $max_price = 5000000;
        }elseif($price < 150000000){
            $max_price = 8000000;
        }elseif($price < 200000000){
            $max_price = 10000000;
        }elseif($price < 2000000000){
            $max_price = 15000000;
        }else{
            $max_price = 50000000;
        }
        
        $max_price *= 1.2;
        
        $profit_price = min($profit_price, $max_price);
        
        $price = $price + $profit_price;
        
        return $price;
        
    }
    
    public function getAgentPrice($price,$group_id = -1, $level = -1, $sim = [])
    {
        if($price == 0) return 0;
        
        switch ($group_id) {
            case 3: // CTV mới
                if($price < 300000){
                    $max_price = 30000;
                }elseif($price < 600000){
                    $max_price = 50000;
                }elseif($price < 2000000){
                    $max_price = 80000;
                }elseif($price < 3300000){
                    $max_price = 130000;                    
                }elseif($price < 4000000){
                    $max_price = 150000;
                }elseif($price < 5500000){
                    $max_price = 170000;
                }elseif($price < 7000000){
                    $max_price = 200000;
                    
                //////////////////////////////////////
                }elseif($price < 8000000){
                    $max_price = 220000;
                }elseif($price < 10000000){
                    $max_price = 220000;
                }elseif($price < 15000000){
                    $max_price = 250000;
                }elseif($price < 20000000){
                    $max_price = 250000;
                }elseif($price < 30000000){
                    $max_price = 300000;
                }elseif($price < 50000000){
                    $max_price = 500000;
                }elseif($price < 100000000){
                    $max_price = 1000000;
                }elseif($price < 200000000){
                    $max_price = 2000000;
                }elseif($price < 2000000000){
                    $max_price = 5000000;
                }else{
                    $max_price = 10000000;
                }
                 
                
                $price += $max_price;
                
            break;
            
            case 4: // CTV Boss
                
                $break_id = 1507;
                
                if(isset($sim['partner_id']) && $sim['partner_id'] == $break_id)
                {
                    return $price;
                }
                
                
                if($price < 300000){
                    $max_price = 30000;
                }elseif($price < 600000){
                    $max_price = 50000;
                }elseif($price < 2000000){
                    $max_price = 80000;
                }elseif($price < 3300000){
                    $max_price = 130000;
                }elseif($price < 4000000){
                    $max_price = 150000;
                }elseif($price < 5500000){
                    $max_price = 170000;
                }elseif($price < 7000000){
                    $max_price = 200000;
                    
                    //////////////////////////////////////
                }elseif($price < 8000000){
                    $max_price = 220000;
                }elseif($price < 10000000){
                    $max_price = 220000;
                }elseif($price < 15000000){
                    $max_price = 250000;
                }elseif($price < 20000000){
                    $max_price = 250000;
                }elseif($price < 30000000){
                    $max_price = 300000;
                }elseif($price < 50000000){
                    $max_price = 500000;
                }elseif($price < 100000000){
                    $max_price = 1000000;
                }elseif($price < 200000000){
                    $max_price = 2000000;
                }elseif($price < 2000000000){
                    $max_price = 5000000;
                }else{
                    $max_price = 10000000;
                }
                
                
                $price += $max_price;
                
                
                break;
                
            case 5: // CTV Boss C2
                
                
                
                if($price < 300000){
                    $max_price = 30000;
                }elseif($price < 600000){
                    $max_price = 50000;
                }elseif($price < 2000000){
                    $max_price = 60000;
                }elseif($price < 3300000){
                    $max_price = 100000;
                }elseif($price < 4000000){
                    $max_price = 100000;
                }elseif($price < 5500000){
                    $max_price = 120000;
                }elseif($price < 7000000){
                    $max_price = 130000;
                    
                    //////////////////////////////////////
                }elseif($price < 8000000){
                    $max_price = 150000;
                }elseif($price < 10000000){
                    $max_price = 150000;
                }elseif($price < 15000000){
                    $max_price = 180000;
                }elseif($price < 20000000){
                    $max_price = 220000;
                }elseif($price < 30000000){
                    $max_price = 280000;
                }elseif($price < 50000000){
                    $max_price = 480000;
                }elseif($price < 100000000){
                    $max_price = 800000;
                }elseif($price < 200000000){
                    $max_price = 1000000;
                }elseif($price < 2000000000){
                    $max_price = 5000000;
                }else{
                    $max_price = 10000000;
                }
                
                
                $price += $max_price;
                 
                
                break;
            
            default:
                $price = $this->getSalePrice2($price);
            break;
        }
        
        return $price;
    }
    
    
    public function validateIstm($sim_info)
    {
        $partner_id = [1507];
        
        if(in_array($sim_info['partner_id'], $partner_id) && $sim_info['price'] > 6000000){
            return true;
        }
        return false;
    }
    
    
}