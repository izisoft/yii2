<?php

namespace izi\sim;


use yii\mongodb\ActiveRecord;

class SimonlineMongodbModel extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'simonline2';
    }
    
    public static function collectionModule()
    {
        return 'simonline_module';
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
//            'network_label',
            'category_id',
            'category2_id',
            'category3_id',
//            'category_label',
            'partner_id',
//            'partner_label',
            'price',
            'price0',
            'price1',
            'price2',
            'status',
            'updated_at',
            'score',        // Tổng số nut
            'number_of_key', // Tổng số phím
            'istm',         // Trả góp
            'type_id',
            'note',
            'fixed_price',
            'fixed_profit',
            'created_time',
            'history',
            'store_name',
            'duplicate',
            'is_sold',
            'exchange_price',
            'nguhanh',
            'nut',
            'is_invisible',
            'p_invisible',
            'note2',
            'dauso',
            's2','s3','s4','s5','s6', 'daicat', 'attrs'
        ];
    }
}
