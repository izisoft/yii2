<?php

namespace izi\template\models;

/**
 * This is the ActiveQuery class for [[Templates]].
 *
 * @see Templates
 */
class TemplatesQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Templates[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Templates|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
