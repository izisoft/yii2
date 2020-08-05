<?php
namespace izi\menu;
use Yii;

class NestedSet extends \yii\base\Component
{
    var $tableName = '{{%nestedsets}}';
    var $position = 999;
    var $parent_id = 0;
    var $sid = __SID__;
    var $lang = __LANG__;
    
    /**
     * Add the root node to an empty table.
     *
     * @return    mixed  The id of the new root node or false on error.
     */
    
    private function getDb(){
        return new \yii\db\Query();
    }
    
    public function addRoot($params)
    {
        $params['lft'] = 0 ;
        $params['rgt'] = 1 ;
        $params['parent_id'] = 0 ;
        $params['sid'] = __SID__;
        if((new \yii\db\Query())->from($this->tableName)->where(['sid'=>__SID__])->count(1) == 0){
            Yii::$app->db->createCommand()->insert($this->tableName, $params)->execute();
            return (new \yii\db\Query())->from($this->tableName)->max('id');
        }
        return false;
    }
    
    private function _insertNode($params){
        
        Yii::$app->db->createCommand()->update($this->tableName,[
            'lft'=>new \yii\db\Expression('`lft`+2'),
        ],['and',
            $this->setCondition(),
        [
            '>=', 'lft', $params['lft']
        ]])->execute();
        Yii::$app->db->createCommand()->update($this->tableName,[
            'rgt'=>new \yii\db\Expression('`rgt`+2'),            
        ],['and',$this->setCondition()
            ,[
            '>=', 'rgt', $params['lft']
        ]])->execute();
                
        
        Yii::$app->db->createCommand()->insert($this->tableName, $params)->execute();
        return (new \yii\db\Query())->from($this->tableName)->max('id');
    }
    
    public function setPosition($params){
        if($this->position !== false){
        if(!isset($params['position'])){
            $params['position'] = $this->position;
        }else{
            $this->position = $params['position'];
        }}elseif(isset($params['position'])){
            unset($params['position']);
        }
        return $params;
    }
    
    public function setLang($params){
        if($this->lang !== false){
            if(!isset($params['lang'])){
                $params['lang'] = $this->lang;
            }else{
                $this->lang = $params['lang'];
            }}elseif(isset($params['lang'])){
                unset($params['lang']);
            }
            return $params;
    }
    
    public function setParentId($params){
        
        if($this->parent_id !== false){
        
            if(!isset($params['parent_id'])){
                $params['parent_id'] = $this->parent_id;
            }else{
                $this->parent_id = $params['parent_id'];
            }
        }elseif(isset($params['parent_id'])){
            unset($params['parent_id']);
        }
        return $params;
    }
    
    public function setSid($params){
        
        if($this->sid === false){
            if(isset($params['sid'])){
        
            unset($params['sid']);
            return $params;
            }
            return $params;
        }
        
        if(!isset($params['sid'])){
            $params['sid'] = $this->sid;
        }else{
            $this->sid = $params['sid'];
        }
        return $params;
    }
    
    public function setParams($params){
        if($this->sid>0){
            $params = $this->setSid($params);
        }
        if($this->position !== false){
            $params = $this->setPosition($params);
        }
        if($this->parent_id !== false){
            $params = $this->setParentId($params);
        }
        
        if($this->lang !== false){
            $params = $this->setLang($params);
        }
        //$params['parent_id'] = $this->setParentId($params);
        return $params;
    }
    
    public function setCondition($params = [], $param2 = []){
        if($this->sid>0){
            $params = $this->setSid($params);
        }
        
        if($this->lang !== false){
            $params = $this->setLang($params);
        }
        //$params['parent_id'] = $this->setParentId($params);
        return $params + $param2;
    }
    
    public function getRoot(){
        $node = $this->getDb()->from($this->tableName)->where($this->setCondition([]) + ['lft'=>0])->one();
        //
        if(!!empty($node)){
            
        }
        return $node;
    }
    
    public function getMaxRgt(){
        $rgt = (new \yii\db\Query())->from($this->tableName)->where($this->setCondition([]))->max('rgt');
        return $rgt > 0 ? $rgt : -1;
    }
    
    public function getNode($id){
        return $this->getDb()->from($this->tableName)->where($this->setCondition([]) + ['id'=>$id])->one();
    }
    
    
    public function getNodes($params){
        //$params['sid'] = $this->setSid($params);
        return $this->getDb()->from($this->tableName)->where($this->setCondition($params))
        ->orderBy(['position'=>SORT_ASC, 'title'=>SORT_ASC])
        ->all();
    }
    
