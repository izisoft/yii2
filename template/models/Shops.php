<?php

namespace izi\template\models;

use Yii;

/**
 * This is the model class for table "shops".
 *
 * @property int $id
 * @property string $code
 * @property string $time
 * @property string $bizrule
 * @property int $state
 * @property int $status
 * @property string $type
 * @property int $parent_id
 * @property string $to_date
 * @property string $last_modify
 * @property string $checksum
 * @property int $creator_id
 * @property int $reseller_level
 * @property int $package_id
 * @property string $layout
 * @property string $api_token
 *
 * @property AppsToShop[] $appsToShops
 * @property Apps[] $apps
 * @property AuthAssignment[] $authAssignments
 * @property CtemplateToShop[] $ctemplateToShops
 * @property CustomPage[] $customPages
 * @property DomainPointer[] $domainPointers
 * @property EmailsSubscribes[] $emailsSubscribes
 * @property Foods[] $foods
 * @property GoodsFields[] $goodsFields
 * @property GoodsGroups[] $goodsGroups
 * @property GuideLanguage[] $guideLanguages
 * @property ProductAttrs[] $productAttrs
 * @property ProductUnitExchange[] $productUnitExchanges
 * @property Satellite[] $satellites
 * @property Satellite[] $satellites0
 * @property Shops[] $items
 * @property Shops[] $partners
 * @property ShopToGroups[] $shopToGroups
 * @property ShopsConnected[] $shopsConnecteds
 * @property ShopsToPackages[] $shopsToPackages
 * @property ShopsPackages[] $packages
 * @property Slugs[] $slugs
 * @property TempToShop[] $tempToShops
 * @property Templates[] $temps
 * @property UserExchangeRate[] $userExchangeRates
 * @property UserGroups[] $userGroups
 * @property UserTextTranslate[] $userTextTranslates
 * @property UserToShop[] $userToShops
 * @property Users[] $users
 */
class Shops extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shops';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'checksum', 'layout', 'api_token'], 'required'],
            [['time', 'to_date', 'last_modify'], 'safe'],
            [['bizrule'], 'string'],
            [['state', 'status', 'parent_id', 'creator_id', 'reseller_level', 'package_id'], 'integer'],
            [['code'], 'string', 'max' => 32],
            [['type', 'layout'], 'string', 'max' => 16],
            [['checksum'], 'string', 'max' => 128],
            [['api_token'], 'string', 'max' => 64],
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
            'time' => 'Time',
            'bizrule' => 'Bizrule',
            'state' => 'State',
            'status' => 'Status',
            'type' => 'Type',
            'parent_id' => 'Parent ID',
            'to_date' => 'To Date',
            'last_modify' => 'Last Modify',
            'checksum' => 'Checksum',
            'creator_id' => 'Creator ID',
            'reseller_level' => 'Reseller Level',
            'package_id' => 'Package ID',
            'layout' => 'Layout',
            'api_token' => 'Api Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAppsToShops()
    {
        return $this->hasMany(AppsToShop::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApps()
    {
        return $this->hasMany(Apps::className(), ['id' => 'app_id'])->viaTable('apps_to_shop', ['shop_id' => 'id']);
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
    public function getCtemplateToShops()
    {
        return $this->hasMany(CtemplateToShop::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomPages()
    {
        return $this->hasMany(CustomPage::className(), ['sid' => 'id']);
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
    public function getEmailsSubscribes()
    {
        return $this->hasMany(EmailsSubscribes::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFoods()
    {
        return $this->hasMany(Foods::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsFields()
    {
        return $this->hasMany(GoodsFields::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsGroups()
    {
        return $this->hasMany(GoodsGroups::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuideLanguages()
    {
        return $this->hasMany(GuideLanguage::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttrs()
    {
        return $this->hasMany(ProductAttrs::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductUnitExchanges()
    {
        return $this->hasMany(ProductUnitExchange::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSatellites()
    {
        return $this->hasMany(Satellite::className(), ['partner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSatellites0()
    {
        return $this->hasMany(Satellite::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Shops::className(), ['id' => 'item_id'])->viaTable('satellite', ['partner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartners()
    {
        return $this->hasMany(Shops::className(), ['id' => 'partner_id'])->viaTable('satellite', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopToGroups()
    {
        return $this->hasMany(ShopToGroups::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopsConnecteds()
    {
        return $this->hasMany(ShopsConnected::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopsToPackages()
    {
        return $this->hasMany(ShopsToPackages::className(), ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(ShopsPackages::className(), ['id' => 'package_id'])->viaTable('shops_to_packages', ['shop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSlugs()
    {
        return $this->hasMany(Slugs::className(), ['sid' => 'id']);
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
        return $this->hasMany(Templates::className(), ['id' => 'temp_id'])->viaTable('temp_to_shop', ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserExchangeRates()
    {
        return $this->hasMany(UserExchangeRate::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroups::className(), ['sid' => 'id']);
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
    public function getUserToShops()
    {
        return $this->hasMany(UserToShop::className(), ['sid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['id' => 'user_id'])->viaTable('user_to_shop', ['sid' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ShopsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopsQuery(get_called_class());
    }
}
