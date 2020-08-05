<?php

namespace izi\sim;


use yii\mongodb\ActiveRecord;

class SimonlineSelledModel extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'simonline_selled';
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
            'price',
            'created_time',
            'is_selled',
        ];
    }
}
