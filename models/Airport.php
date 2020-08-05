<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "airport".
 *
 * @property int $id
 * @property string $lang_code
 * @property string $name
 * @property string $title
 * @property string $ICAO
 * @property string $IATA
 * @property string $bizrule
 * @property int $local_id
 * @property int $is_international
 * @property int $is_airport
 */
class Airport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'airport';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lang_code', 'name', 'title', 'ICAO', 'IATA', 'bizrule', 'local_id'], 'required'],
            [['bizrule'], 'string'],
            [['local_id', 'is_international', 'is_airport'], 'integer'],
            [['lang_code'], 'string', 'max' => 64],
            [['name', 'title'], 'string', 'max' => 255],
            [['ICAO', 'IATA'], 'string', 'max' => 16],
            [['ICAO'], 'unique'],
            [['IATA'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lang_code' => 'Lang Code',
            'name' => 'Name',
            'title' => 'Title',
            'ICAO' => 'I C A O',
            'IATA' => 'I A T A',
            'bizrule' => 'Bizrule',
            'local_id' => 'Local ID',
            'is_international' => 'Is International',
            'is_airport' => 'Is Airport',
        ];
    }
    
    public function getItem($id)
    {
        $query = Airport::find()->where(['id' => $id]); 
        return $query->asArray()->one();
    }
    
    public function getItems($params = [])
    {
        $query = Airport::find();
        
        $query->orderBy(['type_id' => SORT_ASC,'region_sort' => SORT_ASC, 'name'=>SORT_ASC]);
        return $query->asArray()->all();
    }
}
