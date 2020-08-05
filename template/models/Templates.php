<?php

namespace izi\template\models;

use Yii;

/**
 * This is the model class for table "templates".
 *
 * @property int $id
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
 * @property string $bizrule
 *
 * @property ItemToTemplate[] $itemToTemplates
 * @property Articles[] $items
 * @property TempToShop[] $tempToShops
 * @property Shops[] $s
 */
class Templates extends \izi\db\ActiveRecord
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
            [['name', 'title'], 'required'],
            [['parent_id', 'state', 'is_active', 'is_mobile', 'is_extension', 'is_invisible'], 'integer'],
            [['bizrule'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['ref_code', 'layout'], 'string', 'max' => 16],
            [['title'], 'string', 'max' => 255],
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
            'bizrule' => 'Bizrule',
        ];
    }
	
	public function getAllCategory()
	{  
	    return TemplateCategory::find()->asArray()->all();
	}
    
	public function getAllBlock(){
        $query = static::find()->from('ctemplate')->where(['is_active'=>1]);
        
        return $query->orderBy(['type_code'=>SORT_ASC,'name'=>SORT_ASC])->asArray()->all();
    }
	
	public function getCtemplate($id)
	{
		return static::find()->from('ctemplate')->where(['name'=>$id])->asArray()->one();
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

    
    /**
     * {@inheritdoc}
     * @return TemplatesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TemplatesQuery(get_called_class());
    }
    
    public static function findMyTemplate()
    {
        return static::find()->where(['id'=>(new \yii\db\Query())->from(TempToShop::tableName())->select('temp_id')->where(['sid'=>__SID__])])->all();
    }
    
}
