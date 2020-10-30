<?php

namespace izi\models;

use Yii;
use app\models\Articles;

/**
 * This is the model class for table "catalog_post_website".
 *
 * @property int $option_id
 * @property int $post_id Product ID
 * @property int $website_id Website ID
 * @property int $is_active
 * @property int $is_hidden
 * @property int $sort_order
 *
 * @property StoreWebsite $website
 * @property Articles $post
 */
class CatalogPostWebsite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalog_post_website';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id', 'website_id'], 'required'],
            [['post_id', 'website_id', 'is_active', 'is_hidden', 'sort_order'], 'integer'],
            [['post_id', 'website_id'], 'unique', 'targetAttribute' => ['post_id', 'website_id']],
            [['website_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoreWebsite::className(), 'targetAttribute' => ['website_id' => 'website_id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Articles::className(), 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'option_id' => 'Option ID',
            'post_id' => 'Post ID',
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
    public function getPost()
    {
        return $this->hasOne(Articles::className(), ['id' => 'post_id']);
    }
}
