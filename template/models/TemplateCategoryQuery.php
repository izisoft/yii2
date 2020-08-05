<?php

namespace izi\template\models;

/**
 * This is the ActiveQuery class for [[TemplateCategory]].
 *
 * @see TemplateCategory
 */
class TemplateCategoryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TemplateCategory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TemplateCategory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
