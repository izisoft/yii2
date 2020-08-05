<?php

namespace izi\frontend\sim;

use Yii;

use yii\mongodb\ActiveRecord;

class SimonlineMongodbModel extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'simonline';
    }
    
    /**
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return [
            '_id',
            'id',
            'display',
            'network_id',
            'network_label',
            'category_id',
            'category2_id',
            'category3_id',
            'category_label',
            'partner_id',
            'partner_label',
            'price',
            'price1',
            'price2',
            'status',
            'updated_at',
            'score',
            'type_id',
            'note',
            'fixed_price',
            'fixed_profit',
            'created_time',
            'history',
            'store_name'
        ];
    }
}
