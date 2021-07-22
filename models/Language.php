<?php

namespace izi\models;

use Yii;

/**
 * This is the model class for table "languages".
 *
 * @property int $id
 * @property string $lang_code
 * @property string $title
 * @property string $iso2
 * @property string $hl_code
 * @property string $hl
 * @property string $code
 * @property string $iso639
 * @property string $iso639a2
 * @property string $locale
 * @property string $language
 * @property string $region
 */
class Language extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'languages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lang_code'], 'required'],
            [['lang_code'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 255],
            [['iso2', 'locale'], 'string', 'max' => 16],
            [['hl_code', 'hl'], 'string', 'max' => 6],
            [['code', 'iso639', 'iso639a2', 'language', 'region'], 'string', 'max' => 8],
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
            'iso639' => 'Iso639',
            'iso639a2' => 'Iso639a2',
            'locale' => 'Locale',
            'language' => 'Language',
            'region' => 'Region',
        ];
    }
}
