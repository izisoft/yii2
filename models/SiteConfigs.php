<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "site_configs".
 *
 * @property string $code
 * @property int $sid
 * @property string $lang
 * @property string $json_data
 * @property int $last_modify
 *
 * @property Shops $s
 */
class SiteConfigs extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_configs';
    }

    public static $_cache2 = [];
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['json_data'], 'string'],
            [['sid'], 'integer'],
            [['code'], 'string', 'max' => 64],
            [['lang'], 'string', 'max' => 16],
            [['code', 'sid', 'lang'], 'unique', 'targetAttribute' => ['code', 'sid', 'lang']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Code',            
            'sid' => 'Sid',
            'lang' => 'Lang',
            'json_data' => 'Json Data',
            'updated_at' => 'Last Modify',
            'created_at' => 'Created',
        ];
    }

    /**
     * Gets query for [[S]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
    
    ///////
    public static function getConfigs($code = null, $lang = __LANG__,$sid=__SID__,$cached=true, $required = false){
        
        
        
        $langx = $lang == null ? 'all' : $lang;
        $code = $code !== null ? strtoupper($code) : 'SITE_CONFIGS';
        
        $params = [
            __METHOD__,
            $code,
            $lang,
            $sid,
            $cached,
            $required
        ];

        $cache = Yii::$app->icache->getTmpCache($params);
                
        if(!empty($cache)){
            return $cache;
        }
        
        
        $conditions = [
            "code"=>$code
        ];
        
        if($sid> -1){
            $conditions["sid"]  = $sid;
        }
                        
        if($lang != null){
            $conditions["lang"] = $lang;
        }        

        $item = SiteConfigs::find()->select(['json_data'])->where($conditions)->asArray()->one();

        if(!empty($item)){
            $item = json_decode($item['json_data'], true, JSON_UNESCAPED_UNICODE);
             
        }
                               
        return Yii::$app->icache->setTmpCache($params, $item);                
    }
    
    
    public static function updateData($data, $conditions, $replace = false){
        if(!isset($conditions['code'])){
            return;
        }

        if(isset($conditions['sid']) && $conditions['sid'] < 1){
            return;
        }
        
        $item = !$replace ? self::getConfigs($conditions['code'] , isset($conditions['lang']) ? $conditions['lang'] : null, __SID__, false) : [];
        
        $config = static::findOne($conditions);
        
        $add_new = false;
        
        if(empty($config)){
            $config = new SiteConfigs();
            $add_new = true;
            
        }
        
        $new_db = isset($config->json_data);
        
        foreach ($conditions as $key=>$value) {
            //             $con[$key] = $value;
            $config->{$key}= $value;
        }
        
        if(is_array($data)){
            $config->json_data = $config->json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        
        
        if(!empty($item)){
            
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $item[$key] = $value;
                }
            }
            
            $config->json_data = json_encode($item, JSON_UNESCAPED_UNICODE);
            
        }
        
        
        if($new_db){
            return $config->save();
        }
        
        if($add_new){
            
            return Yii::$app->db->createCommand()->insert(SiteConfigs::tableName(), [
                'json_data'=>$config->json_data
            ] + $conditions)->execute();
            
        }
        
        return Yii::$app->db->createCommand()->update(SiteConfigs::tableName(), [
            'json_data'=>$config->json_data
        ], $conditions)->execute();
        
        
    }
    
}
