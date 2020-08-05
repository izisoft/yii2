<?php 
namespace izi\ogp;

use Yii;

class Ogp extends \yii\base\Component
{
    
    private $_csm;
    
    public function getCsm(){
        if($this->_csm == null){
            $this->_csm = Yii::createObject('izi\ogp\csm\OpenGraph');
        }
        return $this->_csm;
    }
}