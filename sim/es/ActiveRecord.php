<?php 
namespace izi\sim\es;
use Yii;
class ActiveRecord extends \yii\elasticsearch\ActiveRecord
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
}