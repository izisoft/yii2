<?php
namespace izi\web;
use Yii;
class Template extends \yii\base\Component
{
    
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject('izi\models\Template');
        }
        return $this->_model;
    }
    
    
    public function getAllBlock(){
        return $this->getModel()->getAllBlock();
    }
    
    public function showBlockTemplate($temp_id, $data = []){
        $fp = Yii::$app->viewPath . '/partials/page/' . $temp_id . '/index.php';
         
        if(file_exists($fp)){
            echo Yii::$app->renderPartial($fp);
        }
    }
}