<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "site_menu".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $lft
 * @property int $rgt
 * @property int $level
 * @property int $access
 * @property int $position
 * @property int $is_active
 * @property string|null $type
 * @property string $route
 * @property int|null $status
 * @property int|null $views
 * @property string $lang
 * @property string $title
 * @property string $short_title
 * @property string $url
 * @property string|null $check_sum
 * @property string|null $bizrule
 * @property int $state
 * @property int $sid
 * @property int $category_type
 * @property int $is_permission
 * @property string $url_link
 * @property int $temp_id
 * @property int $is_invisibled
 * @property int $spc
 * @property int $created_at
 * @property int $updated_at
 * @property string $layout
 * @property int $root_id
 *
 * @property BoxToSiteMenu[] $boxToSiteMenus
 * @property Box[] $items
 * @property CategoryToGoodsGroup[] $categoryToGoodsGroups
 * @property GoodsGroups[] $groups
 * @property CategoryToMenu[] $categoryToMenus
 * @property Menu[] $menus
 * @property FilterToMenu[] $filterToMenus
 * @property Filters[] $filters
 * @property ItemsToCategory[] $itemsToCategories
 * @property Articles[] $items0
 */
class SiteMenu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_menu';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'lft', 'rgt', 'level', 'access', 'position', 'is_active', 'status', 'views', 'state', 'sid', 'category_type', 'is_permission', 'temp_id', 'is_invisibled', 'spc', 'created_at', 'updated_at', 'root_id'], 'integer'],
            [['route', 'title', 'short_title', 'url', 'url_link', 'layout'], 'required'],
            [['bizrule'], 'string'],
            [['type', 'route', 'check_sum', 'layout'], 'string', 'max' => 32],
            [['lang'], 'string', 'max' => 16],
            [['title', 'short_title', 'url', 'url_link'], 'string', 'max' => 255],
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
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'level' => 'Level',
            'access' => 'Access',
            'position' => 'Position',
            'is_active' => 'Is Active',
            'type' => 'Type',
            'route' => 'Route',
            'status' => 'Status',
            'views' => 'Views',
            'lang' => 'Lang',
            'title' => 'Title',
            'short_title' => 'Short Title',
            'url' => 'Url',
            'check_sum' => 'Check Sum',
            'bizrule' => 'Bizrule',
            'state' => 'State',
            'sid' => 'Sid',
            'category_type' => 'Category Type',
            'is_permission' => 'Is Permission',
            'url_link' => 'Url Link',
            'temp_id' => 'Temp ID',
            'is_invisibled' => 'Is Invisibled',
            'spc' => 'Spc',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'layout' => 'Layout',
            'root_id' => 'Root ID',
        ];
    }

    /**
     * Gets query for [[BoxToSiteMenus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoxToSiteMenus()
    {
        return $this->hasMany(BoxToSiteMenu::className(), ['menu_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Box::className(), ['id' => 'item_id'])->viaTable('box_to_site_menu', ['menu_id' => 'id']);
    }

    /**
     * Gets query for [[CategoryToGoodsGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryToGoodsGroups()
    {
        return $this->hasMany(CategoryToGoodsGroup::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Groups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(GoodsGroups::className(), ['id' => 'group_id'])->viaTable('category_to_goods_group', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[CategoryToMenus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryToMenus()
    {
        return $this->hasMany(CategoryToMenu::className(), ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Menus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['id' => 'menu_id'])->viaTable('category_to_menu', ['category_id' => 'id']);
    }

    /**
     * Gets query for [[FilterToMenus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilterToMenus()
    {
        return $this->hasMany(FilterToMenu::className(), ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Filters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(Filters::className(), ['id' => 'filter_id'])->viaTable('filter_to_menu', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ItemsToCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemsToCategories()
    {
        return $this->hasMany(ItemsToCategory::className(), ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Items0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems0()
    {
        return $this->hasMany(Articles::className(), ['id' => 'item_id'])->viaTable('items_to_category', ['category_id' => 'id']);
    }
}
