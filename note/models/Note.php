<?php
/**
 *
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\note\models;
use Yii;
class Note extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%system_note}}';
    }
    
    public function getAll($params = []){
        
        $limit = isset($params['limit']) ? $params['limit'] : 0;
        
        $p = isset($params['p']) && $params['p']>1 ? $params['p'] : 1;
        
        $offset = ($p - 1) * $limit;
        
        $query = static::find()->where(['sid'=>__SID__]);
        
        $query->offset($offset);
        
        if($limit>0){
            $query->limit($limit);
        }
        
        if(!Yii::$app->user->can([ROOT_USER,DEV_USER])){
            $query->andWhere(['created_by'=>Yii::$app->user->id]);
        }
        
        $l = $query->orderBy(['time'=>SORT_DESC])->asArray()->all();
        
        return $l;
    }
}