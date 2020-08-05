<?php

namespace izi\promotion;

use Yii;

/**
 * This is the model class for table "articles".
 *
 * @property int $id
 * @property string $title
 * @property string $code
 * @property int $is_active
 * @property string $barcode
 * @property string $mpn
 * @property int $category_id
 * @property int $position
 * @property int $owner
 * @property int $created_by
 * @property int $status
 * @property int $viewed
 * @property string $lang
 * @property string $short_title
 * @property string $url
 * @property string $check_sum
 * @property string $type
 * @property string $bizrule
 * @property string $time
 * @property string $content
 * @property int $is_promotion
 * @property string $price1
 * @property string $price2
 * @property string $price3
 * @property int $old_id
 * @property int $state
 * @property int $sid
 * @property int $currency
 * @property int $manage_quantity
 * @property string $url_link
 * @property int $rate
 * @property int $updated
 * @property int $is_invisibled
 * @property string $updated_at
 * @property int $is_comment
 * @property int $is_show_comment
 * @property string $unit
 * @property string $tax_vat
 * @property int $safe_inventory
 * @property int $price_type
 * @property int $started_date
 * @property int $expired_date
 *
 * @property AppItemsToNote[] $appItemsToNotes
 * @property AppItemsNote[] $notes
 * @property ArticlesPrices[] $articlesPrices
 * @property ArticlesToAttrs[] $articlesToAttrs
 * @property ArticlesToFilters[] $articlesToFilters
 * @property Filters[] $filters
 * @property ClassToArticles[] $classToArticles
 * @property Classes[] $classes
 * @property CouponToProduct[] $couponToProducts
 * @property Coupons[] $coupons
 * @property ItemToCourses[] $itemToCourses
 * @property ItemToGoodsGroup[] $itemToGoodsGroups
 * @property GoodsGroups[] $groups
 * @property ItemToPlace[] $itemToPlaces
 * @property ItemToPrices[] $itemToPrices
 * @property ItemToTag[] $itemToTags
 * @property Tags[] $tags
 * @property ItemToTemplate[] $itemToTemplates
 * @property Templates[] $temps
 * @property ItemToTextAttrs[] $itemToTextAttrs
 * @property ItemToWarehouse[] $itemToWarehouses
 * @property ItemsToBranches[] $itemsToBranches
 * @property Branches[] $brans
 * @property ItemsToCategory[] $itemsToCategories
 * @property SiteMenu[] $categories
 * @property ItemsToCustomer[] $itemsToCustomers
 * @property ItemsToCustomers[] $itemsToCustomers0
 * @property ItemsToProducer[] $itemsToProducers
 * @property ProductUnitExchange[] $productUnitExchanges
 * @property PromotionToProduct[] $promotionToProducts
 * @property Promotions[] $promotions
 * @property ToursAttrs $toursAttrs
 */
