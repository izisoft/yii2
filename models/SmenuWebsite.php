<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "smenu_website".
 *
 * @property int $option_id
 * @property int $menu_id Menu ID
 * @property int $website_id Website ID
 * @property int $is_active
 * @property int $is_hidden
 * @property int $sort_order
 *
 * @property StoreWebsite $website
 * @property SiteMenu $menu
 */
class SmenuWebsite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'smenu_website';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['menu_id', 'website_id'], 'required'],
            [['menu_id', 'website_id', 'is_active', 'is_hidden', 'sort_order'], 'integer'],
            [['menu_id', 'website_id'], 'unique', 'targetAttribute' => ['menu_id', 'website_id']],
            [['website_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoreWebsite::className(), 'targetAttribute' => ['website_id' => 'website_id']],
            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiteMenu::className(), 'targetAttribute' => ['menu_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'option_id' => 'Option ID',
            'menu_id' => 'Menu ID',
            'website_id' => 'Website ID',
            'is_active' => 'Is Active',
            'is_hidden' => 'Is Hidden',
            'sort_order' => 'Sort Order',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsite()
    {
        return $this->hasOne(StoreWebsite::className(), ['website_id' => 'website_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(SiteMenu::className(), ['id' => 'menu_id']);
    }
}
