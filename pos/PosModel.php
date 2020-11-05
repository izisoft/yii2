<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\pos;
use Yii;
class PosModel extends \yii\db\ActiveRecord
{
	 
    public static function tableName()
    {
        return 'pos_bills';
    }
}