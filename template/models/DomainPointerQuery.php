<?php

namespace izi\template\models;

/**
 * This is the ActiveQuery class for [[DomainPointer]].
 *
 * @see DomainPointer
 */
class DomainPointerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return DomainPointer[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return DomainPointer|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
