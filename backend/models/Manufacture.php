<?php

namespace izi\backend\models;

use Yii;
 
class Manufacture extends \izi\local\Local
{
     
    
    public function getAll()
    {
        return Yii::$app->local->getCountries();
    }
}
