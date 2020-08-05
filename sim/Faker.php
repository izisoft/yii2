<?php

namespace izi\sim;

use Yii;

class Faker extends \yii\base\Component
{
    
    public function getCustomerData($limit = 10){
        $l = (new \yii\db\Query())->from('customers')->where(['and',
            ['type_id' => 0, 'code' => ''],
            ['<>', 'name', '']
        ])->orderBy((new \yii\db\Expression('rand()')))->limit($limit)->all();
        
        
        return $l;
    }
    
    public function getRandomSim(){
        $so = '';
        $dauso = [
            '032', '033', '034', '035', '036', '037', '038', '039',
            '052','053','055','056','057','058','059',
            '070','071','072','073','074','075','076','077','078','079',
            '081','082','083','084','085','086','087','088','089',
            '090','091','092','093','094','095','096','097','098','099',
        ];
        
        $so = $dauso[rand(0, count($dauso)-1)] . rand(100, 999) . 'xxxx';
        
        return $so;
    }
    
    public function getFakeData($options = [])
    {        
        $cookies1 = Yii::$app->request->cookies;
        
        if (($cookie2 = $cookies1->get('faker-simdata')) !== null) {
            return ($cookie2->value);
        }
        
        $data = [];
        $customers = $this->getCustomerData(5);
        
        $states = [
            ['id'=> 1, 'text' => 'Đã đặt mua'], 
            ['id'=> 2, 'text' => 'Sim đang GD'],
            ['id'=> 3,'text'=> 'Đã giao sim'],            
        ];
        
        $time = time();
        
        foreach ($customers as $c){
            
            $time -= rand(60, 86400);                       
            
            $state = $states[rand(0, count($states)-1)];

            if($state['id'] == 3 && time() - $time < 86400/2){
                $state = $states[1];
            }
            
            $data[] = [
                'name'  =>  $c['name'],
                'sim'   =>  $this->getRandomSim(),
                'time'  =>  $time,
                'state' =>  $state,
            ];
        }
        
        $cookies = Yii::$app->response->cookies;
        
        // add a new cookie to the response to be sent
        $cookies->add(new \yii\web\Cookie([
            'name' => 'faker-simdata',
            'value' => $data,
            'expire'=> time() + 300
        ]));
        
        return $data;
    }
}