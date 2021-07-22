<?php

namespace izi\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "shops".
 *
 * @property int $id
 * @property string $code
 * @property int $created_at
 * @property int $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property DomainPointer[] $domainPointers
 * @property Notes[] $notes
 * @property ServerConfig[] $serverConfigs
 * @property SiteCategory[] $siteCategories
 * @property SiteConfigs[] $siteConfigs
 * @property Slugs[] $slugs
 * @property StoreWebsite[] $storeWebsites
 * @property StoreWebsite[] $storeWebsites0
 * @property TempToShop[] $tempToShops
 * @property Templates[] $temps
 * @property UserTextTranslate[] $userTextTranslates
 * @property Users[] $users
 */
class Shop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shops';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        
        return [
            TimestampBehavior::class,
            [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ]
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDomainPointers()
    {
        return $this->hasMany(DomainPointer::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(Note::className(), ['sid' => 'id']); 
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServerConfigs()
    {
        return $this->hasMany(ServerConfig::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSiteCategories()
    {
        return $this->hasMany(SiteCategory::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSiteConfigs()
    {
        return $this->hasMany(SiteConfig::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSlugs()
    {
        return $this->hasMany(Slug::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreWebsites()
    {
        return $this->hasMany(StoreWebsite::className(), ['sid' => 'id']);
    }
 
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTempToShops()
    {
        return $this->hasMany(TempToShop::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemps()
    {
        return $this->hasMany(Template::className(), ['id' => 'temp_id'])->viaTable('temp_to_shop', ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserTextTranslates()
    {
        return $this->hasMany(UserTextTranslate::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['sid' => 'id']);
    }
}