    public function getNodeInfo($childs, $node){
        
        if(!isset($node['id'])){
            $node['id'] = 0;
        }
        
        if(!empty($childs) && array_search($node['id'], array_column($childs, 'id')) === false){
            $childs[] = $node;
        }elseif(!!empty($childs)){
            $childs[] = $node;
        }
        
        $array = (array_sort($childs,'position'));
        
        foreach ($array as $a){
            if(!isset($array2[$a['position']])){
                $array2[$a['position']] = [];
            }
            $array2[$a['position']][] = $a;
        }
        
        $rs = [];
        
        foreach ($array2 as $position => $array){
            $array = array_sort($array, 'title');
            foreach ($array as $a){
                $rs[] = $a;
            }
            
        }
        //view($rs);
        
        $key = array_search($node['id'], array_column($rs, 'id'));
        //view($rs);
        return [
            'index' => $key,
            'leftNode'=> $key > 0 ? $rs[$key-1] : [],
            'rightNode'=> $key < count($rs)-1 ? $rs[$key+1] : []
        ];
    }
    
    public function updateNodePosition($id, $parent_id = -1){
        $node = $this->getNode($id);
         
        Yii::$app->db->createCommand()->update($this->tableName, ['access'=>1],[
            'and',$this->setCondition([]),['>=','lft',$node['lft']],['<=','rgt',$node['rgt']]
        ])->execute();
        
        $count = $node['rgt'] - $node['lft'];
        
        $parentNode = $parent_id == -1 ? $this->getNode($node['parent_id']) : $this->getNode($parent_id);
        
        $parent_id = !empty($parentNode) ? $parentNode['id'] : 0;
        
        $childs = $this->getNodes(['parent_id'=>!empty($parentNode) ? $parentNode['id'] : 0]);
        
        // Xác định left + right node
        
        
        
        $nodes = $this->getNodeInfo($childs,$node);
        if(!empty($nodes['leftNode'])){
            $lft = $nodes['leftNode']['rgt'] + 1;
        }elseif (!empty($nodes['rightNode'])){
            $lft = $nodes['rightNode']['lft'];
        }elseif(!empty($parentNode)){
            $lft = $parentNode['lft'] + 1;
        }
        $rgt = $lft + $count;
        
        
        
        
        if($node['lft'] == $lft && $node['rgt'] == $rgt){
            Yii::$app->db->createCommand()->update($this->tableName, ['access'=>0],[
                'and',$this->setCondition(),['>=','lft',$node['lft']],['<=','rgt',$node['rgt']]
            ])->execute();
            return false;
        }
        
        // Nếu nut cha có chỉ số nhỏ hơn
        if(!empty($parentNode)){
            if($node['parent_id'] == $parentNode['id'] || $parentNode['rgt'] < $node['lft']){
                Yii::$app->db->createCommand()->update($this->tableName,[
                    'lft'=>new \yii\db\Expression('`lft`+' . ($count+1)),
                    //'rgt'=>new \yii\db\Expression('`rgt`+'. ($count+1)),
                ],['and',$this->setCondition(['access'=>0]),
                    ['>=', 'lft', $lft ],
                    ['<=', 'lft', $node['lft'] ],
                ])->execute();
                
                Yii::$app->db->createCommand()->update($this->tableName,[
                    //'lft'=>new \yii\db\Expression('`lft`+' . ($count+1)),
                    'rgt'=>new \yii\db\Expression('`rgt`+'. ($count+1)),
                ],['and',
                    $this->setCondition(['access'=>0]),
                    ['>=', 'rgt', $lft ],
                    ['<=', 'rgt', $node['lft'] ],
                ])->execute();
                
                
            }else{
                
                Yii::$app->db->createCommand()->update($this->tableName,[
                    'lft'=>new \yii\db\Expression('`lft`-' . ($count+1)),
                    //'rgt'=>new \yii\db\Expression('`rgt`+'. ($count+1)),
                ],['and',
                    $this->setCondition(['access'=>0]),
                    ['>=', 'lft', $node['lft'] ],
                    ['<', 'lft', $lft],
                ])->execute();
                
                Yii::$app->db->createCommand()->update($this->tableName,[
                    //'lft'=>new \yii\db\Expression('`lft`+' . ($count+1)),
                    'rgt'=>new \yii\db\Expression('`rgt`-'. ($count+1)),
                ],['and',
                    $this->setCondition(['access'=>0]),
                    ['>=', 'rgt', $node['lft'] ],
                    ['<', 'rgt', $lft ],
                ])->execute();
                
                $lft -= $count+1;
                $rgt -= $count+1;
            }
            
            
        }
        
        //if($parent_id != $node['parent_id']){
            Yii::$app->db->createCommand()->update($this->tableName,[
                'parent_id'=>$parent_id,
                'lft'=>$lft,
                'rgt'=>$rgt,
                'level'=>!empty($parentNode) ? $parentNode['level'] + 1 : 0,
            ],[
                'id'=>$node['id']
            ])->execute();
        //}
        
        
        
        
        
        
        
        
        
            Yii::$app->db->createCommand()->update($this->tableName, ['access'=>0],$this->setCondition(['access'=>1]))->execute();
         
        
        
    }
    
