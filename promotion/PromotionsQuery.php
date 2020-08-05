<?php

namespace izi\promotion;

/**
 * This is the ActiveQuery class for [[Promotions]].
 *
 * @see Promotions
 */
class PromotionsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Promotions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Promotions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
