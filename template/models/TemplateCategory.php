<?php

namespace izi\template\models;

use Yii;

/**
 * This is the model class for table "template_category".
 *
 * @property int $id
 * @property string $title
 * @property int $state
 * @property int $is_active
 * @property int $is_invisible
 *
 * @property CustomerTypeToTemplete[] $customerTypeToTempletes
 * @property FormToTemps[] $formToTemps
 * @property TempToModules[] $tempToModules
 * @property AdminMenu[] $modules
 */
class TemplateCategory extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'template_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['state', 'is_active', 'is_invisible'], 'integer'],
            [['title'], 'string', 'max' => 255],
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
            'state' => 'State',
            'is_active' => 'Is Active',
            'is_invisible' => 'Is Invisible',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerTypeToTempletes()
    {
        return $this->hasMany(CustomerTypeToTemplete::className(), ['temp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFormToTemps()
    {
        return $this->hasMany(FormToTemps::className(), ['temp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTempToModules()
    {
        return $this->hasMany(TempToModules::className(), ['temp_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModules()
    {
        return $this->hasMany(AdminMenu::className(), ['id' => 'module_id'])->viaTable('temp_to_modules', ['temp_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return TemplateCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TemplateCategoryQuery(get_called_class());
    }
}
