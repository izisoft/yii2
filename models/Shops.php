<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "shops".
 *
 * @property int $id
 * @property string $code
 * @property string|null $time
 * @property string|null $bizrule
 * @property int|null $state
 * @property int $status
 * @property string|null $type
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
 * @property AccFunds[] $accFunds
 * @property Activity[] $activities
 * @property Ads[] $ads
 * @property Adverts[] $adverts
 * @property AdvertsCategory[] $advertsCategories
 * @property ApiTokens[] $apiTokens
 * @property AppItemsNote[] $appItemsNotes
 * @property AppsToShop[] $appsToShops
 * @property Apps[] $apps
 * @property Articles[] $articles
 * @property ArticlesPricesList[] $articlesPricesLists
 * @property AuthAssignment[] $authAssignments
 * @property Box[] $boxes
 * @property Branches[] $branches
 * @property Coupons[] $coupons
 * @property CrawData[] $crawDatas
 * @property Cronjobs[] $cronjobs
 * @property CtemplateToShop[] $ctemplateToShops
 * @property CustomPage[] $customPages
 * @property CustomerGroups[] $customerGroups
 * @property DomainPointer[] $domainPointers
 * @property EmailsSubscribes[] $emailsSubscribes
 * @property Filters[] $filters
 * @property Foods[] $foods
 * @property GoodsFields[] $goodsFields
 * @property GoodsGroups[] $goodsGroups
 * @property GuideLanguage[] $guideLanguages
 * @property InventorySource[] $inventorySources
 * @property InventoryStock[] $inventoryStocks
 * @property Menu[] $menus
 * @property Menus[] $menuses
 * @property ProductAttrs[] $productAttrs
 * @property ProductUnitExchange[] $productUnitExchanges
 * @property Promotions[] $promotions
 * @property SatelliteWeb[] $satelliteWebs
 * @property Services[] $services
 * @property ServicesRatings[] $servicesRatings
 * @property ShopToGoodField[] $shopToGoodFields
 * @property GoodsFields[] $items
 * @property ShopToGoodGroup[] $shopToGoodGroups
 * @property GoodsGroups[] $items0
 * @property ShopToGroups[] $shopToGroups
 * @property ShopsConnected[] $shopsConnecteds
 * @property ShopsToPackages[] $shopsToPackages
 * @property ShopsPackages[] $packages
 * @property SiteConfigs[] $siteConfigs
 * @property Slugs[] $slugs
 * @property Smenu[] $smenus
 * @property StoreWebsite[] $storeWebsites
 * @property TempToShop[] $tempToShops
 * @property Templates[] $temps
 * @property TextAttrs[] $textAttrs
 * @property TextInstructions[] $textInstructions
 * @property Tickets[] $tickets
 * @property TourProgramBase[] $tourProgramBases
 * @property TourServices[] $tourServices
 * @property ToursPrograms[] $toursPrograms
 * @property ToursSeries[] $toursSeries
 * @property UserExchangeRate[] $userExchangeRates
 * @property UserGroups[] $userGroups
 * @property UserTextTranslate[] $userTextTranslates
 * @property UserToShop[] $userToShops
 * @property Users[] $users
 * @property VehiclesCategorys[] $vehiclesCategorys
 */
