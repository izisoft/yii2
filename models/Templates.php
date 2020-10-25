<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "templates".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $ref_code
 * @property string $title
 * @property int $parent_id
 * @property int $state
 * @property int $is_active
 * @property int $is_mobile
 * @property int $is_extension
 * @property string $layout
 * @property int $is_invisible
 * @property int $is_hidden
 * @property string $bizrule
 *
 * @property ItemToTemplate[] $itemToTemplates
 * @property Articles[] $items
 * @property TempToShop[] $tempToShops
 * @property Shops[] $s
 */
class Templates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['parent_id', 'state', 'is_active', 'is_mobile', 'is_extension', 'is_invisible', 'is_hidden'], 'integer'],
            [['bizrule'], 'string'],
            [['code', 'name'], 'string', 'max' => 32],
            [['ref_code', 'layout'], 'string', 'max' => 16],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'ref_code' => 'Ref Code',
            'title' => 'Title',
            'parent_id' => 'Parent ID',
            'state' => 'State',
            'is_active' => 'Is Active',
            'is_mobile' => 'Is Mobile',
            'is_extension' => 'Is Extension',
            'layout' => 'Layout',
            'is_invisible' => 'Is Invisible',
            'is_hidden' => 'Is Hidden',
            'bizrule' => 'Bizrule',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToTemplates()
    {
        return $this->hasMany(ItemToTemplate::className(), ['temp_id' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Articles::className(), ['id' => 'item_id'])->viaTable('item_to_template', ['temp_id' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTempToShops()
    {
        return $this->hasMany(TempToShop::className(), ['temp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasMany(Shops::className(), ['id' => 'sid'])->viaTable('temp_to_shop', ['temp_id' => 'id']);
    }
}
