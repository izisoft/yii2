<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "site_configs".
 *
 * @property string $code
 * @property string $bizrule
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['bizrule', 'json_data'], 'string'],
            [['sid', 'last_modify'], 'integer'],
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
            'bizrule' => 'Bizrule',
            'sid' => 'Sid',
            'lang' => 'Lang',
            'json_data' => 'Json Data',
            'last_modify' => 'Last Modify',
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
            
        ];
        
        if($cached && !empty($cache = Yii::$app->icache->getCache($params))){
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
        
        $item = static::find()->select(['bizrule'])->where($conditions)->asArray()->one();
        
        return self::populateData($item);
        
        if(!empty($item)) {
            
            //$item = $item->toArray() ;
            
            if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                return $content;
                unset($item['bizrule']);
            }
            return $item;
        }
        
        //return $item;
        
        
    }
    
    
    public static function updateData($data, $conditions, $replace = false){
        if(!isset($conditions['code'])){
            return;
        }
        
        $item = !$replace ? self::getConfigs($conditions['code'] , isset($conditions['lang']) ? $conditions['lang'] : null, __SID__, false) : [];
        
        $config = static::findOne($conditions);
        
        $add_new = false;
        
        if(empty($config)){
            $config = new SiteConfigs();
            $add_new = true;
            
        }
        
        $new_db = isset($config->bizrule);
        
        foreach ($conditions as $key=>$value) {
            //             $con[$key] = $value;
            $config->{$key}= $value;
        }
        
        if(is_array($data)){
            $config->bizrule = $config->json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        
        
        if(!empty($item)){
            
            if(!empty($data)){
                foreach ($data as $key=>$value){
                    $item[$key] = $value;
                }
            }
            
            $config->bizrule = json_encode($item, JSON_UNESCAPED_UNICODE);
            
        }
        
        
        if($new_db){
            return $config->save();
        }
        
        if($add_new){
            
            return Yii::$app->db->createCommand()->insert(SiteConfigs::tableName(), [
                'bizrule'=>$config->bizrule,
                'json_data'=>$config->json_data
            ] + $conditions)->execute();
            
        }
        
        return Yii::$app->db->createCommand()->update(SiteConfigs::tableName(), [
            'bizrule'=>$config->bizrule,
            'json_data'=>$config->json_data
        ], $conditions)->execute();
        
        
    }
    
}
