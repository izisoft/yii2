<?php
/**
 *
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\vote\models;
use Yii;
class Rating extends \yii\db\ActiveRecord
{
    
    public $minScore = 0, $maxScore = 0, $totalScore = 0, $totalVote = 0, $avgScore = 0;
    
    public $rating;
    
    public static function tableName(){
        return '{{%ratings}}';
    }
    
    public static function tableStatistic(){
        return '{{%ratings_statistics}}';
    }
    
    public function getRating($o = []){
        
         
        
        if(is_numeric($o)){
            $item_id = $o;
            $type = 'article';
        }else {
            $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
            $type = isset($o['type']) ? $o['type'] : 'article';
            
        }
        
        if(isset($this->rating[$item_id])){
            return $this->rating[$item_id];
        }
        
        $condition = [
            'sid'=>__SID__,
            'type'=>$type,
            'item_id'=>$item_id,
        ];
        $l = (new \yii\db\Query())->from(Rating::tableStatistic())
        ->where($condition)->orderBy(['score'=>SORT_ASC])->all();
        if(!empty($l)){
            foreach ($l as $k=>$v){
                if($k == 0 ){
                    // Min score
                    $this->minScore = $v['score'];
                }
                if($k == count($l)-1 ){
                    // Max score
                    $this->maxScore = $v['score'];
                }
                $this->totalScore += $v['total_score'] * $v['score'];
                $this->totalVote += $v['total_score'];
            }
            $this->avgScore = round($this->totalScore/$this->totalVote,1);
        }
        
        $this->rating[$item_id] = [
            'min'=>$this->minScore,
            'max'=>$this->maxScore,
            'avg'=>$this->avgScore,
            'total'=>$this->totalVote,
        ];
        
        
        return $this->rating[$item_id];
    }
}