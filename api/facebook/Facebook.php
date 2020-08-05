<?php
namespace izi\api\facebook;
use Yii;
class Facebook extends \yii\base\Component
{
    
    public function login($params){
        $access_token = $params['access_token'];
        $fields = ['id','email','first_name','last_name','age_range','link','gender','locale','picture','timezone','updated_time','verified'];
        // Get user infomation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?fields=".implode(',',$fields)."&access_token=$access_token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        $user = json_decode($response,1);
        
        if(isset($params['type_id']) && $params['type_id']>0){
            switch ($params['type_id']) {
                case TYPE_ID_COLLABORATOR:
                    $model = Yii::$app->collaborator;
                break;
                
                default:
                    $model = Yii::$app->member;
                break;
            }
        }else{
            $model = Yii::$app->member;
        }
        
        if(isset($user['email']) && validateEmail($user['email'])){
            $mem = $model->model->findByUsername2($user['email']);
            if(!empty($mem)){
                Yii::$app->member->login($mem);
                return $mem;
            }else{
                $mem = $this->register($params);
                $model->login($mem);
                return $mem;
            }
        }
        return null;
    }
    
    public function register($params){
        
        $access_token = $params['access_token'];
        $fields = ['id','email','first_name','last_name','age_range','link','gender','locale','picture','timezone','updated_time','verified'];
        // Get user infomation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?fields=".implode(',',$fields)."&access_token=$access_token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        $user = json_decode($response,1);
        
        if(isset($params['type_id']) && $params['type_id']>0){
            switch ($params['type_id']) {
                case TYPE_ID_COLLABORATOR:
                    $model = Yii::$app->collaborator;
                    break;
                    
                default:
                    $model = Yii::$app->member;
                    break;
            }
        }else{
            $model = Yii::$app->member;
        }
        
        if(isset($user['email']) && validateEmail($user['email'])){
            //
            $mem = $model->model->findByUsername2($user['email']);
            if(!empty($mem)){
                
                $model->login($mem);
                return $mem;
            }else{
                $mem = $model->model->registerFromFacebook($user);
                $model->login($mem);
                return $mem;
            }
            
        }
        
        return null;
    }
}