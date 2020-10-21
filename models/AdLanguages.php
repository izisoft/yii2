<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "ad_languages".
 *
 * @property int $id
 * @property string $lang_code
 * @property string $title
 * @property string $iso2
 * @property string $hl_code
 * @property string $hl
 * @property string $code
 * @property int $state
 * @property string $iso639
 * @property string $iso639a2
 * @property string $locale
 * @property string $language
 * @property string $region
 *
 * @property GuideLanguage $guideLanguage
 * @property GuideLanguage[] $guideLanguages
 */
class AdLanguages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ad_languages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lang_code', 'title', 'iso2', 'hl_code', 'hl', 'code', 'iso639', 'iso639a2', 'locale', 'language', 'region'], 'required'],
            [['state'], 'integer'],
            [['lang_code'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 255],
            [['iso2', 'code', 'locale'], 'string', 'max' => 16],
            [['hl_code', 'hl'], 'string', 'max' => 6],
            [['iso639', 'iso639a2', 'language', 'region'], 'string', 'max' => 8],
            [['hl_code', 'code'], 'unique', 'targetAttribute' => ['hl_code', 'code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lang_code' => 'Lang Code',
            'title' => 'Title',
            'iso2' => 'Iso2',
            'hl_code' => 'Hl Code',
            'hl' => 'Hl',
            'code' => 'Code',
            'state' => 'State',
            'iso639' => 'Iso639',
            'iso639a2' => 'Iso639a2',
            'locale' => 'Locale',
            'language' => 'Language',
            'region' => 'Region',
        ];
    }

    /**
     * Gets query for [[GuideLanguage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGuideLanguage()
    {
        return $this->hasOne(GuideLanguage::className(), ['language' => 'code']);
    }

    /**
     * Gets query for [[GuideLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGuideLanguages()
    {
        return $this->hasMany(GuideLanguage::className(), ['lang_code' => 'lang_code']);
    }
}
