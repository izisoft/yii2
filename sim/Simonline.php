<?php 

namespace izi\sim;
use Yii; 

class Simonline extends \yii\base\Component
{

    
    private $_collection;
    
    public function init()
    {
        $this->_collection = Yii::$app->mongodb->getCollection(SimonlineMongodbModel::collectionName());
    }
    
    public function getCollection()
    {
        if($this->_collection == null){
            $this->_collection = Yii::$app->mongodb->getCollection(SimonlineMongodbModel::collectionName());
        }
        return $this->_collection;
    }
    
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class' =>  'izi\\sim\\SimonlineMongodbModel',                
            ]);
        }
        
        return $this->_model;
    }
    
    
    private $_faker;
    
    public function getFaker(){
        if($this->_faker == null){
            $this->_faker = Yii::createObject([
                'class' =>  'izi\\sim\\Faker',
            ]);
        }
        
        return $this->_faker;
    }
    
    
    private $_es;
    
    public function getEs(){
        if($this->_es == null){
            $this->_es = Yii::createObject([
                'class' =>  'izi\\sim\\es\\Sim3',
            ]);
        }
        
        return $this->_es;
    }
    
    private $_sale;
    
    public function getSale(){
        if($this->_sale == null){
            $this->_sale = Yii::createObject([
                'class' =>  'izi\\sim\\Sale',
                'sim'   =>  $this
                
            ]);
        }
        
        return $this->_sale;
    }
    
    private $_quotation;
    
    public function getQuotation(){
        if($this->_quotation == null){
            $this->_quotation = Yii::createObject([
                'class' =>  'izi\\sim\\Quotation',                
            ]);
        }
        
        return $this->_quotation;
    }
    
    private $_import;
    
    public function getImport(){
        if($this->_import == null){
            $this->_import = Yii::createObject([
                'class' =>  'izi\\sim\\import\Import',
                //'sim'   =>  $this,
            ]);
        }
        
        return $this->_import;
    }    
    
    private $_list;
    
    public function getList(){
        if($this->_list == null){
            $this->_list = Yii::createObject([
                'class' =>  'izi\\sim\\Listsim',
                //'sim'   =>  $this,
            ]);
        }
        
        return $this->_list;
    }
    
    private $_package;
    
    public function getPackage(){
        if($this->_package == null){
            $this->_package = Yii::createObject([
                'class' =>  'izi\\sim\\Package',
                //'sim'   =>  $this,
            ]);
        }
        
        return $this->_package;
    }
    
    
    private $_phongthuy;
    
    public function getPhongthuy(){ 
        if($this->_phongthuy == null){
            $this->_phongthuy = Yii::createObject([
                'class' =>  'izi\\sim\\PhongThuy',
            ]);
        }
        
        return $this->_phongthuy;
    }   
        
      
    public function getSimId($string)
    {
        $string = str_replace(['O', 'o'], '0', $string);
        return (string) ltrim(preg_replace('/\D/', '', $string),'0');
    }
    
    
    public function adjustFieldValue($params, $conditions)
    {
        $p = $this->buildConditions($conditions);
        if(count($p) > 1){
            
            $this->getEs()->adjustFieldValue($params, $conditions);
            
            $method = $params['method'];
            
            switch($params['unit'])
            {
                case '%':
                    $method = 'mul';
                    
                    if($params['value_of_change'] > 0){
                        $params['value_of_change'] = (1 + $params['value_of_change']/100);
                    }else{
                        $params['value_of_change'] = (1 - $params['value_of_change']/100);
                    }
                    
                    break;
            }
            
            switch($method)
            {
                case 'inc': case 'mul': case 'min':
                    return $this->getCollection()->update($p, ["\$$method" => [$params['field'] => $params['value_of_change']]] );
                    break;
                default:
                    
                    return $this->getCollection()->update($p, [$params['field'] => $params['value_of_change']] );
                    
                    break;
            }
            
            
            
            
        }
        
    }
    
    
    public function adjustPrice()
    {
        
    }
    
    
  
    
    
    public function removeItems($params){
        $p = $this->buildConditions($params);
        if(count($p) > 1){
            $this->getCollection()->remove($p);
            $this->getEs()->deleteMultipleSim($params);
        }
        
    }
    
    public function removeItem($simId, $partner_id){
        $this->getCollection()->remove(['id'=>$simId, 'partner_id' => $partner_id]);
        
        $this->getEs()->deleteSim($simId, $partner_id);
        
//         SimonlineModuleModel::deleteAll(['id'=>$simId, 'partner_id' => $partner_id]);
    }
    
    public function findSim($sosim, $partner_id = 0)
    {
        $sosim = $this->getSimId($sosim);
        
        $query = new \yii\mongodb\Query();
        $query->from(SimonlineMongodbModel::collectionName())->where(['id' => $sosim]);
        
        if($partner_id > 0){
            $query->andWhere(['partner_id' => (int)$partner_id]);
            return $query->one();
        }
        
        $query->orderBy(['price2' => SORT_ASC]);
        
        return $query->one();
    }
    
    
    public function getSimDetail($simId, $partner_id = 0)
    {
        return $this->getEs()->getSimDetail($simId, $partner_id);
        
        $simId = $this->getSimId($simId);
        $query = new \yii\mongodb\Query();
        $query->from(SimonlineMongodbModel::collectionName())->where(['id' => $simId]);
                
        if($partner_id > 0){
            $query->andWhere(['partner_id' => $partner_id]);
            
        }
        
        $query->orderBy(['is_invisible' => SORT_ASC, 'price' => SORT_ASC]);
        return $query->all();
    }
    
    
    public function getItem($sosim, $partner_id = 0, $params = [])
    {
        return $this->getEs()->getItem($sosim, $partner_id, $params);
        
        $sosim =  $this->getSimId($sosim);
                                 
        $query = new \yii\mongodb\Query();
        $query->from(SimonlineMongodbModel::collectionName())->where(['id' => $sosim]);
        
        if(is_numeric($partner_id) && $partner_id > 0){
            $query->andWhere(['partner_id' => (int) $partner_id]);
            return $query->one();
        }
        
        $query->orderBy(['is_invisible' => SORT_ASC, 'price' => SORT_ASC]);
        
        $item = $query->one();

        return $item;
    }
    
    public function getRegex($params)
    {
        if(isset($params['regex']) && $params['regex'] != ""){
            switch ($regex = $params['regex']){
              
                case '_DAI_CAT_': break;
                
                case '[^a]bcdefgh$':
                case '[^a]bcdefg$':
                case '[^a]bcdef$':
                case '[^a]bcde$':
                case '[^a]bcd$':
                case '[^d]cba$':
                    
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
    
    
    
    
    public function getItems($params)
    
    {
        
        

        //if(!(isset($params['es']) && $params['es'] === false)){
            return $this->getEs()->getItems($params);
        //}
         
         
         
        $limit = (int) (isset($params['limit']) ? $params['limit'] : 30);
        
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        
        $p = isset($params['p']) && $params['p'] > 1 ? $params['p'] : 1;
        
        $offset = ($p - 1) * $limit;
        
        $count = isset($params['count']) && $params['count'] === true ? true : false;
        
         
        
        $min_price = isset($params['min_price']) ? $params['min_price'] : 0;
        
        $max_price = isset($params['max_price']) ? $params['max_price'] : 0;
        
        $query = new \yii\mongodb\Query();
        $query->from(SimonlineMongodbModel::collectionName());
        
        //$params['sosim'] = '0949*0';
        
        // Conditions
        
        $conditions = ['and'];
        
        $validate_min_price = false;
        
        
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
       
        
        if(isset($params['min_score']) && $params['min_score']>0){
            $sim_filter['min_score'] = $params['min_score'];
        }
        
        if(isset($params['max_score']) && $params['max_score']>0){
            $sim_filter['max_score'] = $params['max_score'];
        }
        
        if(isset($params['min_key']) && $params['min_key']>0){
            $sim_filter['min_key'] = $params['min_key'];
        }
        
        if(isset($params['max_key']) && $params['max_key']>0){
            $sim_filter['max_key'] = $params['max_key'];
        }
        
        if(isset($params['is_sold']) && $params['is_sold']> - 1){
            $sim_filter['is_sold'] = $params['is_sold'];
        }
        
        if(isset($params['is_invisible']) && $params['is_invisible']> - 1){
            $sim_filter['is_invisible'] = $params['is_invisible'];
        }
        
        // simfilter
        if(!empty($sim_filter)){
            
            foreach ([
                'network_id',
                'category_id',
                'category2_id',
                'category3_id',
                'type_id',
                'partner_id'
                
            ] as $field){
                if(isset($sim_filter[$field]) && $sim_filter[$field]>0){
                    
                    $conditions[] = [$field => $sim_filter[$field]];
                }
            }
            
            if(isset($sim_filter['network_id']) && $sim_filter['network_id']>0){
                $query->andWhere(['network_id' => (int)$sim_filter['network_id']]);
            }
            
            if(isset($sim_filter['category_id']) && $sim_filter['category_id']>0){
                $query->andWhere(['or',[
                    'category_id' => (int)$sim_filter['category_id']
                    ],
                    [
                        'category3_id' => (int)$sim_filter['category_id']
                    ]
                ]);
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
            
            if(isset($sim_filter['is_sold']) && $sim_filter['is_sold']>-1){
                $query->andWhere(['is_sold' => (int)$sim_filter['is_sold']]);
            }
            
            if(isset($sim_filter['is_invisible']) && $sim_filter['is_invisible']>-1){
                $query->andWhere(['is_invisible' => (int)$sim_filter['is_invisible']]);
            }
            
            if(isset($sim_filter['min_score']) && $sim_filter['min_score']>0){
                $query->andWhere(['>', 'score' , (int)$sim_filter['min_score'] - 1]);
                $conditions[] = ['>', 'score' , (int)$sim_filter['min_score'] - 1];
            }
            if(isset($sim_filter['max_score']) && $sim_filter['max_score']>0){
                $query->andWhere(['<', 'score' , (int)$sim_filter['max_score'] + 1]);
                $conditions[] = ['<', 'score' , (int)$sim_filter['max_score'] + 1];
            }
            
            if(isset($sim_filter['min_key']) && $sim_filter['min_key']>0){
                $query->andWhere(['>', 'number_of_key' , (int)$sim_filter['min_key'] - 1]);
                $conditions[] = ['>', 'number_of_key' , (int)$sim_filter['min_key'] - 1];
            }
            if(isset($sim_filter['max_key']) && $sim_filter['max_key']>0){
                $query->andWhere(['<', 'number_of_key' , (int)$sim_filter['max_key'] + 1]);
                $conditions[] = ['<', 'number_of_key' , (int)$sim_filter['max_key'] + 1];
            }
            
            
            if(isset($sim_filter['regex']) && ($regex = $sim_filter['regex']) != ""){
                
                $val = explode('|', $regex);
                 
                
                switch ($val[0]){
                    
                    case 'price':
                        $val1 = max($min_price, isset($val[1]) && is_numeric($val[1]) ? $val[1] : 0);
                        $val2 = max($max_price, isset($val[2]) && is_numeric($val[2]) ? $val[2] : 0);
                        
                        if($val2 > $val1-1){
                            $query->andWhere(['between', 'price2', (float)$val1, (float)$val2]);
                            $conditions[] = ['between', 'price2', (float)$val1, (float)$val2];
                            $validate_min_price = true;
                        }
                        break;
                        
                    default:
                        $query->andWhere(['REGEX' ,'id',   '/' . $sim_filter['regex'] . '/i']);
                        $conditions[] = ['REGEX' ,'id',   '/' . $sim_filter['regex'] . '/i'];
                        break;
                }
                
                
            }
            
        } 
		
		 
		
        if(isset($params['sosim']) && ($sosim = trim($params['sosim'])) != ""){
            $sosim = str_replace(['o', 'O'], '0', $sosim);
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
                'o' =>'0',
                'O' => '0',
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
                
                $query->andWhere(['id' => $sosim]);
                $conditions[] = ['id' => $sosim];
            }else{
                
                if(preg_match('/\d+/', $sosim, $m) && $m[0] == $sosim){
                    
                    $query->andWhere(['regex' ,'id',  "/$sosim/i"]);
                    $conditions[] = ['regex' ,'id',  "/$sosim/i"];
                    
                }else{
                    $query->andWhere(['regex' ,'id',  "/^$sosim$/i"]);
                    $conditions[] = ['regex' ,'id',  "/^$sosim$/i"];
                }
            }
            
            
        }
        
        
        
        
        if(isset($params['regex']) && $params['regex'] != ""){
            $query->andWhere(['regex' ,'id',  $params['regex']]);
            $conditions[] = ['regex' ,'id',  $params['regex']];
        }
        
		
		 
		
        if(isset($params['explode']) && !empty($params['explode'])){
            
            $a = [0,1,2,3,4,5,6,7,8,9];
			
			$n49 = $n53 =false;
			
			foreach($params['explode'] as $k=>$n){
				if($n==49){
					$n49 = true;
					unset($params['explode'][$k]);
				}
			
				if($n==53){
					$n53 = true;
					unset($params['explode'][$k]);
				}
			}
            $array_diff = array_diff($a, $params['explode']);
             
            $query->andWhere(['REGEX' ,'id',   "/^[".implode('', $array_diff)."]+$/i"]);
            $conditions[] = ['REGEX' ,'id',   "/^[".implode('', $array_diff)."]+$/i"];
            
            if($n49 == true)
			{
			    
			    //$query->andWhere(['not in', 'id',  ['REGEX' ,'id',   "/^.*49.*/"]]);
			}

			if($n53 == true)
			{
				
// 				$query->andWhere(['$not', 'id', new \MongoDB\BSON\Regex('/53/')]);
			}			
			 
        }
        
        
        if(!$validate_min_price){
            if($min_price>0 && $max_price>0){
                $query->andWhere(['between', 'price2', (float)$min_price, (float)$max_price]);
                $conditions[] = ['between', 'price2', (float)$min_price, (float)$max_price];
            }elseif ($min_price > 0){
                $query->andWhere(['between', 'price2', (float)$min_price, 100000000000]);   
                $conditions[] = ['between', 'price2', (float)$min_price, 100000000000];
            }elseif ($max_price > 0){
                $query->andWhere(['between', 'price2',0, (float)$max_price]);
                $conditions[] = ['between', 'price2',0, (float)$max_price];
            }
                      
        }
        
        if(isset($params['select'])){
            $query->addSelect($params['select']);
        }
        
        if(isset($params['sim_filter']['list_id'])){
            $params['list_id'] = $params['sim_filter']['list_id'];
        }
         
        if(isset($params['list_id'])){
            
            if(is_numeric($params['list_id']) && $params['list_id'] > 0){
                $lists = [$params['list_id']];
            }elseif(is_array($params['list_id']) && !empty($params['list_id'])){
                $lists = $params['list_id'];
            }else{
                $lists = [];
            }
            
            if(!empty($lists)){
                
                $l1 = [];
                
                foreach ($lists as $list_id){
                    $list = Yii::$app->sim->list->getItem($list_id);
                    if(isset($list['data']) && !empty($list['data'])){
                        $l1 += $list['data'];
                    }
                }
                 
                
                if(!empty($l1)){
                    $query->andWhere(['id' => $l1]);
                    $conditions[] = ['id' => $l1];
                }
            }
            
             
            
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
                $sort = $this->buildSortParams($params);
                if(!empty($sort)){
                    $query->orderBy($sort);
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
        
        if(isset($params['sort']) && ($sort = $params['sort']) != "")
        {
//             if(is_array($sort) && in_array(100, $sort) || $sort == 100) 
//             {
//                 $collection = Yii::$app->sim->collection;
                
// //                 view($conditions);
                
//                 if(count($conditions) > 1){
                
//                 return $collection->aggregate([                                        
                    
//                     ["\$match"  => Yii::$app->mongodb->getQueryBuilder()->buildCondition($conditions)],
//                     //["\$sort" => ['price' => 1]],
//                     //["\$limit"  => 10],
//                     ["\$sample"  => ['size'=>$limit]],
                    
//                 ]
//                     );
//                 }
//             }
            
            
            $sort = $this->buildSortParams($params);
 
            
            if(!empty($sort)){
                $query->orderBy($sort);
            }
            
        }
          
         
        
         
        
        return $query->limit($limit)->all();
    }
    
    
    private function buildSortParams($params)
    {
        $sort_array = [];
        
        if(isset($params['sort']) && ($sorts = $params['sort']) != "")
        {
            if(!is_array($sorts)){
                $sorts = array($sorts);
            }else{
                
            }
            
            foreach($sorts as $sort){
                
             
            
            
            switch ($sort){
                case 1: // price inc
                    
                    $field = 'price2';
                    
                    if(!Yii::$app->collaborator->isGuest){
                        //$field = 'price';
                    }
                    
                    $sort_array[$field] = SORT_ASC ;
                    break;
                case 2: // price inc
                    
                    $field = 'price2';
                    
                    if(!Yii::$app->collaborator->isGuest){
                        //$field = 'price';
                    }
                    $sort_array[$field] = SORT_DESC;
                    break;
                    
                case 21: // price inc
                    
                    $field = 'price';
                    
                    $sort_array[$field] = SORT_ASC;
                    break;
                case 22: // price inc
                    
                    $field = 'price';
                    $sort_array[$field] = SORT_DESC;
                    break;
                    
                    
                case 3: // price inc
                    $sort_array['network_id'] = SORT_ASC;
                    break;
                case 4: // price inc
                    $sort_array['network_id'] = SORT_DESC;
                    break;
                    
                case 5: // price inc
                    $sort_array['id'] = SORT_ASC;
                    break;
                case 6: // price inc
                    $sort_array['id'] = SORT_DESC;
                    break;
                case 7: // price inc
                    $sort_array['category_id'] = SORT_ASC;
                    break;
                case 8: // price inc
                    $sort_array['category_id'] = SORT_DESC;
                    break;
                    
                case 9: // price inc
                    $sort_array['partner_id'] = SORT_ASC;
                    break;
                case 10: // price inc
                    $sort_array['partner_id'] = SORT_DESC;
                    break;
                    
                case 11: // updated_at inc
                    $sort_array ['updated_at'] = SORT_ASC;
                    break;
                case 12: // updated_at desc
                    
                    $sort_array['updated_at'] = SORT_DESC;
                    break;
                    
                    
                case 13: // updated_at inc
                    $sort_array['score'] = SORT_ASC;
                    break;
                case 14: // updated_at desc
                    
                    $sort_array['score'] = SORT_DESC;
                    break;
                    
                    
                case 15: // updated_at inc
                    $sort_array['number_of_key'] = SORT_ASC;
                    break;
                case 16: // updated_at desc
                    
                    $sort_array['number_of_key'] = SORT_DESC;
                    break;
                case 17: // updated_at inc
                    $sort_array['type_id'] = SORT_ASC;
                    break;
                case 18: // updated_at desc
                    
                    $sort_array['type_id'] = SORT_DESC;
                    break;
                    
                case 31: // updated_at inc
                    $sort_array['exchange_price'] = SORT_ASC;
                    break;
                case 32: // updated_at desc
                    
                    $sort_array['exchange_price'] = SORT_DESC;
                    break;
                    
                    
//                 case 100: // updated_at desc
                    
//                     $sort_array = new \yii\db\Expression('rand()');
                    
//                     break;
                    
            }
            }
        }
        
        return $sort_array;
    }
     
    
    
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
            $query->from(\izi\sim\SimonlineMongodbModel::collectionName())->where(['network_id' => $network_id]);
            
            $data[$network_label] = $query->count(1) ;

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
    
    
    public function getCategoryName($id)
    {
        $identity = $this->getType($id);
        if(!empty($identity)) return $identity['name'];
    }
    
    public function getCategoryRegex($id)
    {
        $identity = $this->getType($id);
        if(!empty($identity)) return $identity['regex'];
    }
    
    
    public function getAllNetwork($params = [])
    {
        $query = SimonlineModel::find()->from('simonline_network');
        
        if(isset($params['status'])){
            $query->andWhere(['status'=>$params['status']]);
        }
        
        return $query->orderBy(['id'=>SORT_ASC])->asArray()->all();
    }
    
    
    private $_network;
    public function getNetwork($id)
    {
        if(isset($this->_network[$id]) && !empty($this->_network[$id])){
            return $this->_network[$id];
        }
        
        $query = SimonlineModel::find()->from('simonline_network');         
        $query->andWhere(['id'=>$id]);                 
        return ($this->_network[$id] = $query->asArray()->one());
    }
    
    
    public function getNetworkName($id)
    {
        $identity = $this->getNetwork($id);
        if(!empty($identity)) return $identity['name'];
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
    
    public function validateSoTien($string, $pattern)
    {
        $last_code = $ic = 0;
        
        preg_match('/(\[\^[a-z]])/i', $pattern, $m1);
        
        if(!empty($m1) && isset($m1[1])){
            $first_char = preg_replace('/\W/i', '', $m1[1]);            
            $ic = ord($first_char);
            $pattern = preg_replace('/(\[\^[a-z]])/i', '', $pattern);
        }
       
        $pattern = preg_replace('/\W/i', '', $pattern);
        
      
        
        $len = strlen($pattern);
        
        $str = substr($string, $len * -1);
        
        
        $s1 = [];
        
        $s2 = '';
        
        $daxet = [];
        
        $fc = 0;
        
        for ($i = 0;  $i< $len; $i++){
            
            
            
            $code = ord($pattern[$i]);
            if($i == 0){
                $fc =$code;
                $fc_number = $str[$i];
            }
            if($last_code > 0){
                
                $cx = abs($code - $last_code);
                
                if($code > $last_code){
                    if(!($str[$i] == $str[$i-1] + $cx)){
                        break;
                    }
                }elseif($code < $last_code){
                    if(!($str[$i] == $str[$i-1] - $cx)){
                        break;
                    }
                }else{
                    if(!($str[$i] == $str[$i-1])){
                        break;
                    }
                }
            }
            
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
            
            $last_code = $code;
            
        }
        
        
        $ic_number = (substr($string,  ($len+1) * -1 , 1));
        
        $ax = abs($ic - $fc); 
        
        if($ic > $fc){
            if($ic_number == $fc_number + $ax ){
                return false;
            }
        }elseif($ic < $fc){
            if($ic_number == $fc_number - $ax ){
                return false;
            }
        }else{
            if($ic_number == $fc_number ){
                return false;
            }
        }
         
        
        return $s2 == $str;
        
    }
    
    
    public function buildPattern($pattern, $string)
    {

        $string = preg_replace('/\D/', '', $string);
        
        
        
        switch ($pattern){
            
            case '_DAI_CAT_':
                return $this->validateDaiCat($string);
                break;
                
            case '__KEP4__':
                $s1 = substr($string, -2); 
                $s2 = substr($string, -4, -2 );
                $s3 = substr($string, -6, -4);
                $s4 = substr($string, -8, -6);
                
                if($s1[0] == $s1[1] 
                    && $s2[0] == $s2[1] 
                    && $s3[0] == $s3[1] 
                    && $s4[0] == $s4[1]){
                    return true;
                }
                
                break;
            case '__NAM_SINH__':
                
                 
                $s1 = substr($string, -2); // 2 số cuối (năm)
                $s2 = substr($string, -4,-2);  // 2 số cuối (tháng)
                $s3 = substr($string, -6,-4);  // ngày
                
                $s4 = "$s2$s1";                 
                
                // Check theo 4 số cuối
                if($s4 > 1930 && $s4<2030){
                    return true;
                }
                
                
                // Check theo dạng ngày - tháng - năm
                $st = true;
                
                $year = 0;
                
                // validate year
                if(!(($year = "19$s1") > 1930 || ($year = "20$s1") < 2030)){
                    $st = false;
                }
                
                if(!($s2 > 0 && $s2 < 13)){
                    $st = false;
                }
                
                if(!($s3 > 0 && $s3 < daysOfMonth($s2, $year) + 1)){
                    $st = false;
                }
                
                if($st === true){
                    return $st;
                }
                
                
                break;
                
            case '[^h]gfedcba$':
            case '[^g]fedcba$':
            case '[^f]edcba$':
            case '[^e]dcba$':
            case '[^d]cba$':
                
            case '[^a]bcd$':
            case '[^a]bcde$':
            case '[^a]bcdef$':
            case '[^a]bcdefg$':
            case '[^a]bcdefgh$':
                
                if($this->validateSoTien($string, $pattern)){ 
                    return true;
                }
                
                break;
            case '[^a]aa$':  
            case '[^a]aaa$':
            case '[^a]aaaa$':
            case '[^a]aaaaa$':
            case '[^a]aaaaaa$':
            case '.*[^a]aaaa[^a].*$':
            case '.*[^a]aaaaa[^a].*$':
            case '.*[^a]aaaaaa[^a].*$':
            case '.*[^a]aaaaaaa[^a].*$':
            case '(19[5-9][0-9])|(20[0-1][0-9])$':
            
                
            
                
            case '(68|86|88|66)$':
            case '(39|79)$':
            case '(38|78)$':
            case '^aaabbb.*$':
            case '^aaaaaaaa[^a].*$':
            case '^aaaaaaa[^a].*$':
            case '^aaaaaa[^a].*$':
            case '^aaaaa[^a].*$':
            case '^aaaa[^a].*$':
            case '.*[^a]aaabbb[^b].*$':
                
                
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
                
                
            case 'aabbcc$':
                $s1 = substr($string, -2);
                $s2 = substr($string, -4,-2);
                $s3 = substr($string, -6,-4);
                
                if($s1 != $s2 || $s2 != $s3){
                    
                    if($s1[0] == $s1[1] && $s2[0] == $s2[1] && $s3[0] == $s3[1]){
                        return true;
                    }
                    
                }
                
                break;
                
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
            case '__TAXI2__':
                
                $s1 = substr($string, -2);
                $s2 = substr($string, -4,-2);
                $s3 = substr($string, -6,-4);
                
                
                if( $s1 == $s2 && $s1 == $s3) return true;
                
                break;
            case '__TAXI3__':
                $s1 = substr($string, -3);
                $s2 = substr($string, -6,-3);
                
                if( $s1 == $s2) return true;
                break;
                
            case '__TAXI4__':
                $s1 = substr($string, -4);
                $s2 = substr($string, -8,-4);
                if( $s1 == $s2) return true;
                break;
                
            case 'aaaaaaa$': case '__SODOC__':
                
                $patterns = [
                    'aaaaaaa$',
                    'abcdefg$',
                    'abcdefgh$',
                    'hgfedcba$',
                    'gfedcba$'
                        
                ];
                
              
                
                foreach ($patterns as $pattern){
                $pattern = preg_replace('/\W/i', '', $pattern);
                
                $len = strlen($pattern);
                
                $str = substr($string, $len * -1);
                
                
                $s1 = [];
                
                $s2 = '';
                
                $daxet = [];
                
                $last_code = 0;
                
                for ($i = 0;  $i< $len; $i++){
                    
                    $code = ord($pattern[$i]);
                    
                    if($i > 0){
                    
                        $cx = abs($code - $last_code);
                        
                        if($code > $last_code){
                            if(!($str[$i] == $str[$i-1] + $cx)){
                                break;
                            }
                        }elseif($code < $last_code){
                            if(!($str[$i] == $str[$i-1] - $cx)){
                                break;
                            }
                        }else{
                            if(!($str[$i] == $str[$i-1])){
                                break;
                            }
                        }
                    }
                    
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
                    
                    $last_code = $code;
                    
                }
                 
                 
                
                if ($s2 == $str) {
                    return true;
                }
                }
                
                break;
            
            default:
                
                $state = $break = false;
                
                 
                
                switch ($pattern) {
                    
                     
                }
                 
                /* if($pattern == 'abcabc$'){
                    
                    $s1 = substr($string, -3);
                    $s2 = substr($string, -6,-3);
                    
                    if($s1[0] != $s1[1] && $s1[1] != $s1[2] && $s1[0] != $s1[2]){
                        return $s1 == $s2;
                    }
                     
                }
                
                if($pattern == 'abcdabcd$'){
                    
                    $s1 = substr($string, -4);
                    $s2 = substr($string, -8,-4); 
                    return $s1 == $s2;
                }
                 
                
                if($pattern == 'abbacc$'){ 
                     
                }
                 */
                
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
                     
                   
                    
                    if ($s2 == $str) {
                        return true;
                    }
                    
                    
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
    
    
    public function getNguHanh()
    {
        return [
            ['id' => 1, 'name' => 'Kim', 'number' => [6,7]],
            ['id' => 2, 'name' => 'Thủy', 'number' => [1]],
            ['id' => 3, 'name' => 'Hỏa', 'number' => [9]],                        
            ['id' => 4, 'name' => 'Thổ', 'number' => [0,2,5,8]],
            ['id' => 5, 'name' => 'Mộc', 'number' => [3,4]],
        ];
    }
    
    
    public function getSiminfo($id)
    {
        $id = str_replace(['O', 'o'], '0', $id);
        
        $sosim = $this->getSimId($id);
        
        $type = $this->findSimType($sosim,1, [1]);
//         view($type);
 
        
        // Dm1 
        $category_id = !empty($type) ? $type['id'] : 0;
        
        $categorylabel = !empty($type) ? $type['name'] : '';
        
        
        // Dm2
        $type = $this->findSimType($sosim, 1 , [2]); 
        
        $category2_id = 0;
        
        if(!empty($type)){
            $category2_id = $type['id'];
        }
        
        $type3 = $this->findSimType($sosim, 1 , [3]);
        
        $category3_id = 0;
        
        if(!empty($type3)){
            $category3_id = $type3['id'];
        }
        
        // Score
        $score = 0; $keys = [];
        for($i = 0; $i<strlen($sosim); $i++){
            $score += $sosim[$i];
            if(!in_array($sosim[$i], $keys)){
                $keys[] = $sosim[$i];
            }
        }
         
        // Tính nut
        $nut = $score % 10;
        if($nut == 0) $nut = 10;
        
        $array['nut'] = $nut;
        
        // Dau so
        $array['dauso'] = (string)substr($sosim, 0, 2);
        
        // Duoi sim
        $array['s2'] = (string)substr($sosim, -2);
        $array['s3'] = (string)substr($sosim, -3);
        $array['s4'] = (string)substr($sosim, -4);
        $array['s5'] = (string)substr($sosim, -5);
        $array['s6'] = (string)substr($sosim, -6);
        
        $array['daicat'] = $this->validateDaiCat($sosim) ? 1 : 0;
        
        return array_merge([
            'id' => $sosim,
            'display'=>makePhoneNumber($id),
            'network_id'=>(int)$this->findNetworkBySim($sosim)['id'],
           
            'category_id'=>(int)$category_id,

            'category2_id'=>(int)$category2_id,
            'category3_id'=>(int)$category3_id,
            'score' => $score,
            'number_of_key'=>count($keys)
        ],$array);
    }
    
    public function phonePattern2()
    {
        
        $space = '[\-\. ]?';
        $space2 = '[\-\.]?';
        $patterns = [
            '(([0Oo]84|\+84|84)[\.]?([5789]|3(?![0Oo]))'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo](?![\dOo]))',           
            '(([0Oo][\.]?)([79]|3(?![0Oo])|5(?![0Oo])|8(?![0Oo]))'.$space.'[0-9Oo]'.$space.'[0-9Oo]'.$space.'[0-9Oo]'.$space.'[0-9Oo]'.$space.'[0-9Oo]'.$space.'[0-9Oo]'.$space.'[0-9Oo]'.$space.'[0-9Oo](?![\dOo]))',
            '(([79]|3(?![0Oo])|5(?![0Oo])|8(?![0Oo]))'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo]'.$space2.'[0-9Oo](?![\dOo]))',
        ];
        
        
        return '/'.implode('|', $patterns).'/i';
    }
    
    public function phonePattern()
    {        
        return $this->phonePattern2();               
    }
    
    
    public function pricePattern()
    {
        return '/([0-9]([0-9Oo]+)?[\.,]?([0-9Oo]+([\.,]?[0-9Oo]+([\.,]?[0-9Oo]+)?)?)?[\s]?(triệu|trieu|tr|k|ty|tỷ|tỉ|ti|t)?[\s]?([0-9Oo]+)?((vnd|vnđ|đ|₫)(?!\w))?)/ui';
         
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
    
    public function extractSimFormString($strings, $params = []){
        return $this->extractSimFromString($strings, $params);
    }
    public function extractSimFromString($strings, $params = [])
    {
		
	   
	    $strings = explode(PHP_EOL, $strings);
 
        $is_fixed_sell_price = isset($params['is_fixed_sell_price']) && $params['is_fixed_sell_price'] == 'on' ? true : false;
        $fixed_sell_price = isset($params['fixed_sell_price']) ? cprice($params['fixed_sell_price'])  : 0;
        $fx_state = false;
        
        $data = []; $state = true;
        
        $kts = 0; $price_option = [];        
        
        if(!empty($strings)){
            foreach ($strings as $string){
                
                if(!(trim($string) != "")) continue;
                
                if($kts++ == 0){
                    $pt = '/(thuê bao|tb).*(giá bán).*(giá thu).*/ui';
                    preg_match($pt, $string, $m2);
                    
                    if(!empty($m2)){
                        $params['price_option'] = [
                            'id', 'price2', 'price'
                        ];
                    }else{
                        $pt = '/(thuê bao|tb|số).*(giá bán|giá khách).*(giá thu|thu).*/ui';
                        preg_match($pt, $string, $m2);
                        
                        //view($m2, $string);
                        
                        if(!empty($m2)){
                            $params['price_option'] = [
                                'id', 'price2', 'price'
                            ];
                        }
                    }
                }
                
                
                if(!$fx_state && !$is_fixed_sell_price){
                   
                    $pt = '/^(thu đồng giá|đồng giá|Đồng giá|sập thu|giá thu|gia thu|Giá thu|Gia thu|Thu|thu|thu nhanh|thu lướt|lướt)\s?([1-9]([0-9Oo]+)?[\.,]?([0-9Oo]+([\.,]?[0-9Oo]+([\.,]?[0-9Oo]+)?)?)?[\s+]?(triệu|trieu|tr|k|ty|tỷ|tỉ|ti|t)?[\s+]?([0-9Oo]+)?)/ui';
                   
                    //$pattern = '/^thu\s(\d+)(k|tr)?/i';
                    preg_match($pt, $string, $m2); 

                    if(!empty($m2)){
                        //$fx_state = true;
                        $params['is_fixed_sell_price'] = 'on';
                        
                        $unit = isset($m2[7]) && $m2[7] != "" ? unMark($m2[7]) : '';                                                
                        $params['fixed_sell_price'] = $this->autoPrice($this->extractNumberForPrice($m2[2]),$unit);
                    }else{
                        $pt = '/^([1-9]([0-9Oo]+)?[\.,]?([0-9Oo]+([\.,]?[0-9Oo]+([\.,]?[0-9Oo]+)?)?)?[\s+]?(triệu|trieu|tr|k|ty|tỷ|tỉ|ti|t)?[\s+]?([0-9Oo]+)?)\s?\/\s?(sim|s)\W?/ui';
                        
                        //$pattern = '/^thu\s(\d+)(k|tr)?/i';
                        preg_match($pt, $string, $m2);
                        
                        //view($m2,$string);
                        
                        if(!empty($m2)){
                            //$fx_state = true;
                            $params['is_fixed_sell_price'] = 'on';
                            
                            $unit = isset($m2[6]) && $m2[6] != "" ? unMark($m2[6]) : '';
                            $params['fixed_sell_price'] = $this->autoPrice($this->extractNumberForPrice($m2[1]),$unit);
                        }
                    }
                }
                
                $d2 = $this->extractSimInfoFromString($string, $params);                

                if($d2['state']){
                    $data = array_merge($data, $d2['data']);
                    
                   // $fx_state = true;
                }
            }
        }
        
        return [
            'state' =>  $state,
            'data'  =>  $data,
        ];
    }
    
    
    public function extractPriceFromString($priceString, $params = [])
    {
        
    }
    
    public function strToPrice($priceString, $params = [])
    {
        $priceString = trim( preg_replace('/[^\d\.,]/', '', $priceString), ' ,.');
        
        $c1 = substr_count($priceString, ',');
        
        if($c1 > 1) {
            $priceString = str_replace(',', '', $priceString);
            $c1 = 0;
        }
        
        $c2 = substr_count($priceString, '.');
        
        if($c1 > 0 && $c2 > 0){
            $priceString = str_replace(',', '', $priceString);
        }elseif($c1 > 0){
            $priceString = str_replace(',', '.', $priceString);
            $c2 = substr_count($priceString, '.');
        }
        
        if($c2 > 1) {
            $priceString = str_replace('.', '', $priceString);
        }elseif ($c2 > 0){
            $c3 = explode('.', $priceString);
            if(trim($c3[count($c3) - 1]) == '000'){
                $priceString = str_replace('.', '', $priceString);
            }
        }
        
        
        return $priceString;
    }
    
    
    public function extractNumberForPrice($priceString, $params = [])
    {
        $unit = $this->getPriceUnit($priceString);
        
        preg_match('/\d+([\s]+)?(triệu|trieu|tr|tỷ|tỉ|ty|ti|t)([\s]+)?\d+/ui', $priceString, $m3);
    
        $prx = false;
        
        if(!empty($m3)){
            $prx = true;
        }
    
        
        $priceString = trim( preg_replace('/([\s]+)?(triệu|trieu|tr|tỷ|tỉ|ty|ti|t)([\s]+)?/ui', '.', $priceString), ' ,.');
        
        $priceString = trim( preg_replace('/[^\d\.,]/', '', $priceString), ' ,.');
         
		
		$c1 = substr_count($priceString, ',');
		
	 
        
        if($c1 > 1) {
            $priceString = str_replace(',', '', $priceString);
            $c1 = 0;
        }
        
        $c2 = substr_count($priceString, '.');
        
        if($c1 > 0 && $c2 > 0){
            $priceString = str_replace(',', '', $priceString);
        }elseif($c1 > 0){
            $priceString = str_replace(',', '.', $priceString);
            $c2 = substr_count($priceString, '.');
        }
         
        if($c2 > 1) {
            $priceString = str_replace('.', '', $priceString);
        }elseif ($c2 > 0){
            $c3 = explode('.', $priceString);                        

            if(!$prx && (trim($c3[count($c3) - 1]) === '000' || ($c3[count($c3) - 1] > 99 && $c3[count($c3) - 1] % 100 == 0))){
                $priceString = str_replace('.', '', $priceString);
            }
            
            switch ($unit){
                case 'k':
                    
                    if(strlen($c3[count($c3) - 1]) == 3){
                        $priceString = str_replace('.', '', $priceString);
                    }
                    break;
                   
            }
             
            
            
        }
        
        return $priceString;
    }
    
    
    public function getPriceUnit($priceString){
        $priceString = unMark(preg_replace('/[\d\.,\s]/i', '', $priceString));
        $unit = '';
        if($priceString != "")
        {
            switch ($priceString) {
                case 'trieu': case 't':
                    $unit = 'tr';
                    break;
                case 'ti': 
                    $unit = 'ty';
                    break;
                    
                case 'vnd':
                    $unit = 'd';
                    break;
                default:
                    $unit = $priceString;
                    break;
            }
            
        }
        return $unit;
    }
    
    public function extractSimInfoFromString($string, $params = [])
    {
//         $origin_string = $string ;
//         $string = trim(str_replace(['O','o','..'], ['0','0','.'], $string));
        
        // 
        $pattern = $this->phonePattern();
        
        preg_match_all($pattern, $string, $match);
        
       
        
        $string = preg_replace($pattern, 'xxx', $string);
        $string = preg_replace('/[\d]{1,3}[\.-]\s?xxx/i', 'xxx', $string); 
        
//         $string = preg_replace('/xxx/i', PHP_EOL, $string); 

        $datas = explode('xxx', $string);
        
        if(!empty($datas)) {
            array_shift($datas);
        }
        
        $sim_data = [];
        
        $sims = [];
        if(!empty($match[0])){
            foreach ($match[0] as $k => $sim){
                
                $unit = isset($params['unit']) ? $params['unit'] : '';
                
               
                $simNumer = str_replace([' ',',', 'o', 'O'], ['.','.', '0', '0'], trim($sim));
                $sid = $this->getSimId($simNumer);
                
                if(strlen($sid) > 10 && (substr($simNumer, 0,2) == '84' || substr($simNumer, 0,3) == '084')){
                    $simNumer = substr($sid, -9);
                }
                
                if($this->validateOldNumber($simNumer)){
                    $simNumer = $this->convertOldNumber($simNumer);
                }
                
                
                $sims[$k] = $simNumer;
                
                if(substr_count($sims[$k], '.') == 0){
                    $sp = $this->splitNumber($sims[$k], []);
                    if(!empty($sp)){
                        $sims[$k] = $sp[0];
                    }
                }
                
                if(substr($sims[$k], 0,1) != '0'){
                    $sims[$k] = "0" . $sims[$k];
                }
                
                $sim_id = $this->getSimId($sims[$k]);
                $sim_data[$sim_id] =[
                    'display' => $sims[$k],
                    'id' => $sim_id,
                ];         
                
                if(isset($params['type_id'])){
                    $sim_data[$sim_id]['type_id'] = $params['type_id'];
                }
                
                
                if(isset($datas[$k]) && ($str = trim($datas[$k])) != ""){
                    
                    $pattern = '/(sim trả sau|trả sau|sts|ts)\s(\d+)[-\s\0\t]+\d+/ui';
                    preg_match($pattern, $str, $m2);
                    
                    if(!empty($m2)){
                        $sim_data[$sim_id]['type_id'] = 3;
                        
                        $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m2[2], '', 1, true);
                        
                        $str = trim(preg_replace('/(sim trả sau|trả sau|sts|ts)\s(\d+)[-\s\0\t]+/ui', '', $str));
                        
                    }
                     
                    
                    $pattern = '/(sim trả sau|trả sau|sts|ts)([-\s\0\t]+)?/ui';
                    preg_match($pattern, $str, $m2);
                    
                    if(!empty($m2)){
                        $sim_data[$sim_id]['type_id'] = 3;
                        
                        $str = trim(preg_replace($pattern, '', $str));
                        
                    }
 
                    
                    $pattern = '/(sim trả trước|trả trước|stt|tt)([\0\-\s]+)?/ui';
                    preg_match($pattern, $str, $m2);
                     
                    
                    
                    
                    if(!empty($m2)){
                        $sim_data[$sim_id]['type_id'] = 1;
                        $str = trim(preg_replace($pattern, '', $str));
                        
                    }
                    
                    /**
                     * 
                     */
                    $patterns = [
                        '(ck:|ck|cam kết)([\s])?(\d+([0-9Oo]+)?[\.,]?([\s]+)?(tr|k)?([\s]+)?(\d+([\.,]\d+)?)?(tr|k)?)([\s]+)?\/?([\s]+)?(\d+)?(\w(?![\S\t\0]))?',
                        //'(ck:|ck|cam kết)([\s])?(\d+([0-9Oo]+)?[\.,]?([\s]+)?(tr|k)?([\s]+)?(\d+)?(tr|k)?)([\s]+)?\/?([\s]+)?(\d+)?(\w(?![\S\t\0]))?',
                        '(\d+([0-9Oo]+)?[\.,]?([\s]+)?(tr|k)?([\s]+)?[\d+]?(tr|k)?)([\s]+)?\/(tháng|thang|th|t)',
   
                    ];
                    $pattern = '/'.implode('|', $patterns).'/i';
                    
                    
                    preg_match($pattern,trim_space($str), $m3);
                  
//                     view($m3, $str);
                    
                    if(!empty($m3)){
                        if(isset($m3[3]) && $m3[3] != ""){
                            $m3[3] = trim(str_replace(['O', 'o'], ['0', '0'], $m3[3]));
                            
                            $m4 = explode(' ', $m3[3]);
                            
                            $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m4[0], '', 1, true);
                            
                            if(isset($m4[1])){
                                $sim_data[$sim_id]['price'] = strToPrice($m4[1], '', 1, true);
                            }
                            
                        }
                        
                        if(!(isset($sim_data[$sim_id]['attrs']['commit']) && $sim_data[$sim_id]['attrs']['commit'] > 0) && isset($m3[13]) && $m3[13] != ""){
                            $m3[13] = str_replace(['O', 'o'], ['0', '0'], $m3[13]);
                            
                            $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[13], '', 1, true);
                        }
                        
                        if(isset($m3[13]) && $m3[13] != ""){
                            $m3[13] = str_replace(['O', 'o'], ['0', '0'], $m3[13]);
                            $sim_data[$sim_id]['attrs']['commit_time'] = (float)$m3[13];
                        }
                        
                        $str = trim(preg_replace($pattern, '', $str));
                    }else{
                        
                        //0888.693.936	Trả sau 49	600k
                        $pattern = '/0888.693.936	Trả sau 49	600k/ui';
                    
                        preg_match($pattern,trim_space($str), $m3);
                        
                        
                    }
                
                    
                    
                    $pattern = '/(3G|4G)\W?/i';
                    preg_match($pattern ,trim_space($str), $m3);
                    
                    if(!empty($m3)){
                        if(isset($sim_data[$sim_id]['note'])){
                            $sim_data[$sim_id]['note'] .= 'Phôi ' . $m3[0]; 
                        }else {
                            $sim_data[$sim_id]['note'] = 'Phôi ' . $m3[0]; 
                        }
                        
                        $str = preg_replace($pattern ,'', trim_space($str));
                    }
                
                    
                    
                    
                    
                    $epk = 'VD|EC|ECO|HEY|V|DN|THUONGGIA|TG|HAY';
                    
					/**
					
					*/
                    
                    //$pattern = '/(\d+([\.,]\d+([\.,]\d+)?)?)[\s]+(('.$epk.')\d+|0)\s+(\d+)\/?(\d+)?(\w+)?/ui';
                    
                    //preg_match($pattern,trim_space($str), $m3);
                     
                    
					$pattern = '/^(\d+([\.,]\d+([\.,]\d+)?)?)\s+(\d+\/\d+)/ui';
					preg_match($pattern ,trim_space($str), $m3);
					 
					
					if(!empty($m3) && $m3[0] != "" && $m3[4] != ""){
						$sim_data[$sim_id]['type_id'] = 3;
					}
					
					/**
					 * 
					 */
					
					//
					$pattern = '/(\d+([\.,]\d+([\.,]\d+)?)?)[\s]+(('.$epk.')\d+|0)\s+(\d+)\/?(\d+)?(\w+)?/ui';
					
					preg_match($pattern,trim_space($str), $m3);
				 
					
					if(!empty($m3)){
					    
					    $sim_data[$sim_id]['type_id'] = 3; 
					    
					    if(isset($m3[1]) && $m3[1] != ""){
					        $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
					    }
					    
					    $sim_data[$sim_id]['attrs']['package'] = [Yii::$app->sim->package->getItemIdFromName($m3[4])];
// 					    $sim_data[$sim_id]['note'] = isset($sim_data[$sim_id]['note']) ? $sim_data[$sim_id]['note'] . ' ' . $m3[4] : $m3[4];
					    
					    if(isset($m3[6]) && $m3[6] != ""){
					        
					        if(isset($m3[8]) && in_array(unMark($m3[8]), ['t', 'th','thang'])){
					            $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[6];
					            $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[4], '', 1, true);
					        }else{
					            
					            $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[6], '', 1, true);
					            if(isset($m3[7]) && $m3[7] > 0){
					                $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[7];
					            }
					        }
					    }
					    
					    $str = $str != 0 ? trim($m3[4]) : '';
					    
					    //
					}else{
					    $pattern = '/(\d+([\.,]\d+([\.,]\d+)?)?)[\s]+(\d+)\/?(\d+)?\s+(('.$epk.')\d+|0)/ui';
					    
					    preg_match($pattern,trim_space($str), $m3);
					     
					    
					    if(!empty($m3)){
					        
					        if(isset($m3[1]) && $m3[1] != ""){
					            $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
					        }
					        $sim_data[$sim_id]['type_id'] = 3; 
					        if(isset($m3[4]) && $m3[4] != ""){
					            $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[4], '', 1, true);
					            if(isset($m3[5]) && $m3[5] > 0){
					                
					                $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[5];
					            }
					            
					            if(isset($m3[6]) && $m3[6] != ""){
					                
					                $sim_data[$sim_id]['attrs']['package'] = [Yii::$app->sim->package->getItemIdFromName($m3[6])];
					            }
					        }
					        
					        //                             $str = trim(preg_replace($pattern, '', $str));
					        $str = $str != 0 ? trim($m3[6]) : '';
					        
					        //
					    }else{
					        
					        $pattern = '/(\d+([\.,]\d+([\.,]\d+)?)?)\s+(('.$epk.')\s?\d+)(\/)(\d+)/ui';
					        
					        preg_match($pattern,trim_space($str), $m3);
					        
					        if(!empty($m3)){
					            $sim_data[$sim_id]['type_id'] = 3; 
					            $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
					            $sim_data[$sim_id]['attrs']['package'] = [Yii::$app->sim->package->getItemIdFromName($m3[4])];
					            $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[7];
					            $sim_data[$sim_id]['note'] = isset($sim_data[$sim_id]['note']) ? $sim_data[$sim_id]['note'] . ' ' . $m3[4] : $m3[4];
					            $str = trim(preg_replace($pattern, '', $str));
					            switch (strtolower($m3[5])) {
					                case 'vd': case 'hey': case 'hay':
					                    $sim_data[$sim_id]['attrs']['commit'] = strToPrice(preg_replace('/[^0-9\.,]/ui', '', $m3[4]), '', 1, true);
					                break;
 
					            }
					        }else{					            					        					        
					        
    					        $pattern = '/('.$epk.')\d+/ui';
    					        
    					        preg_match($pattern,trim_space($str), $m3);
    					        
    					        
    					        
    					        if(!empty($m3)){
    					            
    					            if(isset($m3[0]) && $m3[0] != ""){
    					               // 
    					                $sim_data[$sim_id]['attrs']['package'] = [Yii::$app->sim->package->getItemIdFromName($m3[0])];
    					                $sim_data[$sim_id]['note'] = isset($sim_data[$sim_id]['note']) ? $sim_data[$sim_id]['note'] . ' ' . $m3[0] : $m3[0];
    					               //$sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
    					            }
     
    					            $str = trim(preg_replace($pattern, '', $str));
    					            //
    					        }
					        }
					    }
					}
					 
					/**
					 * 
					 */
					
                    if(isset($sim_data[$sim_id]['type_id']) && $sim_data[$sim_id]['type_id'] == 3){
                        
                        /* //
                        $pattern = '/^(\d+([\.,]\d+([\.,]\d+)?)?)[\s]+(('.$epk.')\d+|0)\s+(\d+)\/?(\d+)?(\w+)?/ui';
                        
                        preg_match($pattern,trim_space($str), $m3);
                                                  
                        if(!empty($m3)){
                            
                            if(isset($m3[1]) && $m3[1] != ""){
                                $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);								
                            }
                            
                            if(isset($m3[6]) && $m3[6] != ""){
                                
                                if(isset($m3[8]) && in_array(unMark($m3[8]), ['t', 'th','thang'])){
                                    $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[6];
                                    $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[4], '', 1, true);
                                }else{
                            
                                $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[6], '', 1, true);
                                if(isset($m3[7]) && $m3[7] > 0){                                     
                                    $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[7];
                                }
                                }
                            }
                            
                            $str = $str != 0 ? trim($m3[4]) : ''; 
                           
                            //
                        }else{
                            $pattern = '/^(\d+([\.,]\d+([\.,]\d+)?)?)[\s]+(\d+)\/?(\d+)?\s+(('.$epk.')\d+|0)/i';
                            
                            preg_match($pattern,trim_space($str), $m3);
                            
                            
 
                            if(!empty($m3)){
                                
                                if(isset($m3[1]) && $m3[1] != ""){
                                    $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
                                }
                                
                                if(isset($m3[4]) && $m3[4] != ""){
                                    $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[4], '', 1, true);;
                                    if(isset($m3[5]) && $m3[5] > 0){
                                        
                                        $sim_data[$sim_id]['attrs']['commit_time'] = (int)$m3[5];
                                    }
                                }
                                
                                //                             $str = trim(preg_replace($pattern, '', $str));
                                $str = $str != 0 ? trim($m3[6]) : '';
                                
                                //
                            }
                        } */
                        
                        if($str != ""){
                            $pattern = '/(\d+(([\.,][\d+]{1,3})?([\.,][\d+]{3})?)?)(triệu|trieu|tỷ|ty|tỉ|ti|k|tr|t)?(\d+)?\s+(\d+)\/?(\d+)?/ui';
                            
                            preg_match($pattern,trim_space($str), $m3);
                             
                            
                            if(!empty($m3)){
                                if(isset($m3[1]) && $m3[1] != ""){
    //                                 
                                    if(isset($m3[5]) && $m3[5] != ""){
                                        $unit = $m3[5];
                                    }
                                    
                                    if(is_numeric($m3[1]) && is_numeric($m3[6]))
                                    {
                                        $m3[1] .= '.' . $m3[6];
                                    }
                                    
                                    $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
									
                                }
                                
                                if(isset($m3[7]) && $m3[7] != ""){
                                    $sim_data[$sim_id]['attrs']['commit'] = strToPrice($m3[7], '', 1, true);;
                                    $sim_data[$sim_id]['attrs']['commit_time'] = isset($m3[8]) && $m3[8] != "" ? (int) $m3[8] : 0;
                                }
                                
                                $str = trim(preg_replace($pattern, '', $str));
                                
//                                 $str = trim(preg_replace('/(\d+(([\.,][\d+]{1,3})?([\.,][\d+]{3})?)?)(triệu|trieu|tỷ|ty|tỉ|ti|k|tr|t)?(\d+)?\s+(\d+)(\/(\d+))?/', '', $str));
                                
                               
                            }else{
                                
                            }
                        }
                        
                        //                                                
                        
                        
                        
                    }else{
						
					}
					 
					$patterns = [
					    '(=\s?thu)',
					    '(^thu)',
					    '(thu$)'
					];
					
					$str = trim(preg_replace('/' . implode('|', $patterns) . '/ui', '', $str));										 
                      
                    if(!isset($sim_data[$sim_id]['price'])){
                        
                        $pattern = '/^(\d+([\.,]\d+([\.,]\d+)?)?)\s+(('.$epk.')\d+)/i';
                        preg_match($pattern ,trim_space($str), $m3);	
                        
                        
                                                
                        if(!empty($m3)){
                            if(isset($m3[1]) && $m3[1] != ""){           
                                $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[1]),$unit, $sim_id);
                                
                            }
                             
                            $str = $m3[4];
                            
                            //                                 $str = trim(preg_replace('/(\d+(([\.,][\d+]{1,3})?([\.,][\d+]{3})?)?)(triệu|trieu|tỷ|ty|tỉ|ti|k|tr|t)?(\d+)?\s+(\d+)(\/(\d+))?/', '', $str));
 
                        }else{
                        
                        /**
                         * TH Giá thu/Giá khách
                         */
                        
                        $p1 = substr($this->pricePattern(), 1, -3);                                            
                        
                        $p2 = '/' . $p1 . '[\s+]?\/[\s+]?' . $p1 . '/ui'; 
                        
                        preg_match($p2 ,trim_space($str), $m3);
                        
                   
						 
                        if(!empty($m3) && $m3[0] != ""){
                            $m3[0] = str_replace(['O', 'o'], ['0', '0'], $m3[0]);
                            
                            $p = explode('/', $m3[0]);
                            
                            $pr1 = $this->autoPrice($this->extractNumberForPrice($p[0]),'', $sim_id);
                            
                            $pr2 = $this->autoPrice($this->extractNumberForPrice($p[1]),'', $sim_id);
                            
                            if($pr1 > 0 && $pr2 > 0){
                                $sim_data[$sim_id]['price'] = min($pr1, $pr2);
                                $sim_data[$sim_id]['price2'] = max($pr1, $pr2);
                                
                            }else{
                                $sim_data[$sim_id]['price'] = max($pr1, $pr2);
                            }
                            
                            $str = trim(preg_replace($p2, '', $str));
                        }else{
                        
                            preg_match('/db|đb|đã bán|đã bán|dã bán|da ban|daban|bay|🚀|✈|🛫|🛩/ui', $str, $m);
                            
                             
                            
                            if(!empty($m)){
                                
                                $sim_data[$sim_id]['price'] = 0;
                                $sim_data[$sim_id]['is_sold'] = 1;
                                $sim_data[$sim_id]['is_invisible'] = 1;
//                                 $sim_data[$sim_id]['is_sold'] = 1;
                                $str = '';
                                
                            }else{
                                
                                if(isset($params['price_option']) && isset($params['price_option'][1])){
                                    
                                    $pt = '/(\d+(,?\.?\d+)?)\s+(\d+(,?\.?\d+)?\s?(k|triệu|trieu|tr|t)?(\d+)?)/ui';
                                    preg_match($pt, $str, $m3);
                                     
                                    //view($m3, $str);
                                    
                                    if(!empty($m3)){
                                        
                                        foreach ($params['price_option'] as $index => $op_value){
                                            if($op_value == 'price'){
                                                
                                                switch ($index){
                                                    case 1: 
                                                        $str = $m3[$index];
                                                        break;
                                                    case 2:
                                                        $str = $m3[$index+1];
                                                        break;
                                                }
                                               
                                            }
                                        }
                                    
                                        
                                    }
                                }
                                       
                            
                                preg_match($this->pricePattern() ,trim_space($str), $m3);
                                
//                                 view($m3, $str);
								                                   
                                if(!empty($m3) && $m3[0] != ""){
                                    $m3[0] = str_replace(['O', 'o'], ['0', '0'], $m3[0]);
                                    
                                    $unit2 = $this->getPriceUnit($m3[0]); 
                                    
                                    $unit = $unit2 != "" ? $unit2 : $unit; 
                                    
//                                     view($this->extractNumberForPrice($m3[0]));
                                    
                                    $sim_data[$sim_id]['price'] = $this->autoPrice($this->extractNumberForPrice($m3[0]),$unit, $sim_id);
                                }
                                $str = trim(preg_replace($this->pricePattern(), '', $str));
                            }
                        }
                        
                        }
                    }
                    
                    preg_match('/db|đb|đã bán|đã bán|dã bán|da ban|daban|bay|🚀|✈|🛫|🛩/ui', $str, $m);
                    
                    if(!empty($m)){
                        
                        $sim_data[$sim_id]['price'] = 0;
                        $sim_data[$sim_id]['is_sold'] = 1;
                        $sim_data[$sim_id]['is_invisible'] = 1;
                        //                                 $sim_data[$sim_id]['is_sold'] = 1;
                        $str = '';
                        
                    }
                    
                    
                    
                    $note  = $str != '0' ? trim(preg_replace('/^(giá thu |gia thu |thu )([\s]+)?/i', '',   trim($str, ',.-_—=+ '))) : '';
                    
                    $note  = preg_replace('/[^\w0-9\-\.\s,]/ui', '',  $note);
                    
                    if(isset($sim_data[$sim_id]['note'])){
                        $sim_data[$sim_id]['note'] .= " $note";
                    }else{
                        $sim_data[$sim_id]['note'] = $note;
                    }
                }
				
 
                if(!isset($sim_data[$sim_id]['price'])){
                    $sim_data[$sim_id]['price'] = 0;
                    //$sim_data[$sim_id]['note'] = '';
                    
                    if(isset($params['is_fixed_sell_price']) && $params['is_fixed_sell_price'] == 'on' && isset($params['fixed_sell_price']) && $params['fixed_sell_price'] > 0){
                       $sim_data[$sim_id]['price'] = $params['fixed_sell_price'];
                    }else{
                        $sim_data[$sim_id]['note'] = '';
                    }
                    
                }
                
                if(!isset($sim_data[$sim_id]['attrs']['commit'])){
                    $sim_data[$sim_id]['attrs']['commit'] = 0;
                }
                
                if(!isset($sim_data[$sim_id]['attrs']['commit_time'])){
                    $sim_data[$sim_id]['attrs']['commit_time'] = 0;
                }
                if(isset($sim_data[$sim_id]['note'])){
                    $sim_data[$sim_id]['note'] = trim($sim_data[$sim_id]['note']);
                }
            }
            
            
        }
        
       
        
        return ['data' => $sim_data, 'state' => true];
    }
    
    public function extractSimFormSingleString($string, $params = [])
    {

        $string = trim(str_replace(['O','o','..'], ['0','0','.'], $string));

        $pattern = $this->phonePattern();

        preg_match_all($pattern, $string, $match);
        
        
        
        $string = preg_replace($pattern, 'xxx', $string);     
        $string = preg_replace('/[\d]{1,3}[\.-]\s?xxx/i', 'xxx', $string);  
        
 
        $sim_data = [];
        
        $sims = [];
        if(!empty($match[0])){
            foreach ($match[0] as $k => $sim){
                $simNumer = str_replace([' ',',', 'o', 'O'], ['.','.', '0', '0'], trim($sim));
                
                $sid = $this->getSimId($simNumer);
                
                if(strlen($sid) > 10 && (substr($simNumer, 0,2) == '84' || substr($simNumer, 0,3) == '084')){
                    $simNumer = substr($sid, -9);
                }
                
                if($this->validateOldNumber($simNumer)){
                    $simNumer = $this->convertOldNumber($simNumer);
                }
                
                
                $sims[$k] = $simNumer;
                
                if(substr_count($sims[$k], '.') == 0){
                    $sp = $this->splitNumber($sims[$k], []);
                    if(!empty($sp)){
                        $sims[$k] = $sp[0];
                    }
                }
                
                $sim_id = $this->getSimId($sims[$k]);
                $sim_data[$sim_id] =[
                    'display' => $sims[$k],
                    'id' => $sim_id,
                ];                
            }
        }
        
         
        if(!isset($sim_id)){
            return [
                'state' => false,
                'data'  => [],
                //'string' => $string
            ];
        }
        
        preg_match_all('/xxx[\s+]\d{1,3},\d{1,3}(,\d{3})?(,\d{3})?(,\d{3})?/i',trim_space($string), $match);  
        

        
        if(!empty($match[0])){                         
//             $string = implode(PHP_EOL, $match[0]);
        }
      
        if(count($sims) == 1){
            preg_match_all('/xxx[\s+]([\d\.,]+k?|tr?|t?)\s+(\d+|[\D0]+?\d+)\s?([\D]+?\d+(\D+)?)?/i',trim_space($string), $match);
            
             
            if( !empty($match[0])){
                unset($match[0]);
                $string = implode(PHP_EOL, $match[1]);
                
                if(isset($match[2])){
                    $sim_data[$sim_id]['note'] = implode(PHP_EOL, $match[2]);
                }
                if(isset($match[3])){
                    $sim_data[$sim_id]['note'] .= ' ' . implode(PHP_EOL, $match[3]);
                }
            }
        }
        
//         view($sim_data[$sim_id]);
        
        $patterns = [
            'ck[\s]+\d+(k|tr)?(\s)?\/\d+\D+',   
            'ck(\s+)?[\d+]{1,6}(k|tr)[\d+]{1,6}',
            'ck(\s+)?\d+[\.,]?\d{1,3}(k|tr)?(\s?\/\d+(\w+)?)?',
//             'ck(:[\s]\S+)?[\d+]{1,6}(k|tr)?',
//             'ck(\s+)?\d+\.?\d{3}',
            'ck[\s=]?[\d+]{1,6}(k|tr)?(\/\d+)?',
            '[\D][\d+]{1,6}(k|tr)?\/\d+\D',
            'xxx\-[\d+]{1,6}\-',
            'xxx(c|k)[\d+]{1,6}\-',
            
            'có gói',
            'đã lên gói',
            'tk\d{1,3}',
            //'(Trả trước|Tra truoc).+',
            '(Cam kết ).+',
            '(v90|v120|vd89|vd149)'
        ];
        $pattern = '/'.implode('|', $patterns).'/i';                
 
                           
        preg_match_all($pattern,trim_space($string), $match);  
       
       
//         view($match, $string);
        
        
        if(!empty($match[0])){
            $string = preg_replace($pattern, 'xxx', $string);
            
            $sim_data[$sim_id]['note'] = str_replace(['xxx-','xxxk','xxxc'], 'ck', trim($match[0][0], '/\\.=- '));
        }
         
        
        
        $patterns = [
            '(Trả trước|Tra truoc).+',           
        ];
        $pattern = '/'.implode('|', $patterns).'/i';        
        
        preg_match_all($pattern,trim_space($string), $match);
                
        if(!empty($match[0])){
 
            
            $string = preg_replace($pattern, 'xxx', $string);
            
            $sim_data[$sim_id]['type_id'] = 1;
            $sim_data[$sim_id]['note'] = trim($match[0][0], '- ');
        }else{
            $patterns = [
                '(Trả sau|Tra sau).+',
                '(sts|ts)\s?[\d]{1,4}(k|trieu|tr|t)?(\/\d{1,2}[a-zA-Z]+)?',
            ];
            $pattern = '/'.implode('|', $patterns).'/i';
            
            preg_match_all($pattern,trim_space($string), $match);
            
            if(!empty($match[0])){
                                
                $string = preg_replace($pattern, 'xxx', $string);
                
                $sim_data[$sim_id]['type_id'] = 3;
                $sim_data[$sim_id]['note'] = trim($match[0][0], '- ');
            } 
        }
        
        
        
        
        $field_price = isset($params['sale_price']) && $params['sale_price'] == 'on' ? 'price2' : 'price';
        
        // extract giá       
        
        
        
        if(count($sims) == 1){
            /**
             * O387111222 =25 250K-7/2020 | string: xxx =25tr 250K-7/2020
             */
            preg_match_all($this->pricePattern(),strtolower(trim_space($string)), $match); // xxx =25tr 250K-7/2020
            
//             view($match, strtolower(trim_space($string)));
            
            if(!empty($match[0])){
                $p1 = strToPrice($match[0][0]);
                
                if($p1 > 0){
                    $string = $match[0][0]; unset($match[0][0]);
//                     $unit = '';
                    if(!empty($match[0]) && !(isset($sim_data[$sim_id]['note']) && $sim_data[$sim_id]['note'] != "")){
                        $sim_data[$sim_id]['note'] = implode('|', $match[0]);
                    }
                     
                }
            }
        }
        
        preg_match_all($this->pricePattern(), str_replace(['.',','], '.', strtolower(trim_space($string))), $match);
     
//         view($match);
 
        if(count($sims) == 1 && count($match[0]) > 1){
            
            $p1 = strToPrice($match[0][0]);
            $p2 = strToPrice($match[0][1]);
            
            $p3 = min($p1, $p2);
            $p4 = max($p1, $p2);
            
            if($p3 * 2 > $p4){
                
            }
            
            if(!is_numeric($match[0][1]) && !isset($sim_data[$sim_id]['note'])){
                $sim_data[$sim_id]['note'] = $match[0][1];
                unset($match[0][1]);
            }
            
            
            
            if(isset($match[0][1])){
                $m1 = max($match[0][0], $match[0][1]);
                
                foreach ($match[0] as $price){

                    //view($price);
                    if($price > 0 && $price < $m1 && $price * 2 > $m1){
                        $m1 = $price;
                    }
                }
                
                $match[0] = [$m1];
            
            }else{
                $match[0] = [$match[0][0]];
            }
        }
        
        $gia = [];
        
         
        
        if(!empty($match[0])){
            foreach ($match[0] as $k => $sim){
                 
 
                $unit = isset($params['unit']) ? $params['unit'] : '';
                
                 
                
                $validated_price = false;
                
                /**
                 * 
                 */ 
                $c = (substr_count($sim, '/'));
               
                if($c > 0){
                    $c2 = explode('/', $sim);
                    
                    $t1 = strToPrice($c2[0] , $unit); $t2 = strToPrice($c2[count($c2)-1], $unit);
                    
                    $p1 = min($t1, $t2);

                    if(abs($t1 - $t2) > $p1 * 10){
                        
                        $du = max($t1, $t2) / min($t1,$t2);
                                                 
                        $heso = 1;
                        if($du > 1000000000){
                            $heso = 1000000000;
                        }elseif($du > 1000000){
                            $heso = 1000000;
                        }elseif($du > 1000){
                            $heso = 1000;
                        }elseif($du > 100){
                            $heso = 100;
                        }
                        
                        $p1 *= $heso;
                        
                    }
                    
                    
                    if($p1 == 0){
                        $p1 = max($t1, $t2);
                    }
                    
                    if($p1 > 0){
                        $sim = $p1;
                        $unit = '';
                    }
                    
                }
                 
                
                preg_match('/tỷ|ty|tỉ|ti/',$sim, $m2);
             
              
                
                if(!empty($m2)){
                    $pr1 = trim(str_replace(['tỷ','ty', 'tỉ','ti',' '], ['.','.','.','.',''], trim_space($sim)),'.,');
                     
                    
                    if(is_numeric($pr1) && $pr1 > 0){
                        
                        
                        $c2 = explode('.', $pr1);
                        if(strlen($c2[1]) == '000'){
                            $price = trim(str_replace(['.',','], '', $pr1));
                            
                            
                        }else{
                            
                            $price = $pr1;
                            $unit = 'ty';
                            
                        }
                        
                        $validated_price = true;
                        
                        
                        
                    }else{
                        $price = $this->extractNumberForPrice($sim);
                        
                        
                    }
                    
                     
                }else{
                 
                preg_match('/triệu|trieu|tr|t/ui',$sim, $m2);
               
                
                if(!empty($m2)){
                
                    $pr1 = trim(str_replace(['triệu','trieu', 'tr','t',' '], ['.','.','.','.',''], trim_space($sim)), '.,');
             
                    
                    
                    if(is_numeric($pr1) && $pr1 > 0){
                        
                        
                        $c2 = explode('.', $pr1);
                        
                       
                        
                        if(isset($c2[1]) && strlen($c2[1]) == '000'){
                            $price = trim(str_replace(['.',','], '', $pr1));
                            
                            
                        }else{
                        
                        $price = $pr1; 
                        $unit = 'tr';
                        
                        }
                        
                        $validated_price = true;                        
                        
                    }else{
                        $price =   preg_replace('/[^\d\.,]/', '', $sim);
//                         $price =   preg_replace('/[\D\.,]/', '', $sim);

                    }
                }else{
                    
                    $price = $this->extractNumberForPrice($sim);
                     
                }
                
                
                }
                  
                 
                
                if(!$validated_price){
                    
                    preg_match('/[\d\.,].*/', $sim, $m2);
                    
                                                 
                    
                    if(!$validated_price && !empty($m2)){
                        
                        if($price > 1000){
                            
                        }else{
                        
                            $price = trim(str_replace(',', '.', $m2[0]));
                        }
                    }
                                 
                
                    $c = (substr_count($price, '.'));
                    
              
                    
                    if($c > 1){
                        $price = trim(str_replace(['.',','], '', $price));
                    }elseif($c == 1){
                        $c2 = explode('.', $price);
                        
                        if(is_numeric($c2[1]) && strlen($c2[1]) == 3 
//                             && substr($c2[1], -2) == '00'
                            ){
                            $price = trim(str_replace(['.',','], '', $price));
                        }else{
                            
                        }
                        
                         
                    } 
                  
                    
                }
                  
                
                $price = (float) $price;
                
                preg_match('/\D.*|\D.*[\s]|[\D\.].*|[\D\.].*[\s]/', str_replace([',','.'], '',$sim), $m2);
                
                /* $ext = 1;
                //if(isset($params['unit'])){
                    switch ($unit) {
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
                //}
                   */
                
//                 view($m2);
                
                if(!empty($m2)){
                    /* switch (trim($m2[0])){
                        case 'tỉ': case 'ti': case 'tỷ': case 'ty':
                            $price *= 1000000000;
                            break;
                        case 'tr':
                        case 'triệu':
                            $price *= 1000000;
                            break;
                        case 'k':
                            $price *= 1000;
                            break;
                            
                        case 'd': case '₫': case 'đ': case 'vnd': case 'vnđ':
                        case 'D': case 'Đ': case 'VND': case 'VNĐ':
                             
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
                    } */
                    
                    //$price = $this->autoPrice($price, trim($m2[0]), $sim_id);
//                     $unit = trim($m2[0]);
                }else{
                    
                    $price = trim(str_replace(['\r\n'], '', $price));
                    
                    //$price = $this->autoPrice($price, $unit, $sim_id);
                    
                     
                }
                 
//                 view($price, $unit);
                //$gia[$k] = $price; //str_replace([' ',','], [' '], trim($sim));
                $gia[$k] = $price = $this->autoPrice($price, $unit, isset($sims[$k]) ? $sims[$k] : '');
                
                 
                if(count($sims) == 1){
                    $sim_data[$sim_id][$field_price] = $price;
                    
                    preg_match('/(sim.*trả sau.*)|(trả sau.*)|(tra sau.*)|(sts.*)|(ts.*)/i', $string, $m3);
                     
                    if(!empty($m3)){
                        $sim_data[$sim_id]['type_id'] = 3;
                        $sim_data[$sim_id]['note'] = trim($m3[0]);
                    }else{
                        preg_match('/(sim.*trả trước.*)|(tra truoc.*)|(stt.*)|(tt.*)/i', $string, $m3);
                    
                        
                        if(!empty($m3)){
                            $sim_data[$sim_id]['type_id'] = 1;
                            $sim_data[$sim_id]['note'] = trim($m3[0]);
                        }
                    }
                    
                    
                    
                    
                    break;
                }
                
            }
        } 
        
        $state = count($sims) === count($gia);
        
        if(!$state && empty($gia)){
            $state = true;
        }
        
        $type_id = post('type_id', 0);
        
        $data = [];
        if(!empty($sims)){
            foreach($sims as $k => $sim){
                
                $sim_id = $this->getSimId($sim);
                
                if(!isset($sim_data[$sim_id]['type_id'])){
                    $sim_data[$sim_id]['type_id'] = $type_id;
                }
                
                $price = isset($sim[$field_price]) ? $sim[$field_price] : ( isset($gia[$k]) ? $gia[$k] : 0);
                
//                 if($price > 0){
                    $data[] = array_merge([
                        'id' =>  $this->getSimId($sim),
                        'display'=>(substr($sim, 0,1) == '0' ? $sim : makePhoneNumber($sim)),
                        $field_price => isset($sim[$field_price]) ? $sim[$field_price] : ( isset($gia[$k]) ? $gia[$k] : 0),
                    ], 
                        $sim_data[$sim_id],
                        $this->getSiminfo($sim));
//                 }
            }
        }
         
        
//         view($sim_data,1,1);
        
        return [
            'state' => $state,
            'data'  => $data,
            //'string' => $string
        ];
        
    }
    
    
    public function autoPrice2($inputPrice, $simInfo)
    {
        
    }
    
    public function autoPrice($price, $unit = '', $simId = null)
    {

        $u = unMark($unit);
        switch ($u) {
            case 'ti': case 'ty':
                $ext = 1000000000;
                break;
                
            case 'tr': case 'trieu':
                $ext = 1000000;
                break;
                
            case 'k':
                $ext = 1000;
                break;
                
            case 'd': case 'vnd':
                $ext = 1;
                break;
            default:
                $ext = 0;
                break;
                
        }
        
        
        
        if($ext == 0 && is_numeric($price)){
            
            $vld = false;
            
            
            if($this->validatePhoneNumber($simId)){
                $sosim = $this->getSimId($simId);
                $type = $this->findSimType($sosim,1, [1]);
                
                //view($type); 
                 
                $sf = $this->getSiminfo($sosim);      
                
                //view($sf);
                
                if(!empty($type)){
                    $vld = true;
                    
                    switch (true)
                    {
                        case $type['priority'] > 11:
                            
                            if($price < 100)
                            {
                                $price = $price * 1000000;
                            }elseif($price < 85000){
                                $price = $price * 1000;
                            }
                            
                            break;
                        case ($type['priority'] > 5 and $type['priority'] < 12):
                            
                            $l1 = 300;
                            
                            if(substr($sosim, 0,1) == 9){
                                $l1 = 300;
                            }
                            
                            
                            
                            if($price < $l1 && $price < 10000)
                            {
                                $price = $price * 1000000;
                            }elseif($price < 100000){
                                $price = $price * 1000;
                            }
                            break;
                            
                        case ($type['priority'] < 4 && $type['priority'] > 2 && $price < 1000000):
                            
                            
                            
                            if($price < 1000)
                            {
                                $price = $price * 1000000;
                            }elseif($price < 1000000){
                                $price = $price * 1000;
                            }
                            break;
                            
                        case ($type['priority'] < 3 && $price < 10000000):
                            
                            //view($price);
                            
                            if($price < 1000000)
                            {
                                $price = $price * 1000000;
                            }elseif($price < 10000000){
                                $price = $price * 1000;
                            }
                            break;
                            
                        case ($type['priority'] > 3 and $type['priority'] < 6):
                            $p1 = 250; $p2 = 100000;
                            
                            //view($price);
                            
                            if(substr($sosim, 0,1) == 9){
                                $p2 += 50000;
                                $p1 += 50;
                                if($sf['number_of_key'] < 3){
                                    $p1 += 250;
                                    $p2 += 150000;
                                }elseif($sf['number_of_key'] < 5){
                                    $p1 += 150;
                                    $p2 += 100000;
                                }
                                
                                if(substr($sosim, 0,2) == '92'){
                                    $p1 -= 50;
                                    $p2 -= 50000;
                                }
                            }
                                                                                    
                            if(in_array($sf['category_id'] , [4])){
                                $p2 *= 2;
                            }
                            
                            if(in_array($sf['category3_id'] , [84])){
                                $p2 *= 1.5;
                            }
                            
                            
                            if($sf['number_of_key'] < 3){
                                $p2 *= 3;
                            }elseif($sf['number_of_key'] < 4){
                                $p2 *= 2;
                                $p1 += 100;
                            }                            
							
                            //view("$p1/$p2");
                            
                            if($price < $p1 && $price < 10000)
                            {
                                $price = $price * 1000000;
                            }elseif($price < $p2){
                                $price = $price * 1000;
                            }
                            break;
                    }
                    
                }else{
                    if($price < 35)
                    {
                        $price = $price * 1000000;
                    }elseif($price < 35000){
                        $price = $price * 1000;
                    }
                }
                
            }
            if(!$vld){
            
                if($price<300){
                    $price = $price * 1000000;
                }elseif($price<100000){
                    $price = $price * 1000;
                }
            }
            return $price;
        }
        
        return $price * $ext;
        
    }
    
    public function updateSimToModule($sosim, $module_name)
    {
        
        $sim_id = ltrim(preg_replace('/\D/', '', $sosim),'0');
        
        $query = new \yii\mongodb\Query();
        $query->from('simonline')->where(['id' =>$sim_id]);
        $sim = $query->one();
        
        Yii::$app->db->createCommand()->delete(SimonlineModuleModel::tableName(), ['id' => $sim_id])->execute();
         
        
        if(!empty($sim)){
            $sim['module_name'] = $module_name;
            
            if(!isset($sim['id'])){
                $sim['id'] = $sim['id'];
            }
            
            unset($sim['id']);
            Yii::$app->db->createCommand()->insert(SimonlineModuleModel::tableName(), $sim)->execute();
        }
        
    }
    
    
    public function validateSimData($data)
    {
     
        $fileds = [
            'id',
            'display',
            'network_id',
            'category_id',
            'category2_id',
            'category3_id',
            'price',
            'price1',
            'price2',
            'status',
            'score',        // Tổng số nut
            'number_of_key', // Tổng số phím
            //'istm',         // Trả góp
            'type_id',
            //'fixed_price',
            //'fixed_profit',            
            //'store_name',
            //'dauso',
            //'s6'
        ];
        
        foreach ($fileds as $field){
            if(!isset($data[$field])) return false;
        }
        
        return true;
    }
    
    /**
     * Cập nhật từng sim
     * 
     */
    
    
    public function updateSingleSimData($simId, $partner_id, $data, $params = [])
    {
        
        $this->getEs()->updateSingleSimData($simId, $partner_id, $data, $params);
    
        
        $rewrite_duplicate = isset($params['rewrite_duplicate']) && !$params['rewrite_duplicate'] ? false : true;
        
        $simId = $this->getSimId($simId);
        
        $partner_id = (int) $partner_id;
        
        if(!$this->validateSimData($data)){
            $data = array_merge($this->getSiminfo($simId),$data);
        }
        
        $s = [] ; // $this->findSim($simId, $partner_id);
        
        $items = $this->getSimDetail($simId);
        
        $db = 0;
        if(!empty($items)){
            foreach ($items as $sim){
                if($sim['partner_id'] == $partner_id){
                    $s = $sim;
                }else{
                    $db ++;
                }
            }
        }
        
        $data['duplicate'] = $db;
        
        if(isset($data['_id'])){
            unset($data['_id']);
        }
        
        
        if(isset($data['price'])){
            $data['price'] = (float)$data['price'];
        }
        
        if(isset($data['price0'])){
            $data['price0'] = (float)$data['price0'];
        }
        
        if(isset($data['price1'])){
            $data['price1'] = (float)$data['price1'];
        }
        
        if(isset($data['price2'])){
            $data['price2'] = (float)$data['price2'];
        }
        
        if(isset($data['partner_id'])){
            $data['partner_id'] = (int)$data['partner_id'];
        }
        if(isset($data['type_id'])){
            $data['type_id'] = (int)$data['type_id'];
        }
         
        
        if(isset($data['price']) && $data['price'] < 1){
            unset($data['price']);
//             if(isset($data['price2'])){
//                 unset($data['price2']);
//             }
        }
        
        if(isset($data['price0']) && $data['price0'] < 1){
            unset($data['price0']);
        }
        
        if(isset($data['price1']) && $data['price1'] < 1){
            unset($data['price1']);
        }
        
        if(isset($data['price2']) && $data['price2'] < 1){
            unset($data['price2']);
        }
        
        if(!isset($data['price0']) && isset($data['price0']) && $data['price0'] > 1){
            $data['price0'] = $data['price1'];
        }
        
        if(isset($params['children_id']) && $params['children_id'] > 0){
//             $this->getCollection()->remove(['id' => $simId, 'partner_id' => $params['children_id']]);
        }
        
        if(isset($params['parent_id']) && $params['parent_id'] > 0){
            $s2 = $this->findSim($simId, $params['parent_id']);
            if(!empty($s2)){
                return;
            }
        }
        
        if(isset($data['display'])){
            $data['display'] = trim(trim_space($data['display']), '.,');
            
            if(substr_count($data['display'], '.') == 0){
                $sp = $this->splitNumber($data['display'], $data);
                if(!empty($sp)){
                    $data['display'] = $sp[0];
                }
            }
        }
        
        
        if(!empty($s)){
            
            foreach([
                'is_sold' , 'is_invisible'
            ] as $f1){
                if(!isset($s[$f1])){
                    $data[$f1] = 0;
                }
                
            }
            
            $data['updated_at'] = time();                        
            
            // Kiểm tra có thay đổi giá thu
            if(isset($data['price']) && $data['price'] != $s['price'] && $s['price'] > 0){
                
            

                $history = isset($s['history']) && is_array($s['history']) ? $s['history'] :(isset($s['history']) ? json_decode($s['history'], 1) : []);
                
                if(isset($history['price']) && !empty($history['price'])){
                    
                    $px = $data['price'] - $s['price']; // Lấy giá mới - giá cũ
                    
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  $data['price'],
                        'last_price' => $s['price'],
                    ];
                    
                    $history['price'][] = $pre;
                    
                    $data['exchange_price'] = round($px / $s['price'] * 100 , 2);
                    
                }else{
                    
                    $pre = [
                        'time'  =>  time(),
                        'price' =>  $data['price'],
                        'last_price' => $data['price'],
                    ];
                    
                    $history['price'] = [$pre];
                    $data['exchange_price'] = 0;
                }
                
                
                $data['history'] = json_encode($history);
            }else{
                $data['exchange_price'] = 0;
            }
             
            if($rewrite_duplicate){
                $this->getCollection()->update(['id' => $simId, 'partner_id' => $partner_id],$data);
            }

            
        }else{
            
            $s = new SimonlineMongodbModel();
            
            $aa = [
                'price',
                'price0',
                'price1',
                'price2',
                'partner_id',
                'score',
                'number_of_key',
                'network_id',
                'category_id',
                'category2_id',
                'category3_id',
                'type_id',
                'fixed_price',
                'istm',
                'duplicate',
                'exchange_price',
                'nguhanh',
                'nut',
                'is_sold',
                'is_invisible',
                'p_invisible'
            ];
            
            
            foreach ($aa as $field){
                $data[$field] = isset($data[$field]) ? (int) $data[$field] : 0;
            }
            
            
                        
            foreach ($data as $key => $value){                
                $s->$key = $value;                
            }
            
            if(!($s->price + $s->price2 > 0)){
                return false;
            }
            
            $s->partner_id = $partner_id;
            $s->updated_at = time();
            $s->created_time = time();
            
            if(!isset($data['status'])){
                $s->status = -1;
            }
            
            return $s->save();
        }
    }
     
    
    public function updateSimData($data, $params){
        
        
        $existed = [];        
      
        $l = isset($data['data']) ? $data['data'] : $data;    
        
        if(!empty($l)){
                                            
            foreach ($l as $sim){      
                
                $this->updateSingleSimData($sim['id'], $params['partner_id'], $sim);
                
                $existed [] = $sim['id'];
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
            $max_price = 300000;
        }elseif($price < 2000000){
            $max_price = 330000;
        }elseif($price < 4000000){
            $max_price = 500000;
        }elseif($price < 5000000){
            $max_price = 600000;
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
    
    
    public function getSalePrice2($price, $params = [])
    {
        if($price < 10000) return 0;
        
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
            $max_price = 25000000;
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
        if($price < 10000) return 0;
        
        switch ($group_id) {
            case 3: // CTV mới
                if($price < 300000){
                    $max_price = 50000;
                }elseif($price < 600000){
                    $max_price = 80000;
                }elseif($price < 1200000){
                    $max_price = 100000;
                }elseif($price < 2000000){
                    $max_price = 120000;
                }elseif($price < 3300000){
                    $max_price = 150000;                    
                }elseif($price < 4000000){
                    $max_price = 180000;
                }elseif($price < 5500000){
                    $max_price = 190000;
                }elseif($price < 7000000){
                    $max_price = 250000;
                    
                //////////////////////////////////////
                }elseif($price < 8000000){
                    $max_price = 280000;
                }elseif($price < 10000000){
                    $max_price = 290000;
                }elseif($price < 15000000){
                    $max_price = 350000;
                }elseif($price < 20000000){
                    $max_price = 380000;
                }elseif($price < 30000000){
                    $max_price = 450000;
                }elseif($price < 50000000){
                    $max_price = 550000;
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
                
            case 5: // CTV Boss                                
                
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
    
    
    public function getSortValue($field, $dir = 'asc')
    {
        $sort = -1;
        
        switch ($field){
            case 'id':
                $sort = $dir == 'desc' ? 6 : 5;
                break;
            case 'network': case 'network_id':
                $sort = $dir == 'desc' ? 4 : 3;
                break;
            case 'price':
                $sort = $dir == 'desc' ? 22 : 21;
                break;
            case 'price2':
                $sort = $dir == 'desc' ? 2 : 1;
                break;
            case 'category': case 'category_id':
                $sort = $dir == 'desc' ? 8 : 7;
                break;
            case 'score':  
                $sort = $dir == 'desc' ? 14 : 13;
                break;
            case 'number_of_key':
                $sort = $dir == 'desc' ? 16 : 15;
                break;
            case 'type_id':
                $sort = $dir == 'desc' ? 18 : 17;
                break;    
                
            case 'partner': case 'partner_id':
                $sort = $dir == 'desc' ? 10 : 9;
                break;
            case 'time': case 'updated_at':
                $sort = $dir == 'desc' ? 12 : 11;
                break;
        }
        
        return $sort;
    }
    
    
    public function getDefaultSellDiscountConditions()
    {
        /*
         *  giá bán  < 1 triệu: 30%
 giá bán từ 1 triệu – 5 triệu: 25%
 giá bán > 5 triệu: 20%
 giá bán > 10 triệu: 10%

         */
        $conditions = ['default' => 
            [
                [
                    'min_price'   =>  0,  'max_price'   =>  999999,   'discount'  =>  30,  'min_value' =>  0,  'max_value' =>  0,
                ],
                [
                    'min_price'   =>  1000000,  'max_price'   =>  5000000,   'discount'  =>  25,  'min_value' =>  0,  'max_value' =>  0,
                ],
                [
                    'min_price'   =>  5000001,  'max_price'   =>  10000000,   'discount'  =>  20,  'min_value' =>  0,  'max_value' =>  0,
                ],
                [
                    'min_price'   =>  10000001,  'max_price'   =>  0,   'discount'  =>  10,  'min_value' =>  0,  'max_value' =>  0,
                ],
            ],
        ];
        
        
        return $conditions;
    }
    
    public function getSellDiscountConditions($price)
    {
        
        $conditions = [
            [
                'min_price'   =>  0,  'max_price'   =>  1000000,   'discount'  =>  30,  'min_value' =>  100000,  'max_value' =>  300000,
            ],
            [
                'min_price'   =>  0,  'max_price'   =>  1000000,   'discount'  =>  30,  'min_value' =>  100000,  'max_value' =>  300000,
            ],
            [
                'min_price'   =>  0,  'max_price'   =>  1000000,   'discount'  =>  30,  'min_value' =>  100000,  'max_value' =>  300000,
            ],
            [
                'min_price'   =>  0,  'max_price'   =>  1000000,   'discount'  =>  30,  'min_value' =>  100000,  'max_value' =>  300000,
            ],
        ];
        
        
        foreach ($conditions as $con){
            if($price >= $con['min_price'] && $price<=$con['max_price']){
                return $con;
            }
        }
    }
    
    
    public function getSellPriceFromAgentPrice($agentPrice, $params = [])
    {
        return $this->getSale()->getSellPriceFromAgentPrice($agentPrice, $params);
        
        if($agentPrice == 0){
            return 0;
        }
        
        /**
         * Công thức tính giá ngược
         * VD: 
         * Ta có giá gốc  = 300k && ck = 30%
         * Nếu tính giá thuận => giá mới = 300k * 1.3
         * Nếu tính giá ngược ta phải tìm giá mới sau khi chiết khấu 30% = 300k
         * @var Ambiguous $reverse
         */
        $reverse = isset($params['reverse']) && $params['reverse'] === true ? true : false;
        
        // Làm tròn kết quả (mặc định sẽ làm tròn lên 1000đ)
        $round = isset($params['round']) && $params['round']>0 ? $params['round'] : 10000;
        
        
        $conditions = [];
        
        if(isset($params['quotation_id']) && $params['quotation_id'] > 0){
            
            $conditions = $this->getQuotation()->model->getConditionsByQuotation($params['quotation_id'], [
                'price' => $agentPrice
            ]);

            
        }
        if(empty($conditions)){
        
            if(!isset($params['group_name'])){
                $params['group_name'] = "partner_" . $params['partner_id'];
            }
            
            $group_name = $params['group_name'];
            
            $conditions = $this->getDiscountConditions($group_name, [
                'price' => $agentPrice
            ]);
            
             
            
            if(empty($conditions)){
                $conditions = $this->getDiscountConditions($group_name, [
                    'price' => 0
                ]);
            }
        
        }
        $profit_value = 0;
        
        $con = [];
        
        if(!empty($conditions)){
            foreach ($conditions as $c){
                
                $profit_value = $c['profit_value'];
               
                
                
                //
                if(count($conditions) > 1 && isset($params['simId'])){
                    
                    $con = $c; break;
                    
                    $info = Yii::$app->sim->getSimInfo($params['simId']);
                    
                    $st = false;
                    
                    if($c['condition1'] != ""){
                        $c1 = json_decode($c['condition1'],1);
                        if(isset($c1['category_id']) && (in_array($info['category_id'], $c1) || in_array($info['category3_id'], $c1)) ){
                            $con = $c;                          
                            $st = true; 
                        }else{
                            $st = false;
                        }
                    }
                    
                    if($c['condition2'] != ""){
                        $c1 = json_decode($c['condition2'],1);
                        if(isset($c1['category2_id']) && in_array($info['category2_id'], $c1)){
                            $con = $c;
                            $st = true;
                        }else{
                            $st = false;
                        }
                    }
                    
                    if($st ){
                        $con = $c; break;
                    }else{
                        $con = $conditions[0];
                    }
                    
                }else{
                    $con = $c;
                }
            }
        }
         
        if($reverse){
            $profit_price = $agentPrice / (1 - $profit_value/100) - $agentPrice;
        }else{
            $profit_price = $agentPrice * $profit_value/100;
        }
        
        $profit_price = max($con['min_value'], $profit_price);
        
        if($con['max_value'] > 0){
            $profit_price = min($con['max_value'], $profit_price);
        }
                
        $sellPrice = $agentPrice + $profit_price;
        
        
        
        $p1 = (int) ($sellPrice / $round);
        
        if($p1 * $round < $sellPrice){
            $sellPrice = ($p1 * $round) + $round;
        }
                
        return $sellPrice;
    }
    
    
    
    public function getAgentPriceFromSellPrice($sellPrice, $params = [])
    {
        return $this->getSale()->getAgentPriceFromSellPrice($sellPrice, $params);
        
//         if(!isset($params['group_name'])){
//             $params['group_name'] = "partner_" . $params['partner_id'];
//         }
        
//         $group_name = $params['group_name'];
        
//         $conditions = $this->getDiscountConditions($group_name, [
//             'price' => $sellPrice
//         ]);
        
//         $profit_value = 0;
        
//         if(!empty($conditions)){
//             foreach ($conditions as $c){
                
//                 $profit_value = $c['profit_value'];
//             }
//         }
        
//         $agentPrice = $sellPrice * (1 - $profit_value/100);
        
//         $step = 10000;
        
//         $p1 = (int) ($agentPrice / $step);
        
//         if($p1 * $step < $agentPrice){
//             $agentPrice = ($p1 * $step) + $step;
//         }
        
//         return $agentPrice;
    }
    
    
    public function getDiscountConditionsByPrice($s)
    {
        return (new \yii\db\Query())->from(\izi\sim\SimDiscountConditions::tableName())->where(['sid' => __SID__, 'group_name'=>$group_name])
        ->orderBy(['min_price'=>SORT_ASC,'max_price'=>SORT_ASC])
        ->all();
    }
    
    
    private $_discount = [] ;
    public function getDiscountConditions($group_name, $params = [])
    {
        $scache = md5(json_encode([$group_name, $params]));
        
        if(isset($this->_discount[$scache])){
            return $this->_discount[$scache];
        }
        
        $query = (new \yii\db\Query())->from(\izi\sim\SimDiscountConditions::tableName())->where(['sid' => __SID__, 'group_name'=>$group_name]);
        
        if(isset($params['price']) && $params['price']>0){
            $query->andWhere(['<=', 'min_price', $params['price']]);
            
            $query->andWhere(['or' ,['>=', 'max_price', $params['price']], ['max_price' => 0]]);
        }
        
        
        $this->_discount[$scache] = $query->orderBy(['min_price'=>SORT_ASC,'max_price'=>SORT_ASC])->all();
        
        return $this->_discount[$scache];
    }
    
    
    public function getDiscountConditionByQuotation($quotation_id, $params = [])
    {
        $scache = md5(json_encode(["quotation_$quotation_id", $params]));
        
        if(isset($this->_discount[$scache])){
            return $this->_discount[$scache];
        }
        
        $query = (new \yii\db\Query())->from(\izi\sim\SimDiscountConditions::tableName())->where(['sid' => __SID__, 'quotation_id'=>$quotation_id]);
        
        if(isset($params['price']) && $params['price']>0){
            $query->andWhere(['<=', 'min_price', $params['price']]);
            
            $query->andWhere(['or' ,['>=', 'max_price', $params['price']], ['max_price' => 0]]);
        }
        
        
        $this->_discount[$scache] = $query->orderBy(['min_price'=>SORT_ASC,'max_price'=>SORT_ASC])->all();
        
        return $this->_discount[$scache];
    }
    
    
    public function getProfitValue($params)
    {
        
    }
    
    
    public function splitNumber($phoneNumber, $data = [])
    {
        if(!isset($data['category_id'])){
            $data = $this->getSiminfo($phoneNumber);
        }
        
        $br = false;
        
        $r = [];
        
        $so = makePhoneNumber($phoneNumber);
        
        //$r[] = $data['category_id'] . ' / ' . $data['category3_id'];
        
        switch ($data['category_id']){
            
            case 2: // ngu quý
                $s1 = substr($so, 0, 2);
                
                $s2 = substr($so, 2, 3);
                
                $s3 = substr($so, -5);
                
                
                $r[] = "$s1.$s2.$s3";
                
                $s1 = substr($so, 0, 3);
                
                $s2 = substr($so, 3, 2);
                
                $s3 = substr($so, -5);
                
                $r[] = "$s1.$s2.$s3";
                
                $br = true;
                
                break;
                
            case 3: // tu quý
                $s1 = substr($so, 0, 4);
                
                $s2 = substr($so, 4, 2);
                
                $s3 = substr($so, -4);
                
                
                $r[] = "$s1.$s2.$s3";
                
                $s1 = substr($so, 0, 3);
                
                $s2 = substr($so, 3, 3);
                
                $s3 = substr($so, -4);
                
                
                $r[] = "$s1.$s2.$s3";
                
                $s1 = substr($so, 0, 2);
                
                $s2 = substr($so, 2, 4);
                
                $s3 = substr($so, -4);
                
                
                $r[] = "$s1.$s2.$s3";
                
                
                $br = true;
                
                break;
                
                
            case 5: // tam hoa kép
                
                
                $s1 = substr($so, 0, 4);
                
                $s2 = substr($so, 4, 3);
                
                $s3 = substr($so, -3);
                
                
                $r[] = "$s1.$s2.$s3";
                $br = true;
                break;
                
            case 4: // taxi
                switch ($data['category3_id']){
                    case 84: // tx2
                        $s1 = substr($so, 0, 4);
                        
                        $s2 = substr($so, 4, 2);
                        
                        $s3 = substr($so, -2);
                        $s4 = substr($so, -4, -2);
                        
                        
                        $r[] = "$s1.$s2.$s3.$s4";
                        $br = true;
                        break;
                    case 85: // tx3
                        $s1 = substr($so, 0, 4);
                        
                        $s2 = substr($so, 4, 3);
                        
                        $s3 = substr($so, -3);
                        
                        
                        $r[] = "$s1.$s2.$s3";
                        $br = true;
                        break;
                    case 86: // tx4
                        $s1 = substr($so, 0, 2);
                        
                        $s2 = substr($so, 2, 4);
                        
                        $s3 = substr($so, -4);
                        
                        
                        $r[] = "$s1.$s2.$s3";
                        $br = true;
                        break;
                }
                break;
        }
        
        if(empty($r)){
            $r[] = $data['display'];
        }
        
        return $r;
    }
    
    
    public function buildConditions($params)
    {
        extract($params);
        $list_sim = isset($params['list_sim']) ? $params['list_sim'] : (isset($params['listsim']) ? $params['listsim'] : '');
        
//         $data = Yii::$app->sim->extractSimFormString($list_sim);
        
//         $sims = $data['data'];
        
//         foreach ($sims as $sim){
//             $in_array[] = $sim['id'];
//         }
        
        $conditions = ['and'];
        
//         if(!empty($in_array)){
//             $conditions[] = ['id' => $in_array];
//         }
        
        $c2 = [];
        
        if(isset($params['network_id']) && $network_id > 0){
            $c2['network_id'] = (int)$network_id;
        }
        
        if(isset($params['category_id']) && is_numeric($category_id) && $category_id > -1){
            $conditions[] = ['or', ['category_id' => (int)$category_id] ,['category3_id' => (int)$category_id]];
        }
        
        if(isset($params['type_id']) && $type_id > 0){
            $c2['type_id'] = (int)$type_id;
        }
        
        if(isset($params['partner_id']) && $partner_id > 0){
            $c2['partner_id'] = (int)$partner_id;
        }
        
        
        if(isset($params['min_score']) && isset($min_score) && $min_score>0){
            $conditions[] = ['>', 'score' , (int)$min_score - 1];
        }
        if(isset($params['max_score']) && isset($max_score) && $max_score>0){
            $conditions[] = ['<', 'score' , (int)$max_score + 1];
        }
        
        if(isset($params['min_key']) && isset($min_key) && $min_key>0){
            $conditions[] = ['>', 'number_of_key' , (int)$min_key - 1];
        }
        if(isset($params['max_key']) && isset($max_key) && $max_key>0){
            $conditions[] = ['<', 'number_of_key' , (int)$max_key + 1];
        }
        
        if(isset($params['category2_id']) && isset($category2_id) && $category2_id > 0){
            $c2['category2_id'] = (int)$category2_id;
        }
        
        if(isset($params['category3_id']) && isset($category3_id) && $category3_id > 0){
            $c2['category3_id'] = (int)$category3_id;
        }
        
        if(!in_array($list_sim , ['', '*', '.*', '.','+'])){
            
            $pattern = Yii::$app->sim->phonePattern();
            
            preg_match_all($pattern, $list_sim, $match);
            
            if(!empty($match[0])){
                $l1 = [];
                
                foreach($match[0] as $sim_number){
                    $l1[] = Yii::$app->sim->getSimId($sim_number);
                }
                
                
                $c2['id'] = $l1;
                
            }
        }
        
        
        if(isset($params['sosim']) && trim($sosim) != "" && !in_array($sosim , ['', '*', '.*', '.','+'])){
            
            $pattern = Yii::$app->sim->phonePattern();
            
            preg_match_all($pattern, $sosim, $match);
            
            if(!empty($match[0])){
                $l1 = [];
                
                foreach($match[0] as $sim_number){
                    $l1[] = Yii::$app->sim->getSimId($sim_number);
                }
                
                $c2['id'] = $l1;
                
                
            }else{
            
            
            $sosim = str_replace(['o', 'O'], '0', $sosim);
            $sosim = ltrim($sosim, '0^ ');
            $sosim = rtrim($sosim, '$ ');
            
            
            $rg = [
                'o' =>'0',
                'O' => '0',
                '.' => '',
                '*' => '.*',
                '_' => '[0-9]',
                '?' => '[0-9]',
                
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
                
                $c2['id'] = $sosim;
            }else{
                
                if(preg_match('/\d+/', $sosim, $m) && $m[0] == $sosim){
                    
                    $conditions[] = ['regex' ,'id',  "/$sosim/i"];
                    
                }else{
                    $conditions[]  = ['regex' ,'id',  "/^$sosim$/i"];
                }
            }
            }
            
        }
        
        
        
        $min_price = isset($params['min_price']) ? (float) str_replace(',', '', $min_price) : 0;
        $max_price = isset($params['max_price']) ? (float) str_replace(',', '', $max_price) : 0;
        
        if($min_price>0 && $max_price>0){
            $conditions[]  = ['between', 'price', (float)$min_price, (float)$max_price];
        }elseif ($min_price > 0){
            $conditions[]  = ['between', 'price', (float)$min_price, 100000000000];
        }elseif ($max_price > 0){
            $conditions[]  = ['between', 'price',0, (float)$max_price];
        }
        
        $not_in = isset($params['not_in']) ? $params['not_in'] : [];
        
        if(!is_array($not_in)){
            $not_in = explode(',', $not_in);
        }
        
        if(!empty($not_in)){
            
            $a = [0,1,2,3,4,5,6,7,8,9];
            
            
            $array_diff = array_diff($a, $not_in);
            
            $conditions[]  = ['REGEX' ,'id',   "/^[".implode('', $array_diff)."]+$/i"];
            
            
        }
        
        if(isset($params['is_sold']) && $params['is_sold'] > -1)
        {
            $c2['is_sold'] = (int)$params['is_sold'];
        }
        
        if(isset($params['is_invisible']) && $params['is_invisible'] > -1)
        {
            $c2['is_invisible'] = (int)$params['is_invisible'];
        }
        
        
        $from_date = isset($params['from_date']) && $params['from_date'] != "" ? strtotime(str_replace('/', '-', $params['from_date'])) : 0;
        
        $to_date = isset($params['to_date']) && $params['to_date'] != "" ? strtotime(str_replace('/', '-', $params['to_date'])) : 0;
        
        
        if($from_date + $to_date > 0){
            
            if($to_date < $from_date){
                $to_date = $from_date + 365 * 86400;
            }
            
            $conditions[]  = ['between', 'updated_at',$from_date, $to_date];
        }
        
        
        if(!empty($c2)){
            $conditions[] = $c2;
        }
        
        return $conditions;
        
    }
    
    public function updateSimValue($simId, $field, $value, $params = [])
    {
        if(in_array($field, ['id', '_id'])){
            return;
        }
        
        $con = ['id' => $simId];
        
        if(isset($params['partner_id']) && $params['partner_id'] > 0){
            $con['partner_id'] = (int) $params['partner_id'];
        }
         
        
        $this->getCollection()->update($con, [$field => is_numeric($value) ? (int) $value : $value]);
        $this->getEs()->updateSimValue($simId, $field, $value, $params);
    }
    
    public function dausocu()
    {
        return [
            /*Đầu số 0120 chuyển đổi thành 070
            Đầu số 0121 chuyển đổi thành 079
            Đầu số 0122 chuyển đổi thành 077
            Đầu số 0126 chuyển đổi thành 076
            Đầu số 0128 chuyển đổi thành 078*/
            '120'   =>  '70',
            '121'   =>  '79',
            '122'   =>  '77',
            '126'   =>  '76',
            '128'   =>  '78',
            /*
             * Đầu số 0123 chuyển đổi thành 083
Đầu số 0124 chuyển đổi thành 084
Đầu số 0125 chuyển đổi thành 085
Đầu số 0127 chuyển đổi thành 081
Đầu số 0129 chuyển đổi thành 082
             */
            '123'   =>  '83',
            '124'   =>  '84',
            '125'   =>  '85',
            '127'   =>  '81',
            '129'   =>  '82',
            
            /*
             * Đầu số 0162 chuyển đổi thành 032
Đầu số 0163 chuyển đổi thành 033
Đầu số 0164 chuyển đổi thành 034
Đầu số 0165 chuyển đổi thành 035
Đầu số 0166 chuyển đổi thành 036
Đầu số 0167 chuyển đổi thành 037
Đầu số 0168 chuyển đổi thành 038
Đầu số 0169 chuyển đổi thành 039
             */
            
            '162'   =>  '32',
            '163'   =>  '33',
            '164'   =>  '34',
            '165'   =>  '35',
            '166'   =>  '36',
            '167'   =>  '37',
            '168'   =>  '38',
            '169'   =>  '39',
            /*
             * Đầu số 0186 chuyển đổi thành 056
Đầu số 0188 chuyển đổi thành 058
             */
            '186'   =>  '56',
            '188'   =>  '58',
            /*
             * Đầu số 0199 chuyển đổi thành 059
             */
            '199'   =>  '59',
        ];
    }
    
    public function validateOldNumber($number)
    {
        $number = str_replace(['O','o'], '0', $number);
        $id = ltrim(preg_replace('/\D/', '', $number),'0');
        
        if(in_array(($dau = substr($id, 0,3)), array_keys($this->dausocu() ))){
            return true;
        }
        
        return false;
    }
    
    public function convertOldNumber($number)
    {
        $number = str_replace(['O','o'], '0', $number);
        $id = ltrim(preg_replace('/\D/', '', $number),'0');
        
        if(in_array(($dau = substr($id, 0,3)), array_keys($this->dausocu() ))){
            $dau_moi = $this->dausocu()[$dau];
            $number = makePhoneNumber($dau_moi . substr($id, 3));
        }
        
        return $number;
    }
    
    
    public function icons()
    {
        return [
            '⚜', '❤', '🍀', '💥', '💎', '🔸', '🔹', '🔹', '🔥','🔸', '🔺',
            '🌹',
            '🚕',
            '🏆',
            '🌺',
//             '👉',
//             '👉',
//             '👉',
//             '👉',
//             '👉',
//             '👉',
//             '👉',
//             '👉',
            
        ];
    }
   
    /**
     * Cập nhật hàng loạt
     */
    public function bulkUpdate($params, $conditions)
    {
        $this->getEs()->bulkUpdate($params, $conditions);
        
        $p = $this->buildConditions($conditions);

        
        if(count($p) > 1){

            $this->getCollection()->update($p, $params);
             
        }
        
        
    }
    
    
    
    public function updateDataFromTempdata($limit = 5000)
    {
        $l = (new \yii\db\Query())->from('simonline')->limit($limit)->all();
        
     
        $ex = []; $i = 0;
        if(!empty($l)){
            foreach ($l as $sim){
                $ex[] = $sim['id_prm'];
                unset($sim['id_prm']);
                
                if($sim['price'] < 300000){
                    
                    $simInfo = Yii::$app->sim->getSimInfo($sim['id']);
                    
                    if($simInfo['category_id'] == 0){
                        continue;
                    }
                    
                    if($simInfo['network_id'] > 3){
                        continue;
                    }
                }
                
                if(isset($sim['display'])){
                    if(substr($sim['display'], 0, 1) > 0){
                        $sim['display'] = makePhoneNumber($sim['display']);
                    }
                }

                $this->updateSingleSimData($sim['id'], $sim['partner_id'], $sim);
                
                if($i++ > 500){
                    Yii::$app->db->createCommand()->delete('simonline', ['id_prm' => $ex])->execute();
                    $ex = [];
                    $i = 0;
                }
            }
        }
        
        if(!empty($ex)){
            Yii::$app->db->createCommand()->delete('simonline', ['id_prm' => $ex])->execute();
        }
        return count($ex);
    }
    
    
    public function addPackageToSim($simId, $data, $params = [])
    {
        $field = isset($params['field']) ? $params['field'] : null;
        
        if($field == null) return false;
         
        $con = ['id' => $simId];

        $this->getCollection()->update($con, ['attrs' => [$field =>$data]]);
                         
        $this->getEs()->addPackageToSim($simId, $data, $params);
    }
    
    
    public function getPartnerName($id)
    {
        $identity = Yii::$app->customer->model->getItem($id);
        if(!empty($identity)) return $identity['name'];
    }
    
}