    public function addNode($params){
        $params = $this->setParams($params);
        $parentNode = $this->getNode($params['parent_id']);
        $id = 0;
        if(!empty($parentNode)){
            $childs = $this->getNodes(['parent_id'=>$parentNode['id']]);
            // Lấy danh sách con
            if(!empty($childs)){
                
                //$childs[] = $params + ['id'=>0];
                $nodes = $this->getNodeInfo($childs, $params + ['id'=>0]);
                
                if(!empty($nodes['leftNode'])){
                    $params['lft'] = $nodes['leftNode']['rgt']+1;
                    
                }else{
                    $params['lft'] = $parentNode['lft'] + 1;
                }
                $params['rgt'] = $params['lft'] + 1;
                $params['level'] = $parentNode['level'] + 1;
                $id = $this->_insertNode($params);
                
            }else{
                $params['lft'] = $parentNode['rgt'];
                $params['rgt'] = $params['lft'] + 1;
                $params['level'] = $parentNode['level'] + 1;
                $id = $this->_insertNode($params);
            }
            
        }else{
            $params['lft'] = $this->getMaxRgt() + 1;
            $params['rgt'] = $params['lft'] + 1;
            $id = $this->_insertNode($params);
        }
        
        return $id;
    }
    
    
    private $lft = 0, $rgt = 0,$level=0, $existedLft = []; 
    
    public function resetNodeLftRecursive($id, $level = 0){
        
        if($id>0 && $level == 0){
            $node = $this->getNode($id);
            $level = $node['level']+1;
            $this->lft = $node['lft'];
            $this->existedLft[] = $this->lft;
        }
        
        $nodes = $this->getNodes(['parent_id'=>$id]);
        
        if(!empty($nodes)){
            foreach ($nodes as $node){
                while(in_array($this->lft, $this->existedLft)){
                    $this->lft ++;
                }
                $this->existedLft[] = $this->lft;
                if($node['parent_id'] == 0){
                    $level = 0;
                }
                
                $lft = $lft1 = $this->lft++;
                 
                $child = $this->countAllChildRecursive($node['id']);
                
                if($child == 0){
                    $rgt = $lft + 1;
                }else{
                    $rgt = $lft + $child * 2 +1;
                }
                $this->existedLft[] = $rgt;
                 
                $node['level'] = $level;
                
                Yii::$app->db->createCommand()->update($this->tableName, ['lft'=>$lft, 'rgt'=>$rgt, 'level'=>$level],
                    ['id'=>$node['id']])->execute();
                
                if($child == 0){
                    //$this->lft ++ ;
                }
                
                $this->resetNodeLftRecursive($node['id'], $node['level']+1);
            }
        }elseif($id>0){
            
        }
    }
    
    //private $count = 0;
    public function countAllChildRecursive($id, $count = 0){
        $nodes = $this->getDb()->select('id')->from($this->tableName)->where($this->setCondition(['parent_id'=>$id]))->all();
        if(!empty($nodes)){
            $count += count($nodes);
            foreach ($nodes as $node){
                $count = $this->countAllChildRecursive($node['id'],$count);
            }
        }
        return $count;
    }
    
    
    
    
    
    public function removeNode($node){
        if(!is_array($node)){
            $node = $this->getNode($node);
        }
        
        if(!empty($node)){
            $count = $node['rgt'] - $node[''] + 1;
            
            Yii::$app->db->createCommand()->delete($this->tableName,[
                'and',$this->setCondition(),
                [
                    '>=','lft',$node['lft'],
                    '<=','rgt',$node['rgt']
                ]
            ])->execute();
            
            Yii::$app->db->createCommand()->update($this->tableName,[
                'lft'=>new \yii\db\Expression('`lft`-' . ($count)),
                //
            ],['and',
                 
                $this->setCondition(['access'=>0]),
                ['>=', 'lft', $node['lft'] ],
               // ['<', 'lft', $lft],
            ])->execute();
            
            Yii::$app->db->createCommand()->update($this->tableName,[
                //'lft'=>new \yii\db\Expression('`lft`+' . ($count+1)),
                'rgt'=>new \yii\db\Expression('`rgt`-'. ($count)),
            ],['and',
                $this->setCondition(['access'=>0]),
                ['>=', 'rgt', $node['lft'] ],
                //['<', 'rgt', $lft ],
            ])->execute();
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}