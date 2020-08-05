<?php 
namespace izi\statics;

use Yii;

class Statics extends \yii\base\Component
{
    
    private $_visited;
    
    public function getVisited(){
        if($this->_visited == null){
            $this->_visited = Yii::createObject([
                'class' => 'izi\\statics\\Visited',
                'statics'   =>  $this,
            ]);
        }
        
        return $this->_visited;
    }
}