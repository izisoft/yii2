<?php 
namespace izi\frontend\models;
use Yii;

class Service extends \yii\db\ActiveRecord
{
     
    public static function tableName()
    {
        return '{{%services}}';
    }
 
    public static function tableServiceRating()
    {
        return '{{%services_ratings}}';
    }
    
    public function getAllServiceRating($params = [])
    {
        $lang = isset($params['lang']) ? $params['lang'] : __LANG__;
        $query = static::find()->where(['a.sid'=>__SID__]);
        
        $query->from(['a'=>Service::tableServiceRating()]);
        
        if(isset($params['not_in'])){
            $query->andWhere(['not in', 'a.id', $params['not_in']]);
        }
        
        if(isset($params['in'])){
            $query->andWhere(['in', 'a.id', $params['in']]);
        }
                         
        if(isset($params['type_id']) && $params['type_id'] != null){
            $query->andWhere(['a.type_id'=>$params['type_id']]);
        }
//         else{
//             $query->andWhere(['a.type_id'=>[0,1]]);
//         }
        
        if(isset($params['filter_text']) && $params['filter_text'] != ""){
            $query->andFilterWhere(['or',
                 
                ['like', 'a.title', $params['filter_text']]
            ]);
        }
        
        $query->leftJoin(['b' => 'user_text_translate'], 'a.lang_code = b.lang_code and b.lang=\''.$lang.'\'');
        
        $query->select(['a.*', 'name'=>'b.value' ]);
        
        return \izi\db\ActiveRecord::populateData($query->orderBy(['a.position'=>SORT_ASC, 'name'=>SORT_ASC])->asArray()->all());
    }
    
    
    public function getServiceRatingItem($item_id, $lang = __LANG__)
    {
        $query = static::find()->from(['a' => Service::tableServiceRating()])->where(['a.id'=>$item_id]);
        $query->leftJoin(['b' => 'user_text_translate'], 'a.title = b.lang_code and b.lang=\''.$lang.'\'');
        
        $query->select(['a.*', 'name'=>'b.value' ]);   
        return $query->asArray()->one();
    }
    
    
    public function getItem($item_id, $lang = __LANG__)
    {
        $query = static::find()->where(['a.sid'=>__SID__, 'a.id'=>$item_id]);
        $query->from(['a'=>Service::tableName()]);
        $query->leftJoin(['b' => 'user_text_translate'], 'a.title = b.lang_code and b.lang=\''.$lang.'\'');
        
        $query->select(['a.*','b.lang_code', 'name'=>'b.value' ]);        
        
        return $query->asArray()->one();
    }
    
    
    
    /**
     *
     * @param  $service
     * @return number|boolean|\yii\db\ActiveRecord|array|NULL
     */
    
    
    public function getItemByName($name, $lang = __LANG__, $params = [])
    {
        $query = static::find()->where(['a.sid'=>__SID__, 'b.value'=>$name]);
        $query->from(['a'=>Service::tableName()]);
        $query->leftJoin(['b' => 'user_text_translate'], 'a.title = b.lang_code and b.lang=\''.$lang.'\'');
        
        $query->select(['a.*', 'name'=>'b.value' ]);
        
        if(isset($params['type_id']) && $params['type_id'] != null){
            $query->andWhere(['a.type_id'=>$params['type_id']]);
        }else{
            $query->andWhere(['a.type_id'=>[0,1]]);
        }
        
        if(isset($params['not_in'])){
            $query->andWhere(['not in', 'a.id', $params['not_in']]);
        }
        
        return $query->asArray()->one();
    }
    
    
    /**     
     * @param  $service
     * @return number|boolean|\yii\db\ActiveRecord|array|NULL
     */
    
    public function getAll($params = [])
    {
        $lang = isset($params['lang']) ? $params['lang'] : __LANG__;
        
        $query = static::find()->where(['a.sid'=>__SID__]);
        
        $query->from(['a'=>Service::tableName()]);
        
        $query->leftJoin(['b' => 'user_text_translate'], 'a.title = b.lang_code and b.lang=\''.$lang.'\'');
        
        $query->select(['a.*', 'name'=>'b.value' ]);
        
        if(isset($params['type_id']) && $params['type_id'] != null){
            $query->andWhere(['a.type_id'=>$params['type_id']]);
        }else{
            //$query->andWhere(['a.type_id'=>[0,1]]);
        }
        
        if(isset($params['filter_text']) && $params['filter_text'] != ""){
            $query->andFilterWhere(['or', 
                ['like', 'b.value', $params['filter_text']],
                ['like', 'a.title', $params['filter_text']]
            ]);
        }
        
//         view($query->createCommand()->getRawSql());
        
        return $query->orderBy(['name'=>SORT_ASC])->asArray()->all();
    }
    
    
    /**
     *
     * @param  $service
     * @return number|boolean|\yii\db\ActiveRecord|array|NULL
     */
    
    public function updateService($service, $service_id)
    {
        $lang = isset($service['lang']) ? $service['lang'] : __LANG__;
        $item = $this->getItemByName($service['name'], $lang, ['not_in' => $service_id]);
        
        if(empty($item)){                         
            
            $title = 'text_service_title_' . $service_id;
            $summary = 'text_service_summary_' . $service_id;
 
            Yii::$app->t->updateLangcode($title,$lang,trim($service['name']));
            Yii::$app->t->dbUpdateUserTextTranslate($title,$lang,trim($service['name']));
            
            if(isset($service['summary']) && $service['summary'] != ""){
                Yii::$app->t->updateLangcode($summary,$lang,trim($service['summary']));
                Yii::$app->t->dbUpdateUserTextTranslate($summary,$lang,trim($service['summary']));
            }
            
            $service['id'] = $service_id;
            $service['state'] = true;
            
            return $service;
            
        }
        
        $item['state'] = false;
        return $item;
    }
    
    
    /**
     * 
     * @param  $service
     * @return number|boolean|\yii\db\ActiveRecord|array|NULL
     */
    
    
    public function addService($service)
    {
        $lang = isset($service['lang']) ? $service['lang'] : __LANG__;
        
        
        if(!isset($service['type_id'])) $service['type_id'] = 0;
        
        $item = $this->getItemByName($service['name'], $lang, ['type_id'=>$service['type_id']]);
        
        if(empty($item)){
            
            Yii::$app->db->createCommand()->insert(Service::tableName(), [
                'sid'=>__SID__,
                'type_id'=>$service['type_id']
            ])->execute();
            
            $id = Yii::$app->db->getLastInsertID();
            
            
            $title = 'text_service_title_' . $id;
            $summary = 'text_service_summary_' . $id;
            
            Yii::$app->db->createCommand()->update(Service::tableName(),[
                'title'=>$title,
                'summary'=>$summary
            ], [
                'sid'=>__SID__,
                'id'=>$id
            ])->execute();
            
            
            
            Yii::$app->t->translate($title, $lang, [
                'insert_null'=>true,
                'default'=>$service['name'],
            ]);
            
            if(isset($service['summary']) && $service['summary'] != ""){
                Yii::$app->t->translate($summary, $lang, [
                    'insert_null'=>true,
                    'default'=>$service['summary'],
                ]);
            }
//             

            $service['id'] = $id;
            $service['state'] = true;
            
            return $service;
            
        }
        
        $item['state'] = false;
        return $item;
    }
    
}

