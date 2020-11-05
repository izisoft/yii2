<?php

namespace izi\template\models;

/**
 * This is the ActiveQuery class for [[TempToShop]].
 *
 * @see TempToShop
 */
class TempToShopQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TempToShop[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TempToShop|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
