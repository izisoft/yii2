<?php
namespace izi\import\qm;
use Yii;

class Model extends \yii\db\ActiveRecord
{
    public static function tableName(){
        return '{{%articles}}';
    }
    
    public function updateStaticFiles(){
        $sid = 7;
        $l = static::find()->from('articles')->where(['and',[
            'like','content','%"\\\\/upload\\\\/%',false
        ],['sid'=>$sid]])->limit(10);
        
        //view($l->createCommand()->getRawSql());
        
        view($l->asArray()->all());
        
        $sql = "UPDATE articles
        SET content = REPLACE(content, '\"/upload/', '\"//static.quangminhvn.com/ecom02/upload/') where sid=$sid and content like '%\\\"\\\\\\\\/upload\\\\\\\\/%'
        ";
        view(Yii::$app->db->createCommand($sql)->execute());
    }
}