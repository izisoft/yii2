<?php

namespace izi\promotion;

/**
 * This is the ActiveQuery class for [[Coupons]].
 *
 * @see Coupons
 */
class CouponsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Coupons[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Coupons|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
