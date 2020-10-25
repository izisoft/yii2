<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "slugs".
 *
 * @property int $slug_id
 * @property string $url
 * @property string $entity_type Entity type code
 * @property int $entity_id Entity ID
 * @property string $request_path Request Path
 * @property string $target_path Target Path
 * @property string $metadata
 * @property string $route
 * @property string $router
 * @property int $item_id
 * @property int $item_type
 * @property int $sid
 * @property string $rel
 * @property int $state
 * @property string $lang
 * @property string $checksum
 * @property string $redirect
 * @property string $bizrule
 * @property int $status
 * @property int $is_active
 *
 * @property Shops $s
 */
class Slugs extends \izi\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'slugs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['entity_id', 'item_id', 'item_type', 'sid', 'state', 'status', 'is_active'], 'integer'],
            [['metadata', 'bizrule'], 'string'],
            [['url', 'request_path', 'target_path', 'redirect'], 'string', 'max' => 255],
            [['entity_type', 'checksum'], 'string', 'max' => 32],
            [['route'], 'string', 'max' => 64],
            [['router'], 'string', 'max' => 128],
            [['rel'], 'string', 'max' => 10],
            [['lang'], 'string', 'max' => 16],
            [['item_id', 'item_type', 'sid'], 'unique', 'targetAttribute' => ['item_id', 'item_type', 'sid']],
            [['url', 'sid'], 'unique', 'targetAttribute' => ['url', 'sid']],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'slug_id' => 'Slug ID',
            'url' => 'Url',
            'entity_type' => 'Entity Type',
            'entity_id' => 'Entity ID',
            'request_path' => 'Request Path',
            'target_path' => 'Target Path',
            'metadata' => 'Metadata',
            'route' => 'Route',
            'router' => 'Router',
            'item_id' => 'Item ID',
            'item_type' => 'Item Type',
            'sid' => 'Sid',
            'rel' => 'Rel',
            'state' => 'State',
            'lang' => 'Lang',
            'checksum' => 'Checksum',
            'redirect' => 'Redirect',
            'bizrule' => 'Bizrule',
            'status' => 'Status',
            'is_active' => 'Is Active',
        ];
    }

    /**
     * Gets query for [[S]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
