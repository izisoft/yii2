<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "customer_groups".
 *
 * @property int $id
 * @property int $sid
 * @property string $title
 * @property double $discount
 * @property int $state
 * @property int $is_active
 * @property string $rule
 * @property int $plevel
 * @property int $type_id
 * @property string $note
 *
 * @property Shops $s
 */
class CustomerGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sid', 'title', 'rule', 'note'], 'required'],
            [['sid', 'state', 'is_active', 'plevel', 'type_id'], 'integer'],
            [['discount'], 'number'],
            [['title', 'rule', 'note'], 'string', 'max' => 255],
            [['sid'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['sid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => 'Sid',
            'title' => 'Title',
            'discount' => 'Discount',
            'state' => 'State',
            'is_active' => 'Is Active',
            'rule' => 'Rule',
            'plevel' => 'Plevel',
            'type_id' => 'Type ID',
            'note' => 'Note',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS()
    {
        return $this->hasOne(Shops::className(), ['id' => 'sid']);
    }
}
