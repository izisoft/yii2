<?php

namespace izi\notify\models;

use Yii;

/**
 * This is the model class for table "notifications".
 *
 * @property integer $id
 * @property integer $sid
 * @property string $title
 * @property string $link
 * @property integer $state
 * @property string $time
 */
class Notify extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notifications}}';
    }
    public static function tableToUser()
    {
    	return '{{%notifications_to_users}}';
    }
    
    public function insertNotification($f = [],$multiple = false){
    	if($multiple && !empty($f)){    		 
    		foreach ($f as $data){
    			$data['sid'] = isset($data['sid']) ? $data['sid'] : __SID__;
    			if($data['title'] != ""){
    				return Yii::$app->db->createCommand()->insert(self::tableName(),$data)->execute();
    			}
    		}
    		 
    	}elseif(!empty($f)){
	    	$f['sid'] = isset($f['sid']) ? $f['sid'] : __SID__;
	    	if($f['title'] != ""){
	    		return Yii::$app->db->createCommand()->insert(self::tableName(),$f)->execute();
	    	}
	    	
    	}
    }
    
    
    public function sentNotify($params){    	 
        $to = isset($params['to']) ? $params['to'] : [];
    	$cc = isset($params['cc']) ? $params['cc'] : [];
    	$bcc = isset($params['bcc']) ? $params['bcc'] : [];
    	
    	$text = isset($params['text']) ? $params['text'] : '';
    	$biz = isset($params['biz']) ? $params['biz'] : [];
    	$sid = isset($params['sid']) ? $params['sid'] : __SID__;
    	$title = isset($params['title']) ? $params['title'] : '';
    	$note = isset($params['note']) ? $params['note'] : '';
    	$link = isset($params['link']) ? $params['link'] : '';
    	$type_id = isset($params['type_id']) ? $params['type_id'] : 1; 
    	$message = [
    			'title'		=>	$title,
    			'type_id'	=>	$type_id,
    			'link'		=>	$link,
    			'note'		=>	$note,
    			'bizrule'	=>	json_encode($biz)
    	];
    	/* 1. Nhân viên
    	 * 2. Khách hàng
    	 * 3. Toàn bộ NV
    	 * 4. Toàn bộ khách hàng
    	 * 5.
    	 * 6.
    	 * 7.
    	 * 8.
    	 */
    	
    	
    	$users = array_merge($to,$cc,$bcc);
    	
    	if(!empty($message)){
    		//1. Thêm thông báo
    		$message['sid'] = $sid;
    		 
    		
    		
    		if(!empty($users)){
    		    $id = Yii::$app->zii->insert($this->tableName(), $message);
        		foreach ($users as $user_id){
        			Yii::$app->db->createCommand()->insert($this->tableToUser(), [
        					'notify_id'	=>	$id,
        					'user_id'	=>	$user_id,
        					'type_id'	=>	$type_id
        			])->execute();
        		}
    		}else{
    		    $message['type_id'] = $message['uid'] = 0;
    		    $id = Yii::$app->zii->insert($this->tableName(), $message);
    		}
    	}
    	
    	
    	
    }
    
}
