<?php

namespace izi\promotion;

/**
 * This is the ActiveQuery class for [[PromotionConditionLabel]].
 *
 * @see PromotionConditionLabel
 */
class PromotionConditionLabelQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return PromotionConditionLabel[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return PromotionConditionLabel|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
