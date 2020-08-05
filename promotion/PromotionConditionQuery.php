<?php

namespace izi\promotion;

/**
 * This is the ActiveQuery class for [[PromotionCondition]].
 *
 * @see PromotionCondition
 */
class PromotionConditionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return PromotionCondition[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return PromotionCondition|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
