<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "template_category".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $title
 * @property int $state
 * @property int $is_active
 * @property int $is_hidden
 *
 * @property CustomerTypeToTemplete[] $customerTypeToTempletes
 * @property FormToTemps[] $formToTemps
 * @property TempToModules[] $tempToModules
 * @property AdminMenu[] $modules
 */
class TemplateCategory extends \yii\db\ActiveRecord
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
            [['code', 'name'], 'required'],
            [['state', 'is_active', 'is_hidden'], 'integer'],
            [['code'], 'string', 'max' => 32],
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
            'code' => 'Code',
            'name' => 'Name',
            'title' => 'Title',
            'state' => 'State',
            'is_active' => 'Is Active',
            'is_hidden' => 'Is Hidden',
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
}
