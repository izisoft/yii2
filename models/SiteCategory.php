<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "site_category".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $sid
 * @property int $lft
 * @property int $rgt
 * @property int $level
 * @property int $sort_order
 * @property int $is_active
 * @property string $type
 * @property string $lang
 * @property string $name
 * @property string $url
 * @property string $url_link
 * @property int $view_count
 * @property string $json_data
 * @property string $created_at
 *
 * @property Shops $s
 */
class SiteCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'sid', 'lft', 'rgt', 'level', 'sort_order', 'is_active', 'view_count'], 'integer'],
            [['json_data'], 'string'],
            [['created_at'], 'safe'],
            [['type'], 'string', 'max' => 16],
            [['lang'], 'string', 'max' => 6],
            [['name', 'url', 'url_link'], 'string', 'max' => 255],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'sid' => 'Sid',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'level' => 'Level',
            'sort_order' => 'Sort Order',
            'is_active' => 'Is Active',
            'type' => 'Type',
            'lang' => 'Lang',
            'name' => 'Name',
            'url' => 'Url',
            'url_link' => 'Url Link',
            'view_count' => 'View Count',
            'json_data' => 'Json Data',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
