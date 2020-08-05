<?php
namespace izi\api\token;
use Yii;

class Token extends \yii\base\Component
{
    public $tableName = '{{%api_tokens}}';
    
    
    
    private function createChecksum($token, $domain){ 
        return md5("${token}@".__SID__."${domain}");
    }
    
    public function validateChecksum($token, $domain, $checksum){
        return ($this->createChecksum($token, $domain) === $checksum);
    }
    
    public function validateToken($token, $domain){
        return ((new \yii\db\Query())->from($this->tableName)->where([
            'token'=>$token,
            'domain'=>$domain
        ])->count(1)>0 ? true : false);
    }
    
    public function removeToken($token){
        return Yii::$app->db->createCommand()->delete($this->tableName, ['token'=>$token,'sid'=>__SID__])->execute();
    }
    
    public function createTokenKey($domain){
        $password = randString();
        $token = sha1(md5($password));
        
        while((new \yii\db\Query())->from($this->tableName)->where(['token'=>$token])->count(1)>0){
            $password = randString();
            $token = sha1(md5($password));
        }
        
        $checksum = $this->createChecksum($token, $domain);
        
        if((new \yii\db\Query())->from($this->tableName)->where(['sid'=>__SID__,'domain'=>$domain])->count(1)>0){
            Yii::$app->db->createCommand()->update($this->tableName, [
                'token'=>$token,
                'time'=>time(),
                'checksum'=>$checksum,
            ],
                ['sid'=>__SID__,'domain'=>$domain]
            )->execute();
        }else{
            Yii::$app->db->createCommand()->insert($this->tableName, [
                'sid'=>__SID__,
                'domain'=>$domain,
                'token'=>$token,
                'time'=>time(),
                'checksum'=>$checksum,
            ])->execute();
        }
        
        return $token;
    }
    
    
    
    
}