class Articles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'code', 'barcode', 'mpn', 'short_title', 'url', 'check_sum', 'bizrule', 'content', 'url_link', 'updated', 'unit', 'started_date', 'expired_date'], 'required'],
            [['is_active', 'category_id', 'position', 'owner', 'created_by', 'status', 'viewed', 'is_promotion', 'old_id', 'state', 'sid', 'currency', 'manage_quantity', 'rate', 'updated', 'is_invisibled', 'is_comment', 'is_show_comment', 'safe_inventory', 'price_type', 'started_date', 'expired_date'], 'integer'],
            [['bizrule', 'content'], 'string'],
            [['time', 'updated_at'], 'safe'],
            [['price1', 'price2', 'price3', 'tax_vat'], 'number'],
            [['title', 'short_title', 'url', 'url_link'], 'string', 'max' => 255],
            [['code', 'barcode', 'mpn', 'unit'], 'string', 'max' => 64],
            [['lang'], 'string', 'max' => 16],
            [['check_sum', 'type'], 'string', 'max' => 32],
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
            'code' => 'Code',
            'is_active' => 'Is Active',
            'barcode' => 'Barcode',
            'mpn' => 'Mpn',
            'category_id' => 'Category ID',
            'position' => 'Position',
            'owner' => 'Owner',
            'created_by' => 'Created By',
            'status' => 'Status',
            'viewed' => 'Viewed',
            'lang' => 'Lang',
            'short_title' => 'Short Title',
            'url' => 'Url',
            'check_sum' => 'Check Sum',
            'type' => 'Type',
            'bizrule' => 'Bizrule',
            'time' => 'Time',
            'content' => 'Content',
            'is_promotion' => 'Is Promotion',
            'price1' => 'Price1',
            'price2' => 'Price2',
            'price3' => 'Price3',
            'old_id' => 'Old ID',
            'state' => 'State',
            'sid' => 'Sid',
            'currency' => 'Currency',
            'manage_quantity' => 'Manage Quantity',
            'url_link' => 'Url Link',
            'rate' => 'Rate',
            'updated' => 'Updated',
            'is_invisibled' => 'Is Invisibled',
            'updated_at' => 'Updated At',
            'is_comment' => 'Is Comment',
            'is_show_comment' => 'Is Show Comment',
            'unit' => 'Unit',
            'tax_vat' => 'Tax Vat',
            'safe_inventory' => 'Safe Inventory',
            'price_type' => 'Price Type',
            'started_date' => 'Started Date',
            'expired_date' => 'Expired Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAppItemsToNotes()
    {
        return $this->hasMany(AppItemsToNote::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(AppItemsNote::className(), ['id' => 'note_id'])->viaTable('app_items_to_note', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticlesPrices()
    {
        return $this->hasMany(ArticlesPrices::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticlesToAttrs()
    {
        return $this->hasMany(ArticlesToAttrs::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticlesToFilters()
    {
        return $this->hasMany(ArticlesToFilters::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(Filters::className(), ['id' => 'filter_id'])->viaTable('articles_to_filters', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClassToArticles()
    {
        return $this->hasMany(ClassToArticles::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClasses()
    {
        return $this->hasMany(Classes::className(), ['id' => 'class_id'])->viaTable('class_to_articles', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCouponToProducts()
    {
        return $this->hasMany(CouponToProduct::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoupons()
    {
        return $this->hasMany(Coupons::className(), ['id' => 'coupon_id'])->viaTable('coupon_to_product', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToCourses()
    {
        return $this->hasMany(ItemToCourses::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToGoodsGroups()
    {
        return $this->hasMany(ItemToGoodsGroup::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(GoodsGroups::className(), ['id' => 'group_id'])->viaTable('item_to_goods_group', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToPlaces()
    {
        return $this->hasMany(ItemToPlace::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToPrices()
    {
        return $this->hasMany(ItemToPrices::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToTags()
    {
        return $this->hasMany(ItemToTag::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tags::className(), ['id' => 'tag_id'])->viaTable('item_to_tag', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToTemplates()
    {
        return $this->hasMany(ItemToTemplate::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemps()
    {
        return $this->hasMany(Templates::className(), ['name' => 'temp_id'])->viaTable('item_to_template', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToTextAttrs()
    {
        return $this->hasMany(ItemToTextAttrs::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemToWarehouses()
    {
        return $this->hasMany(ItemToWarehouse::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsToBranches()
    {
        return $this->hasMany(ItemsToBranches::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrans()
    {
        return $this->hasMany(Branches::className(), ['id' => 'bran_id'])->viaTable('items_to_branches', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsToCategories()
    {
        return $this->hasMany(ItemsToCategory::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(SiteMenu::className(), ['id' => 'category_id'])->viaTable('items_to_category', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsToCustomers()
    {
        return $this->hasMany(ItemsToCustomer::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsToCustomers0()
    {
        return $this->hasMany(ItemsToCustomers::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsToProducers()
    {
        return $this->hasMany(ItemsToProducer::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductUnitExchanges()
    {
        return $this->hasMany(ProductUnitExchange::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotionToProducts()
    {
        return $this->hasMany(PromotionToProduct::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromotions()
    {
        return $this->hasMany(Promotions::className(), ['id' => 'promotion_id'])->viaTable('promotion_to_product', ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToursAttrs()
    {
        return $this->hasOne(ToursAttrs::className(), ['item_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ArticlesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ArticlesQuery(get_called_class());
    }
}
