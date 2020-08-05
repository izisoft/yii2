<?php

namespace izi\sim;

use Yii;

/**
 * This is the model class for table "simonline_Package".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $partner_id
 * @property string $json_data
 * @property int $updated_time
 */
class Package extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'simonline_package';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'title', 'json_data'], 'required'],
            [['partner_id', 'updated_time'], 'integer'],
            [['json_data'], 'string'],
            [['name', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'title' => 'Title',
            'partner_id' => 'Partner ID',
            'json_data' => 'Json Data',
            'updated_time' => 'Updated Time',
        ];
    }
    
    public function getList($params = [])
    {
        $partner_id = -1;
        if(is_numeric($params) && $params>-1){
            $partner_id = $params;
        }
        
        if(isset($params['partner_id']))
        {
            $partner_id = $params['partner_id'];
        }
        
        
        $con = [
            
            'sid' => __SID__,
        ];
        if($partner_id != -1){
            $con['partner_id'] = $partner_id;
        }
        
        
        if(isset($params['is_invisible']) && $params['is_invisible'] > -1){
            $con['is_invisible'] = $params['is_invisible'];
        }
        
        
        if(isset($params['return']) && $params['return'] == 'full'){
            
        }
        
        $items = Package::find()->where($con)->orderBy(['updated_time'=>SORT_DESC])->asArray()->all();
        
        if(!empty($items)){
            foreach($items as $k => $item){
                if(isset($item['json_data']) && $item['json_data'] != ""){
                    $item += json_decode($item['json_data'],1);
                    unset($item['json_data']);
                    
                    $items[$k] = $item;
                }
            }
        }
        
        
        
        return $items;
    }
    
    public function getItem($id)
    {
        $item = Package::find()->where(['id' => $id])->asArray()->one();
        
        if(!empty($item)){
            if(isset($item['json_data']) && $item['json_data'] != ""){
                $item += json_decode($item['json_data'],1);
                unset($item['json_data']);
            }
        }
        
        return $item;
    }
    
    public function getItemIdFromName($name)
    {
        $item = Package::find()->where(['name' => strtolower($name)])->asArray()->one();
        
        //view( Package::find()->where(['name' => strtolower($name)]) -> createCommand()->getRawSql());
        
        if(!empty($item)){
            return $item['id'];
        }
        
        return 0;
    }
    
    public function createList($data)
    {
        $data['updated_time'] = time();
        $data['sid'] = __SID__;
        Yii::$app->db->createCommand()->insert(Package::tableName(), $data)->execute();
        return Yii::$app->db->lastInsertID;
    }
    
}
