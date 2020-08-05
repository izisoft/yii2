<?php

namespace izi\frontend\sim;

use Yii;

/**
 * This is the model class for table "simonline_type".
 *
 * @property int $id
 * @property string $name
 * @property string $info
 *
 * @property Simonline[] $simonlines
 */
class SimonlineTypeModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'simonline_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'info'], 'required'],
            [['info'], 'string'],
            [['name'], 'string', 'max' => 255],
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
            'info' => 'Info',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSimonlines()
    {
        return $this->hasMany(Simonline::className(), ['category_id' => 'id']);
    }
}
