<?php

namespace izi\template\models;

/**
 * This is the ActiveQuery class for [[Shops]].
 *
 * @see Shops
 */
class ShopsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Shops[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Shops|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