class Shops extends \yii\db\ActiveRecord
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
     * Gets query for [[AccFunds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccFunds()
    {
        return $this->hasMany(AccFunds::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Activities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivities()
    {
        return $this->hasMany(Activity::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Ads]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAds()
    {
        return $this->hasMany(Ads::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Adverts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdverts()
    {
        return $this->hasMany(Adverts::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[AdvertsCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertsCategories()
    {
        return $this->hasMany(AdvertsCategory::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ApiTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApiTokens()
    {
        return $this->hasMany(ApiTokens::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[AppItemsNotes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppItemsNotes()
    {
        return $this->hasMany(AppItemsNote::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[AppsToShops]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppsToShops()
    {
        return $this->hasMany(AppsToShop::className(), ['shop_id' => 'id']);
    }

    /**
     * Gets query for [[Apps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApps()
    {
        return $this->hasMany(Apps::className(), ['id' => 'app_id'])->viaTable('apps_to_shop', ['shop_id' => 'id']);
    }

    /**
     * Gets query for [[Articles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Articles::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ArticlesPricesLists]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArticlesPricesLists()
    {
        return $this->hasMany(ArticlesPricesList::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[AuthAssignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Boxes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoxes()
    {
        return $this->hasMany(Box::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Branches]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBranches()
    {
        return $this->hasMany(Branches::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Coupons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoupons()
    {
        return $this->hasMany(Coupons::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[CrawDatas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCrawDatas()
    {
        return $this->hasMany(CrawData::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Cronjobs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCronjobs()
    {
        return $this->hasMany(Cronjobs::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[CtemplateToShops]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCtemplateToShops()
    {
        return $this->hasMany(CtemplateToShop::className(), ['shop_id' => 'id']);
    }

    /**
     * Gets query for [[CustomPages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomPages()
    {
        return $this->hasMany(CustomPage::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[CustomerGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerGroups()
    {
        return $this->hasMany(CustomerGroups::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[DomainPointers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomainPointers()
    {
        return $this->hasMany(DomainPointer::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[EmailsSubscribes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmailsSubscribes()
    {
        return $this->hasMany(EmailsSubscribes::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Filters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(Filters::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Foods]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFoods()
    {
        return $this->hasMany(Foods::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[GoodsFields]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsFields()
    {
        return $this->hasMany(GoodsFields::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[GoodsGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsGroups()
    {
        return $this->hasMany(GoodsGroups::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[GuideLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGuideLanguages()
    {
        return $this->hasMany(GuideLanguage::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[InventorySources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventorySources()
    {
        return $this->hasMany(InventorySource::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[InventoryStocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInventoryStocks()
    {
        return $this->hasMany(InventoryStock::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Menus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Menuses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenuses()
    {
        return $this->hasMany(Menus::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ProductAttrs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttrs()
    {
        return $this->hasMany(ProductAttrs::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ProductUnitExchanges]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductUnitExchanges()
    {
        return $this->hasMany(ProductUnitExchange::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Promotions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPromotions()
    {
        return $this->hasMany(Promotions::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[SatelliteWebs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSatelliteWebs()
    {
        return $this->hasMany(SatelliteWeb::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Services]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Services::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ServicesRatings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getServicesRatings()
    {
        return $this->hasMany(ServicesRatings::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ShopToGoodFields]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopToGoodFields()
    {
        return $this->hasMany(ShopToGoodField::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(GoodsFields::className(), ['id' => 'item_id'])->viaTable('shop_to_good_field', ['sid' => 'id']);
    }

    /**
     * Gets query for [[ShopToGoodGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopToGoodGroups()
    {
        return $this->hasMany(ShopToGoodGroup::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Items0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems0()
    {
        return $this->hasMany(GoodsGroups::className(), ['id' => 'item_id'])->viaTable('shop_to_good_group', ['sid' => 'id']);
    }

    /**
     * Gets query for [[ShopToGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopToGroups()
    {
        return $this->hasMany(ShopToGroups::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ShopsConnecteds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopsConnecteds()
    {
        return $this->hasMany(ShopsConnected::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ShopsToPackages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopsToPackages()
    {
        return $this->hasMany(ShopsToPackages::className(), ['shop_id' => 'id']);
    }

    /**
     * Gets query for [[Packages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(ShopsPackages::className(), ['id' => 'package_id'])->viaTable('shops_to_packages', ['shop_id' => 'id']);
    }

    /**
     * Gets query for [[SiteConfigs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiteConfigs()
    {
        return $this->hasMany(SiteConfigs::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Slugs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSlugs()
    {
        return $this->hasMany(Slugs::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Smenus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSmenus()
    {
        return $this->hasMany(Smenu::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[StoreWebsites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoreWebsites()
    {
        return $this->hasMany(StoreWebsite::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[TempToShops]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTempToShops()
    {
        return $this->hasMany(TempToShop::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Temps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemps()
    {
        return $this->hasMany(Templates::className(), ['id' => 'temp_id'])->viaTable('temp_to_shop', ['sid' => 'id']);
    }

    /**
     * Gets query for [[TextAttrs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTextAttrs()
    {
        return $this->hasMany(TextAttrs::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[TextInstructions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTextInstructions()
    {
        return $this->hasMany(TextInstructions::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Tickets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Tickets::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[TourProgramBases]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTourProgramBases()
    {
        return $this->hasMany(TourProgramBase::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[TourServices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTourServices()
    {
        return $this->hasMany(TourServices::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ToursPrograms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToursPrograms()
    {
        return $this->hasMany(ToursPrograms::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[ToursSeries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToursSeries()
    {
        return $this->hasMany(ToursSeries::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[UserExchangeRates]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserExchangeRates()
    {
        return $this->hasMany(UserExchangeRate::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroups::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[UserTextTranslates]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserTextTranslates()
    {
        return $this->hasMany(UserTextTranslate::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[UserToShops]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserToShops()
    {
        return $this->hasMany(UserToShop::className(), ['sid' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['id' => 'user_id'])->viaTable('user_to_shop', ['sid' => 'id']);
    }

    /**
     * Gets query for [[VehiclesCategorys]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehiclesCategorys()
    {
        return $this->hasMany(VehiclesCategorys::className(), ['sid' => 'id']);
    }
}
