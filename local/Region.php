<?php 
namespace izi\local;
use Yii;
class Region extends \yii\base\Component
{
    public $local, $place;
    
    
//     public function migration()
//     {
//         (new \izi\migration\Region())->up();
//     }
    
    public function addLocal($region_id, $local)
    {
        if(!is_array($local)){
            $local = [$local];
        }
        
        Yii::$app->db->createCommand()->delete('local_to_region', ['region_id'=>$region_id])->execute();
        
        if(!empty($local)){
            foreach ($local as $local_id){
                
                Yii::$app->db->createCommand()->insert('local_to_region', ['region_id'=>$region_id, 'local_id'=>$local_id])->execute();
            }
        }
        
    }
    
    public function findLocal($region_id, $params = [])
    {
 
        $query = (new \yii\db\Query())->from(['a'=>'local'])
        ->innerJoin(['b'=>'local_to_region'], 'a.id=b.local_id')
        ->where(['b.region_id'=>$region_id])
        ;
        
        if(isset($params['return_column'])){
            return $query->select(['a.' . $params['return_column']])->column();
        }
        
        return $query->select(['a.*'])->all();
    }
    
    public function countLocal($region_id, $params = [])
    {
        
        $query = (new \yii\db\Query())->from(['a'=>'local'])
        ->innerJoin(['b'=>'local_to_region'], 'a.id=b.local_id')
        ->where(['b.region_id'=>$region_id])
        ;
        
      
        
        return $query->count(1);
    }
    
    
    public function getItems($params = [])
    {
        
        
        $query = (new \yii\db\Query())->from(['a'=>'regions'])
         
        ->where(['a.local_id'=>234])
        ;
        
        if(isset($params['parent_id'])){
            $query->andWhere(['a.parent_id' => $params['parent_id']]);
        }
        
        return $query->select(['a.*'])->all();
    }
    
    public function getItem($id)
    {
        $query = (new \yii\db\Query())->from(['a'=>'regions'])
        
        ->where(['a.id'=>$id])
        ;
        return $query->one();
         
    }
    
    public function getRegionName($id)
    {
        $query = (new \yii\db\Query())->from(['a'=>'regions'])
        
        ->where(['a.id'=>$id])
        ;
        $item = $query->one();
        if(!empty($item)){
            return $item['name'];
        }
    }
    
}