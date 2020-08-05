<?php 
namespace izi\sim\es;
use Yii;
class Simonline extends \yii\elasticsearch\ActiveRecord
{
    public function init()
    {
        Yii::$app->setComponents([
            'elasticsearch' => [
                'class' => 'yii\elasticsearch\Connection',
                'autodetectCluster' => false,
                'nodes' => [
                    ['http_address' => '127.0.0.1:9200'],
                    // configure more hosts if you have a cluster
                ],
            ],
        ]);
        
        
    }
    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        // path mapping for '_id' is setup to field 'id'
        return ['id', 'name', 'address', 'registration_date'];
    }
    
    /**
     * @return ActiveQuery defines a relation to the Order record (can be in other database, e.g. redis or sql)
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->orderBy('id');
    }
    
    /**
     * Defines a scope that modifies the `$query` to return only active(status = 1) customers
     */
    public static function active($query)
    {
        $query->andWhere(['status' => 1]);
    }
    
    
    public function abc()
    {
       // $customer = new Simonline();
//         $customer->primaryKey = 2; // in this case equivalent to $customer->id = 1;
//         $customer->attributes = ['name' => 'test2'];
//         $customer->save();
        $customers = static::find() ->all();
//         $customers = Simonline::find()->active()->all();
        
        view($customers);
        
//         return $customers;
    }
}