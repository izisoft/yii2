<?php

namespace izi\frontend\models;

use Yii;

/**
 * This is the model class for table "menu".
 *
 * @property int $id
 * @property string $title
 * @property string $json_data
 * @property int $sid
 * @property string $lang
 *
 * @property CategoryToMenu[] $categoryToMenus
 * @property SiteMenu[] $categories
 * @property Shops $s
 * @property MenuToLocation[] $menuToLocations
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'sid'], 'required'],
            [['json_data'], 'string'],
            [['sid'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['lang'], 'string', 'max' => 6],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'json_data' => 'Json Data',
            'sid' => 'Sid',
            'lang' => 'Lang',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryToMenus()
    {
        return $this->hasMany(CategoryToMenu::className(), ['menu_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(SiteMenu::className(), ['id' => 'category_id'])
        ->viaTable('category_to_menu', ['menu_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenuToLocations()
    {
        return $this->hasMany(MenuToLocation::className(), ['menu_id' => 'id']);
    }
}
