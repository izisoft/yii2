<?php
namespace izi\web;
global $table_lft;
$table_lft= 0;
use Yii;
use yii\db\Query;
use app\modules\admin\models\Siteconfigs;
use app\models\Articles;
use app\models\Slugs;
use app\models\SiteMenu;
use app\models\Filters;
class Zii extends \yii\base\Component
{
    public function __construct(){
       
    }
    public function countTable($table, $con = []){
        return (new Query())->from($table)->where($con)->count(1);
    }
    public function getUserCurrency(){
    	    	    	
        return Yii::$app->currencies->getUserCurrency();
        $r = [];
        if(0>1 && isset($_SESSION['config']['currency'])){
            $r = $_SESSION['config']['currency'];
        }else{
            $v = Siteconfigs::getItem('SETTINGS',__LANG__);

            if(isset($v['currency'])){
                $_SESSION['config']['currency'] = $v['currency'];
                $r = $_SESSION['config']['currency'];
            }
        }
        //
        if(empty($r)){
            $r['list'] = [['id'=>1,'title'=>'Tiếng Việt','code'=>'vi-VN']];
        }
        return $r;
    }
    
    public function removeTransportSupplierTourProgram($o = []){
    	
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        //1. Xóa bảng danh sách xe - ct
        Yii::$app->db->createCommand()->delete('tours_programs_to_suppliers',[
            'supplier_id'=>$supplier_id,
            'item_id'=>$item_id,
            'segment_id'=>$segment_id
        ])->execute();
        //2. Xóa bảng phương tiện - ct
        Yii::$app->db->createCommand()->delete('tours_programs_suppliers_vehicles',[
            'supplier_id'=>$supplier_id,
            'item_id'=>$item_id,
            'segment_id'=>$segment_id
        ])->execute();
        //3. Xóa bảng chặng - ct
        Yii::$app->db->createCommand()->delete('tours_programs_services_distances',[
            'supplier_id'=>$supplier_id,
            'item_id'=>$item_id,
            'segment_id'=>$segment_id
        ])->execute();
        //4. Xóa bảng giá - ct
        Yii::$app->db->createCommand()->delete('tours_programs_suppliers_prices',[
            'supplier_id'=>$supplier_id,
            'item_id'=>$item_id,
            'segment_id'=>$segment_id
            
        ])->execute();
    }
    
    public function getUserLanguages(){
        return \izi\models\Language::getUserLanguage();
    }
    public function calcDistancePrice($o=[]){
        //
        $price = $t = 0;
        $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        //
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $package_id = isset($o['package_id']) ? $o['package_id'] : 0;
        $vehicle_id = isset($o['vehicle_id']) ? $o['vehicle_id'] : 0;
        $distance_id = isset($o['distance_id']) ? $o['distance_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $exchange_rate = isset($o['exchange_rate']) ? $o['exchange_rate'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $from_date = isset($o['from_date']) ? $o['from_date'] : date('Y-m-d');
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : 0;
        $time_id = isset($o['time_id']) ? $o['time_id'] : -1;
        $loadDefault = isset($o['loadDefault']) && cbool($o['loadDefault']) == 1 ? true : false;
        $updateDatabase = isset($o['updateDatabase']) ? $o['updateDatabase'] : true;
        //
        $distance_item = \app\modules\admin\models\Distances::getItem($distance_id);
        //echo json_encode($distance_item);
        //view($o,true);
        $quotation_id = isset($o['quotation_id']) ? $o['quotation_id'] : 0;
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : 0;
        $season_id = isset($o['season_id']) ? $o['season_id'] : 0;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $weekend_id = isset($o['weekend_id']) ? $o['weekend_id'] : 0;
        $group_id = isset($o['group_id']) ? $o['group_id'] : 0;
        $pax_type = isset($o['pax_type']) ? $o['pax_type'] : 1;
        //
        //'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
        //'supplier_id'=>$supplier_id,
        ///'total_pax'=>$item['guest'],
        //'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
        //'package_id'=>0,
        
        // Lấy giá từ CSDL
        if(!$loadDefault){
            $query = (new Query())->from(['a'=>'tours_programs_suppliers_prices'])
            ->innerJoin(['b'=>'vehicles_categorys'],'b.id=a.vehicle_id')
            ->where([
                'a.supplier_id'=>$supplier_id,
                'a.vehicle_id'=>$vehicle_id,
                'a.item_id'=>$item_id,
                'a.service_id'=>$distance_id,
                'b.type'=>$pax_type,
                'a.segment_id'=>$segment_id,
            ]);
           // view($query->createCommand()->getRawSql());
            $item = $query->one();
            //$item['o']= $query->createCommand()->getRawSql();
            if(empty($item)){
                $loadDefault = true;$updateDatabase = true;
            }else{
                return $item;
            }
        }
        //
        $price_type = !empty($item) ? $item['price_type'] : 0; // Giá chặng
        
        $query = (new Query())->from('distances_to_prices')->where([
            'item_id'=>$distance_id,
            'vehicle_id'=>$vehicle_id,
            'quotation_id'=>$quotation_id,
            'nationality_id'=>$nationality_id,
            'package_id'=>$package_id,
            'season_id'=>$season_id,
            'group_id'=>$group_id,
            'weekend_id'=>$weekend_id,
            'package_id'=>$package_id,
            
            
        ]);
        //if()
        $currency = 1;
        $item = $query->one();
        
        if(!empty($item)){
            $price = $item['price1'];
            $currency = $item['currency'];
        }
        //
        
        if($price == 0){
            $price_type = 1; // Giá km
            $query = (new Query())->from(['a'=>'vehicles_to_prices'])->where([
                
                'a.quotation_id'=>$quotation_id,
                'a.nationality_id'=>$nationality_id,
                'a.package_id'=>$package_id,
                'a.season_id'=>$season_id,
                'a.group_id'=>$group_id,
                'a.weekend_id'=>$weekend_id,
                'a.package_id'=>$package_id,
                'a.item_id'=>$vehicle_id,
                'a.supplier_id'=>$supplier_id,
                
            ])
            ->innerJoin(['b'=>'vehicles_categorys'],'b.id=a.item_id')
            ->andWhere(['>','a.pmax',$distance_item['distance']-1])
            ->andWhere(['<','a.pmin',$distance_item['distance']+1])
            ->select(['a.*','b.*','id'=>'b.id']);
            $item = $query->one();
            
            if(!empty($item)){
                
                $price = $item['price1'];
                $currency =  $item['currency'];
            }
        }
        //
        if(!empty($item) && isset($item['distance'])){
            $distance_item['distance'] = $item['distance'] ;
        }else{
            $distance_item['distance'] = $price_type == 0 ? 1 : $distance_item['distance'] ;
        }
        
        //
        if(!empty($item) && $updateDatabase){
            if((new Query())->from('tours_programs_suppliers_prices')->where([
                'supplier_id'=>$supplier_id,
                'item_id'=>$item_id,
                'vehicle_id'=>$vehicle_id,
                'service_id'=>$distance_id,
                'segment_id'=>$segment_id,
            ])->count(1) == 0){
                if((new Query())->from('tours_programs_suppliers_prices')->where([
                    'supplier_id'=>$supplier_id,
                    'item_id'=>$item_id,
                    'vehicle_id'=>0,
                    'service_id'=>$distance_id,
                    'segment_id'=>$segment_id,
                ])->count(1) == 0){
                    Yii::$app->db->createCommand()->insert('tours_programs_suppliers_prices',[
                        'supplier_id'=>$supplier_id,
                        'vehicle_id'=>$vehicle_id,
                        'item_id'=>$item_id,
                        'service_id'=>$distance_id,
                        'price1'=>$price,
                        'price_type'=>$price_type,
                        'segment_id'=>$segment_id,
                        'quantity'=>isset($distance_item['distance']) && is_numeric($distance_item['distance']) ? $distance_item['distance'] : 0
                    ])->execute();
                }else {
                    Yii::$app->db->createCommand()->update('tours_programs_suppliers_prices',[
                        'vehicle_id'=>$vehicle_id,
                    //    'price1'=>$price,
                        'price_type'=>$price_type,
                        'quantity'=>isset($distance_item['distance']) && is_numeric($distance_item['distance']) ? $distance_item['distance'] : 0
                    ],['supplier_id'=>$supplier_id,
                        'vehicle_id'=>0,
                        'item_id'=>$item_id,
                        'segment_id'=>$segment_id,
                        'service_id'=>$distance_id,])->execute();
                }
                
            }else{
                Yii::$app->db->createCommand()->update('tours_programs_suppliers_prices',[
                    
                   // 'price1'=>$price,
                    'price_type'=>$price_type,
                    'quantity'=>$distance_item['distance']
                ],['supplier_id'=>$supplier_id,
                    'vehicle_id'=>$vehicle_id,
                    'item_id'=>$item_id,
                    'segment_id'=>$segment_id,
                    'service_id'=>$distance_id,])->execute();
            }
        }
        //
        return [
            //'vehicle'=>$item,
            'quantity'=>$distance_item['distance'],
            //'supplier'=>\app\modules\admin\models\Customers::getItem($supplier_id),
            'price1'=>$price,
            //'total_price'=>($price * $distance_item['distance']),
            'price_type'=>$price_type,
            'currency'=>$currency
        ];
    }
    
    public function getDomain($domain = ''){
        
        if($domain == ''){
            $domains = explode(',', get_site_value('seo/domain'));
            $d = $domains[0];
        }else {
            $d = $domain;
        }

        if(strpos($d, '://') === false){
        	$s = get_site_value('seo');
        	$w = str_replace('www.', '', $domain);
        	if(isset($s['ssl'][$w]) && $s['ssl'][$w] =='on'){
        		$scheme = 'https';
        	}else{
        		$scheme = SCHEME;
        	}
            $d = $scheme . '://' . $d;
        }
        return $d;
    }
    
    public function countSitemapLink(){
        $query = new Query();
        $query->from(['a'=>'slugs'])
        //->innerJoin(['b'=>'articles'],'a.item_id=b.id')
        ->where(['a.sid'=>__SID__,'is_active'=>1])
        ->andWhere(['>','a.state',-2])
        ->andWhere(['not in','a.rel',['nofollow']])
        ->andWhere(['not in','a.url',['','#','/']])
        ->andWhere(['not in','a.route',['','#','/']])
        ;
        
        return ($query->count(1))+1;
    }
    public function generateSitemap($o = []){
        $lastmod = isset($o['lastmod']) ? $o['lastmod'] : '';
        $lastmod = '';
        $freq = isset($o['freq']) ? $o['freq'] : '';
        $priority = isset($o['priority']) ? $o['priority'] : '';
        $updateDatabase = isset($o['updateDatabase']) && $o['updateDatabase'] === true ? true : false;
        $domain = isset($o['domain']) ? $o['domain'] : '';
        $existed = [$this->getDomain($domain)];
        //
        $html = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $query = new Query();
        $query->from(['a'=>'slugs'])
        //->innerJoin(['b'=>'articles'],'a.item_id=b.id')
        ->where(['a.sid'=>__SID__,'is_active'=>1])
        ->andWhere(['>','a.state',-2])
        ->andWhere(['not in','a.rel',['nofollow']])
        ->andWhere(['not in','a.url',['','#','/']])
        ->andWhere(['not in','a.route',['','#','/']])
        ;
//         $html .= '
// <url>
//   <loc>'.$this->getDomain($domain).'</loc>'.($lastmod != "" ? '
//   <lastmod>'.$lastmod.'</lastmod>' : '').''.($freq != "" ? '
//   <changefreq>'.$freq.'</changefreq>' : '').'
// </url>';
        $html .= '
<url>
  <loc>'.$this->getDomain($domain).'/</loc>'.($lastmod != "" ? '
  <lastmod>'.$lastmod.'</lastmod>' : '').''.($freq != "" ? '
  <changefreq>'.$freq.'</changefreq>' : '').'
</url>';
        foreach ($query->all() as $k=>$v){

            $url = \common\models\Slugs::getDirectLink($v['url'], $v['item_id'], $v['item_type'],$domain);
            
            $url = rtrim($url, '/');
            
            //view($v['url'] . ' / ' . $url);
             
            if(!in_array($url, $existed)){
            $html .= '
<url>
  <loc>'.$url.'</loc>'.($lastmod != "" ? '
  <lastmod>'.$lastmod.'</lastmod>' : '').''.($freq != "" ? '
  <changefreq>'.$freq.'</changefreq>' : '').'
</url>';
            $existed[] = $url;
        }
        }
        $html .= '
</urlset>';
        //
        if($updateDatabase){
            //Siteconfigs::updateSiteConfigs('seo/sitemap', $html);
        }
        //exit;
        return $html;
    }
    
    public function getTourProgramSuppliers($id, $o = TYPE_ID_VECL){
        $default = isset($o['default']) ? $o['default'] : [];
        if(is_array($o)){
            
            
            
            $type_id = isset($o['type_id']) ? $o['type_id'] : TYPE_ID_VECL;
            $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        }else{
            $type_id = $o;
        }
        $query = new Query();
        $query->select(['a.*',
            'place_id'=>(new Query())->select('place_id')->from('customers_to_places')->where('customer_id=a.id')->limit(1)
        ])->from(['a'=>'customers'])
        ->innerJoin(['b'=>'tours_programs_to_suppliers'],'a.id=b.supplier_id')
        ->where(['b.item_id'=>$id,'b.type_id'=>$type_id])->groupBy(['a.id'])
        //->andWhere(['b.item_id'=>$id])
        ;
        if(isset($segment_id) && $segment_id>0){
            $query->andWhere(['b.segment_id'=>$segment_id]);
        }
        
        $l = $query->orderBy(['b.position'=>SORT_ASC, 'a.name'=>SORT_ASC])->all();
        
        if(!empty($l)){
            return $l;
        }
        
        return $default;
        
    }
    
    public function getSelectedVehicles($o = []){
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : 0;
        $default = isset($o['default']) ? $o['default'] : false;
        $loadDefault = isset($o['loadDefault']) ? $o['loadDefault'] : false;
        $updateDatabase = isset($o['updateDatabase']) ? $o['updateDatabase'] : false;
        $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        if($loadDefault){
            $r = $this->getVehicleAuto([
                'total_pax'=>$total_pax,
                'nationality_id'=>$nationality_id,
                'supplier_id'=>$supplier_id,
                'auto'=>true,
                
            ]);
            
            //view($updateDatabase);
            
            if($updateDatabase){
                /*/ C1
                 Yii::$app->db->createCommand()->delete('tours_programs_to_suppliers',[
                 'supplier_id'=>$supplier_id,
                 'item_id'=>$item_id,
                 'segment_id'=>$segment_id
                 ])->execute();
                 /*/
                /*/ Clear
                Yii::$app->db->createCommand()->delete('tours_programs_suppliers_vehicles',[
                    'supplier_id'=>$supplier_id,
                    'item_id'=>$item_id,
                    'segment_id'=>$segment_id
                ])->execute();
                // Insert
                 * 
                 */
            	$existed = [];
                if(!empty($r)){
                    foreach ($r as $k=>$v){
                    	$existed [] = $v['id'];
                    	if((new Query())->from('tours_programs_suppliers_vehicles')->where([
                    			'supplier_id'=>$supplier_id,
                    			'item_id'=>$item_id,
                    			'segment_id'=>$segment_id,
                    			'vehicle_id'=>$v['id'],
                    	])->count(1) == 0){
                    		Yii::$app->db->createCommand()->insert('tours_programs_suppliers_vehicles',[
                    				'supplier_id'=>$supplier_id,
                    				'item_id'=>$item_id,
                    				'vehicle_id'=>$v['id'],
                    				'segment_id'=>$segment_id,
                    				'quantity'=>$v['quantity']
                    		])->execute();
                    	}else{
                    	
                    	
                    		Yii::$app->db->createCommand()->update('tours_programs_suppliers_vehicles',[                    				 
                    				'quantity'=>$v['quantity']
                    		],[
                    				'supplier_id'=>$supplier_id,
                    				'item_id'=>$item_id,
                    				'segment_id'=>$segment_id,
                    				'vehicle_id'=>$v['id'],
                    		])->execute();
                    	}
                        
                    }
                }
                
                Yii::$app->db->createCommand()->delete('tours_programs_suppliers_vehicles',
                ['and', ['not in','vehicle_id',$existed],		
                		[
                		'supplier_id'=>$supplier_id,
                		'item_id'=>$item_id,
                		'segment_id'=>$segment_id
                ]])->execute();
                		
                Yii::$app->db->createCommand()->delete('tours_programs_suppliers_prices',
                		['and', ['not in','vehicle_id',$existed],
                				[
                						'supplier_id'=>$supplier_id,
                						'item_id'=>$item_id,
                						'segment_id'=>$segment_id
                				]])->execute();
            }
        }else{
            
            // Lấy danh sách xe
            $query = new Query();
            $query->from(['a'=>'vehicles_categorys'])
            ->innerJoin(['b'=>'vehicles_to_cars'],'a.id=b.vehicle_id')
            ->innerJoin(['c'=>'tours_programs_suppliers_vehicles'],'a.id=c.vehicle_id')
            ->where(['>','a.state',-2])
            ->andWhere(['a.type'=>1,
                'b.parent_id'=>$supplier_id,
                'b.is_active'=>1,
                'c.supplier_id'=>$supplier_id,
                'c.item_id'=>$item_id,
                'c.segment_id'=>$segment_id
            ])
            ->select(['a.*','c.quantity','maker_title'=>(new Query())->select('title')->from('vehicles_makers')->where('id=a.maker_id')])
            ->orderBy(['a.pmax'=>SORT_DESC]);
            //view($query->createCommand()->getRawSql());
            $r = $query->all();
            
        }
        if(empty($r) && $default){
            $r = [['id'=>0,'title'=>'Chọn phương tiện','quantity'=>0,'maker_title'=>'']];
        }
        return $r;
    }
    
    public function chooseVehicleAuto($o = []){
        /*
         * Số lượng khách
         * Quốc tịch khách
         *
         */
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $totalPax = isset($o['totalPax']) ? $o['totalPax'] : $total_pax;
        $nationality = isset($o['nationality']) ? $o['nationality'] : 0;
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : $nationality;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $place_id = isset($o['place_id']) ? $o['place_id'] : 0;
        $auto = isset($o['auto']) ? $o['auto'] : 0;
        $update = isset($o['update']) ? $o['update'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $position = isset($o['position']) ? $o['position'] : 0;
        $vehicle_id = isset($o['vehicle_id']) ? $o['vehicle_id'] : 0;
        $default = isset($o['default']) ? $o['default'] : false;
        $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        // Check quốc tịch -
        $pax_type = \app\modules\admin\models\Local::getTypeByNationality($nationality_id);
        $selected_car = [];
        if(isConfirm($auto)){
            
            // Lấy danh sách xe
            $query = new Query();
            $query->from(['a'=>'vehicles_categorys'])
            ->innerJoin(['b'=>'vehicles_to_cars'],'a.id=b.vehicle_id')
            ->where(['>','a.state',-2])
            ->andWhere(['a.type'=>$pax_type,
                'b.parent_id'=>$supplier_id,
                'b.is_active'=>1,
            ])
            
            //->andWhere(['in','a.id',(new Query())->from(['vehicles_to_cars'])->where([
            //		'parent_id'=>$supplier_id,
            //		'is_active'=>1,
            //		'is_default'=>1
            
            //])->select('vehicle_id')])
            ->select(['a.*' ,'maker_title'=>(new Query())->select('title')->from('vehicles_makers')->where('id=a.maker_id')])
            ->orderBy(['a.pmax'=>SORT_DESC]);
            if($vehicle_id>0){
                $query->andWhere(['id'=>$vehicle_id]);
            }else{
                $query->andWhere(['<=','a.pmin',$totalPax]);
                $query->andWhere(['b.is_default'=>1]);
            }
            $listCar = $query->all();
            
            $totalCar = 0;
            if(!empty($listCar)){
                foreach ($listCar as $k=>$v){
                    
                    $t = (int)($totalPax/$v['pmax']);
                    if($t > $totalCar && $totalCar>0){
                        break;
                    }
                    $totalCar = $t;
                    $selected_car[0] = $v;
                    $selected_car[0]['quantity'] = $t;
                }
                
                //
                $du_khach = $totalPax - ($selected_car[0]['quantity'] * $selected_car[0]['pmax']);
                if($du_khach < $selected_car[0]['pmax']){
                    if($du_khach > ($selected_car[0]['quantity'] * $selected_car[0]['factor'])){
                        $selected_car[0]['quantity'] ++;
                    }
                }else{
                    $selected_car[0]['quantity'] ++;
                }
                // Cập nhật cơ sở dữ liệu
                if(isConfirm($update)){
                    Yii::$app->db->createCommand()->delete('tours_programs_to_suppliers',
                    		[
                    				'supplier_id'=>$supplier_id,
                    				'item_id'=>$item_id,
                    				'segment_id'=>$segment_id                    				
                    		])->execute();
                    
                    Yii::$app->db->createCommand()->insert('tours_programs_to_suppliers',
                        [
                            'supplier_id'=>$supplier_id,
                            'item_id'=>$item_id,
                           // 'vehicle_id'=>$selected_car[0]['id'],
                           // 'quantity'=>$selected_car[0]['quantity'],
                            'type_id'=>TYPE_ID_VECL,
                            'position'=>$position,
                            'segment_id'=>$segment_id
                        ]
                        )->execute();
                }
                
            }
        }else{
            $query = new Query();
            $query->from(['a'=>'vehicles_categorys'])
            //->innerJoin(['b'=>'tours_programs_to_suppliers'],'a.id=b.vehicle_id')
            ->where(['>','a.state',-2])
            ->andWhere(['a.type'=>$pax_type])
            ->select(['a.*','maker_title'=>(new Query())->select('title')->from('vehicles_makers')->where('id=a.maker_id')])
            ;
            //view($query->createCommand()->getRawSql());
            $selected_car = $query->all();
            
        }
        
        
        if(empty($selected_car) && $default){
            return [
                ['id'=>0,'title'=>'Chọn phương tiện','quantity'=>0,'maker_title'=>'']
            ];
        }
        return $selected_car;
    }
    
    
    public function getVehicleAuto($o = []){
        /*
         * Số lượng khách
         * Quốc tịch khách
         *
         */
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $totalPax = isset($o['totalPax']) ? $o['totalPax'] : $total_pax;
        $nationality = isset($o['nationality']) ? $o['nationality'] : 0;
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : $nationality;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $place_id = isset($o['place_id']) ? $o['place_id'] : 0;
        $auto = isset($o['auto']) ? $o['auto'] : 0;
        $update = isset($o['update']) ? $o['update'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $position = isset($o['position']) ? $o['position'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : 0;
        $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        // Check quốc tịch -
        $pax_type = \app\modules\admin\models\Local::getTypeByNationality($nationality_id);
        $selected_car = [];
        if(isConfirm($auto)){
            
            // Lấy danh sách xe
            $query = new Query();
            $query->from(['a'=>'vehicles_categorys'])->where(['>','a.state',-2])->andWhere(['a.type'=>$pax_type])
            ->andWhere(['<=','a.pmin',$totalPax])
            ->andWhere(['in','a.id',(new Query())->from(['vehicles_to_cars'])->where([
                'parent_id'=>$supplier_id,
                'is_active'=>1,
                'is_default'=>1
                
            ])->select('vehicle_id')])
            ->select(['a.*','maker_title'=>(new Query())->select('title')->from('vehicles_makers')->where('id=a.maker_id')])
            ->orderBy(['a.pmax'=>SORT_DESC]);
            
            $listCar = $query->all();
            //$lk = [];
            $totalCar = 0;
            if(!empty($listCar)){
                foreach ($listCar as $k=>$v){
                    
                    $t = (int)($totalPax/$v['pmax']);
                    
                    if($t == 0){
                        $t = 1;
                        $totalCar = $t;
                        $selected_car[0] = $v;
                        $selected_car[0]['quantity'] = $t;
                        
                        
                    }
                    //
                    
                    $du_khach = $totalPax - ($t* $v['pmax']);
                    if($du_khach < $v['pmax']){
                        if($du_khach > ($t * $v['factor'])){
                            $t ++;
                        }
                    }else{
                        $t ++;
                    }
                    //view($totalCar);
                    //$lk[$k] = $t;
                    //
                    if($t > $totalCar && $totalCar>0){
                        
                        break;
                    }
                    $totalCar = $t;
                    $selected_car[0] = $v;
                    $selected_car[0]['quantity'] = $totalCar;
                    
                }
            }else{
                return [];
            }
            //
            
            // view($lk);
            
            
        }
        return $selected_car;
    }
    
    public static function getCurrencyByCode($id = 'VND'){
    	$list = \app\modules\admin\models\UserCurrency::getListCurrency();
        
        if( !empty($list)){
        	foreach ($list as $k=>$v){
                if($v['code'] == $id){
                    return $v;
                    break;
                }
            }
        }
    }
    
    public static function getCurrency($id = 1){       
        //return \app\modules\admin\models\UserCurrency::getItem($id);
    }
    
    
    
    public static function getDefaultCurrency(){
    	//return \app\modules\admin\models\UserCurrency::getDefaultCurrency();
    }
    
    public function showDropdownCurrency($o = []){
        
    }
    
    public static function showLang($lang = DEFAULT_LANG){
        $query = (new Query())->from('languages')->select('title');
        if(!is_numeric($lang)){
            $query->where(['code'=>$lang]);
        }else{
            $query->where(['id'=>$lang]);
        }
        //var_dump($query->createCommand()->getSql());
        return $query->scalar();
    }
    
    
    
    public static function showPrice($price = 0,$currency = -1, $showSymbol = true){
        $text_translate = 2;
        if(is_array($price)){
            
            $text_translate = isset($price['text_contact']) ? $price['text_contact'] : $text_translate;
            $price = isset($price['price']) ? $price['price'] : 0;
        }
        $currency = $currency == -1 ? self::getDefaultCurrency() : self::getCurrency($currency);
        if(!is_numeric($price)) $price = cprice($price);
        if(!($price != 0)){
            //f[products][prices][zero][vi-VN]
            $controller_code = is_array($price) && isset($price['controller_code']) ? $price['controller_code'] :
            (defined('CONTROLLER_CODE') ? CONTROLLER_CODE : false);
            //view(Yii::$app->config[$controller_code]);
            if(isset(Yii::$app->config[$controller_code]['prices']['zero'][__LANG__]) && Yii::$app->config[$controller_code]['prices']['zero'][__LANG__] != ""){
                return uh(Yii::$app->config[$controller_code]['prices']['zero'][__LANG__]);
            }
            return getTextTranslate($text_translate);
        }
        switch ($currency['display_type']){
        	
            case 1: $symbol = $currency['symbol'];break;
            default: $symbol = $currency['code']; break;
        }
        if(isset($currency['display']) && $currency['display'] == -1){
            $pre = $symbol;
            $after = '';
        }else{
            $pre = '';
            $after = $symbol;
        }
        if(!$showSymbol) {
            $pre = $after = '';
        }
        return $pre . number_format($price,$currency['decimal_number']) . $after;
    }
    
    public static function showPrices($price = 0,$currency = -1, $showSymbol = true){
        $currency = $currency == -1 ? self::getDefaultCurrency() : self::getCurrency($currency);
        if(!is_numeric($price)) $price = cprice($price);
        if(!($price>0)){
            return getTextTranslate(2);
        }
        switch ($currency['display_type']){
            case 2: $symbol = $currency['symbol'];break;
            default: $symbol = $currency['code']; break;
        }
        if(isset($currency['display']) && $currency['display'] == -1){
            $pre = $symbol;
            $after = '';
        }else{
            $pre = '';
            $after = $symbol;
        }
        if(!$showSymbol) {
            $pre = $after = '';
        }
        return $pre . number_format($price,$currency['decimal_number']) . $after;
    }
    
    public function showCurrency($id=1, $display_type = false){
        //$list = $this->getUserCurrency();
        
        $list = (Yii::$app->currencies->getUserCurrency()); 
         
        if(isset($list) && !empty($list)){
            foreach ($list as $k=>$v){
                if($v['id'] == $id){
                    break;
                }
            }
            switch ($display_type){
                case 3:
                    return $v['decimal_number'];
                    break;
                case 2: return $v['symbol'];break;
                case 1: return $v['code'];break;
                
            }
            if(isset($list['display_type'])){
                switch ($list['display_type']){
                    case 3:
                        return $v['decimal_number'];
                        break;
                    case 2: return $v['symbol'];break;
                    default: return $v['code']; break;
                }
            }
            return $v['code'];
        }
    }
    public function getOrderBy(){
        return [
            
            1=>'Tên / tiêu đề (a-z)',
            2=>'Tên / tiêu đề (z-a)',
            3=>'Thời gian (tăng)',
            4=>'Thời gian (giảm)',
            5=>'Giá (tăng)',
            6=>'Giá (giảm)',
            100=>'Ngẫu nhiên',
        ];
    }
    public function get_incurred_charge_type(){
        return array(
            array('id'=>0,'title'=>'Tính giá trực tiếp'),
            array('id'=>1,'title'=>'Tính giá phát sinh (%)'),
            array('id'=>2,'title'=>'Phụ thu tiền mặt'),
            //array('id'=>8,'title'=>'Tàu thuyền'),
        );
    }
    public function get_unit_prices(){
        return array(
            array('id'=>1,'title'=>'Phòng [Xe vận chuyển]'),
            array('id'=>2,'title'=>'Khách'),
            array('id'=>3,'title'=>'Đoàn'),
            //array('id'=>4,'title'=>'Tàu thuyền'),
        );
    }
    public function get_customer_type_code(){
        return array(
            array('id'=>0,'title'=>'-- không chọn --',),
            array('id'=>20,'title'=>'Doanh nghiệp tư nhân',),
            array('id'=>21,'title'=>'Doanh nghiệp nhà nước',),
            array('id'=>22,'title'=>'Công ty cổ phần'),
            array('id'=>23,'title'=>'Công ty TNHH',),
            array('id'=>24,'title'=>'Công ty hợp danh',),
            array('id'=>25,'title'=>'Công ty liên doanh',),
            array('id'=>26,'title'=>'Hợp tác xã',),
            array('id'=>27,'title'=>'Cá nhân',),
        );
    }
    //
    
    public function getCategorys($o=[]){
        return \app\models\SiteMenu::getList($o);
    }
    
    public function getBootstrapMenu($o = []){
        $html = '<nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Project name</a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li class="active"><a href="#">Home</a></li>
              <li><a href="#">About</a></li>
              <li><a href="#">Contact</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header">Nav header</li>
                  <li><a href="#">Separated link</a></li>
                  <li><a href="#">One more separated link</a></li>
                </ul>
              </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li class="active"><a href="./">Default <span class="sr-only">(current)</span></a></li>
              <li><a href="../navbar-static-top/">Static top</a></li>
              <li><a href="../navbar-fixed-top/">Fixed top</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
      </nav>';
        return $html;
    }
    
    public function getMenuItem($o=[]){
        $key = isset($o['key']) ? $o['key'] : false;
        $maxLevel = isset($o['maxLevel']) && $o['maxLevel'] > 0 && $o['maxLevel'] < 8 ? $o['maxLevel'] : 8;
        $attrs = isset($o['attribute']) ? $o['attribute'] : (isset($o['attrs']) ? $o['attrs'] : []);
        $showIconClass = isset($o['showIconClass']) && $o['showIconClass'] == false ? false : true;
        $showIconClass2 = isset($o['showIconClass2']) && $o['showIconClass2'] == false ? false : true;
        $a1Class = isset($o['a1Class']) ? $o['a1Class'] : '';
        $listItem = isset($o['listItem']) ? $o['listItem'] :
        \app\models\SiteMenu::getList([
            'key'=>$key
        ]);
        $m = ''; $cLevel = 0;
        //
        $htag = isset($o['htag']) ? $o['htag'] : [];
        
        //
        if($cLevel < $maxLevel && !empty($listItem)){
            $m .= '<ul ';
            if(!empty($attrs)){
                foreach($attrs as $a=>$t){
                    $m .= $a .'="'.$t.'" ';
                }
            }			$m .= '>';
            $cLevel = 1;
            $m .= isset($o['firstItem']) ? $o['firstItem'] : '';
            foreach ($listItem as $k=>$v){
                // Check child
            	$cLevel = 1;
                $l1 = \app\models\SiteMenu::getList([
                    'parent_id'=>$v['id']
                ]);
                 
                $li1Class = !empty($l1) ? (isset($o['li1WithChildClass']) ? $o['li1WithChildClass'] : '') : (isset($o['li1NotChildClass']) ? $o['li1NotChildClass'] : '');
                //$liHasChild =
                $liActive = isset($o['activeClass']) && isset($o['activeClass']['li']) && in_array($v['url'],Yii::$app->request->get()) ? $o['activeClass']['li'] : '';
                $aActive = isset($o['activeClass']) && isset($o['activeClass']['a']) && in_array($v['url'],Yii::$app->request->get()) ? $o['activeClass']['a'] : '';
                $m .= '<li data-id="'.$v['id'].'" data-child="'.count($l1).'" class="li-child li-child-'.$k.' li-level-'.$cLevel.' '. $liActive.' '.(isset($o['li1Class']) ? $o['li1Class'] : '').' '.$li1Class.'">';
                if(isset($v['url_link'])){
                    $link = $v['url_link'];
                }else{
                    $link = $v['type'] == 'link' ? $v['link_target'] : cu([DS.$v['url']]);
                }
                $m .= '<a '.(isset($v['rel']) ? ' rel="'.$v['rel'].'"' : '').' '.(isset($v['target']) ? ' target="'.$v['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'  class="'.$aActive.' '.$a1Class.'">';
                
                if($showIconClass && isset($v['icon_class']) && $v['icon_class'] != ""){
                    $m .= '<i class="'.$v['icon_class'].'"></i> ';
                }
                
                $m .= isset($htag[0]) && $htag[0] != "" ? '<' . $htag[0] .'>' : '';
                $m .= uh($v['title']);
                $m .= isset($htag[0]) && $htag[0] != "" ?'</' . $htag[0] .'>' : '';
                $m .= '</a>';
                if($cLevel < $maxLevel && !empty($l1)){
                    $cLevel = 2;
                    
                    $m .= (isset($o['preUl2']) ? $o['preUl2'] : '');
                    
                    $m .= '<ul ';
                    if(isset($o['ul2Attr']) && !empty($o['ul2Attr'])){
                        foreach ($o['ul2Attr'] as $a=>$t){
                            $m .= $a .'="'.$t.'" ';
                        }
                    }
                    $m .= '>';
                    foreach ($l1 as $k1=>$v1){
                    	$cLevel = 2;
                        $l2 = \app\models\SiteMenu::getList([
                            'parent_id'=>$v1['id']
                        ]);
                        //$link = $v1['type'] == 'link' ? $v1['link_target'] : cu([DS.$v1['url']]);
                        if(isset($v1['url_link'])){
                            $link = $v1['url_link'];
                        }else{
                            $link = $v1['type'] == 'link' ? $v1['link_target'] : cu([DS.$v1['url']]);
                        }
                        
                        $m .= '<li data-id="'.$v1['id'].'" data-child="'.count($l2).'" class="li-child li-child-'.$k1.' li-level-'.$cLevel.' '.(isset($o['li2Class']) ? $o['li2Class'] : '').'">';
                        $m .= '<a '.(isset($v1['rel']) ? ' rel="'.$v1['rel'].'"' : '').' '.(isset($v1['target']) ? ' target="'.$v1['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'>';
                        $m .= isset($o['a2Pre']) ? $o['a2Pre'] : '';
                        
                        if($showIconClass2 && isset($v1['icon_class']) && $v1['icon_class'] != ""){
                            $m .= '<i class="'.$v1['icon_class'].'"></i> ';
                        }
                        
                        $m .= uh($v1['title']);
                        $m .= isset($o['a2After']) ? $o['a2After'] : '';
                        //$m .= $eTag[0];
                        $m .= '</a>';
                        
                        if($cLevel < $maxLevel && !empty($l2)){
                            $cLevel = 3;
                            
                            $m .= '<ul >';
                            foreach ($l2 as $k2=>$v2){
                            	$cLevel = 3;
                                $l3 = \app\models\SiteMenu::getList([
                                    'parent_id'=>$v2['id']
                                ]);
                                //$link = $v2['type'] == 'link' ? $v2['link_target'] : cu([DS.$v2['url']]);
                                if(isset($v2['url_link'])){
                                    $link = $v2['url_link'];
                                }else{
                                    $link = $v2['type'] == 'link' ? $v2['link_target'] : cu([DS.$v2['url']]);
                                }
                                $m .= '<li data-id="'.$v2['id'].'" data-child="'.count($l3).'" class="li-child li-child-'.$k2.' li-level-'.$cLevel.'">';
                                $m .= '<a '.(isset($v2['rel']) ? ' rel="'.$v2['rel'].'"' : '').' '.(isset($v2['target']) ? ' target="'.$v2['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'>';
                                //$m .= $hTag[0];
                                $m .= uh($v2['title']);
                                //$m .= $eTag[0];
                                $m .= '</a>';
                                
                                if($cLevel < $maxLevel && !empty($l3)){
                                    $cLevel = 4;
                                    
                                    $m .= '<ul >';
                                    foreach ($l3 as $k3=>$v3){
                                    	$cLevel = 4;
                                        $l4 = \app\models\SiteMenu::getList([
                                            'parent_id'=>$v3['id']
                                        ]);
                                        //$link = $v3['type'] == 'link' ? $v3['link_target'] : cu([DS.$v3['url']]);
                                        if(isset($v['url_link'])){
                                            $link = $v3['url_link'];
                                        }else{
                                            $link = $v3['type'] == 'link' ? $v3['link_target'] : cu([DS.$v3['url']]);
                                        }
                                        $m .= '<li data-id="'.$v3['id'].'" data-child="'.count($l4).'" class="li-child li-child-'.$k3.' li-level-'.$cLevel.'">';
                                        $m .= '<a '.(isset($v3['rel']) ? ' rel="'.$v3['rel'].'"' : '').' '.(isset($v3['target']) ? ' target="'.$v3['target'].'"' : '').' '.($link != '#' ? 'href="'.$link.'"' : 'role="none"').'>';
                                        //$m .= $hTag[0];
                                        $m .= uh($v3['title']);
                                        //$m .= $eTag[0];
                                        $m .= '</a>';
                                        
                                        
                                        $m.= '</li>';
                                    }
                                    $m .= '</ul>';
                                }
                                
                                
                                $m.= '</li>';
                            }
                            $m .= '</ul>';
                        }
                        
                        $m.= '</li>';
                    }
                    $m .= '</ul>';
                    //
                    $m .= (isset($o['afterUl2']) ? $o['afterUl2'] : '');
                }
                
                $m.= '</li>';
                // after ul1                
            }
            if(isset($o['afterUl1'])){
                $m.= $o['afterUl1'];
            }
            $m .= '</ul>';
        }
        return $m;
    }
    
    public function getAdvert($o = []){
        $type = isset($o['type']) && is_numeric($o['type']) ? $o['type'] : -1;
        $category_id = isset($o['category_id']) && is_numeric($o['category_id']) ? $o['category_id'] : -2;
        $box_id = isset($o['box_id']) && is_numeric($o['box_id']) ? $o['box_id'] : -1;
        $lang = isset($o['lang']) ? $o['lang'] : __LANG__;
        $code = isset($o['code']) ? $o['code'] : false;
        $index = isset($o['index']) ? $o['index'] : false;
        $is_all = isset($o['is_all']) ? $o['is_all'] : -1;
        if($index){
            if($category_id  == -1){
                //$category_id = __CATEGORY_ID__;
            }
        }
        $orderBy = isset($o['orderBy']) ? $o['orderBy'] : ['a.position'=>SORT_ASC,'a.title'=>SORT_ASC];
        $query = (new Query())
        ->select(['a.*'])
        ->from(['a'=>'{{%adverts}}'])
        ->where(['a.is_active'=>1])
        ->andWhere(['>','a.state',-2]);
        if($lang !== false){
            $query->andWhere(['a.lang'=>$lang]);
        }
        if($is_all == 1 && $code !== false){
            
        }else{
            $query->andWhere(['a.sid'=>__SID__]);
        }
        if($code !== false){
            $type = -1;
            $query->addSelect(['category_title'=>'b.title'])
            ->innerJoin(['b'=>'{{%adverts_category}}'],'a.type=b.id')
            ->andWhere(['b.code'=>$code,'b.is_active'=>1,'b.is_'.Yii::$app->device=>1]);
            if($is_all == 1){
                $query->andWhere(['b.is_all'=>1,'b.sid'=>0]);
            }
        }
        if($type > -1){
            $query->andWhere(['a.type'=>$type]);
        }
        if($category_id > -2){
            $query->andWhere(['a.category_id'=>$category_id]);
        }
        if($box_id > -1){
            $query->andWhere(['a.box_id'=>$box_id]);
        }
        
        //view($query->createCommand()->getRawSql());
        return $query->orderBy($orderBy)->all();
        
    }
    public function getArticles($o = []){
        $rs = $vb = [];
        $count = $limit = 0; $p = 1; $key = false;
        
        
        /* Check option
         *
         */
        $key = isset($o['key']) ? $o['key'] : '';
        $id = isset($o['id']) && $o['id'] > 0 ? $o['id'] : 0;
        $box_id = isset($o['box_id']) && $o['box_id'] > 0 ? $o['box_id'] : 0;
        $box_code = isset($o['box_code']) ? $o['box_code'] : '';
        $box = isset($o['box']) && is_array($o['box']) ? $o['box'] : [];
        $url = isset($o['url']) && $o['url'] != "" ? $o['url'] : defined('__DETAIL_URL__') ? __DETAIL_URL__ : '';
        
        $type =  isset($o['type']) ? $o['type'] : ''; 
        $category = isset($o['category']) ? $o['category'] :
        (defined('__CATEGORY_ID__') ? __CATEGORY_ID__ : 0);
        $category_id = isset($o['category_id']) ? $o['category_id'] : $category;
        $departure = isset($o['departure']) ? $o['departure'] : -1;
        $price_range = isset($o['price_range']) ? $o['price_range'] : '';
        $other =  isset($o['other']) ? $o['other'] : false;
        $tag =  isset($o['tag']) ? $o['tag'] : false;
        $detail = isset($o['detail']) && $o['detail'] == true ? true : false;
        $search = isset($o['search']) && $o['search'] == true ? true : false;
        $barcode = isset($o['barcode']) ? $o['barcode'] : '';
        $sort_subtitle = isset($o['sort_subtitle']) && $o['sort_subtitle'] == true ? true : false;
        $tabs = isset($o['tabs']) && $o['tabs'] == true ? true : false;
        $check_dateprice = isset($o['check_dateprice']) && $o['check_dateprice'] == false ? false : true;
        $orderby = isset($o['orderby']) ? $o['orderby'] : false;
        $sort = isset($o['sort']) ? $o['sort'] : $orderby;
        
        $attr = isset($o['attr']) ? $o['attr'] : false;
        $box = !empty($box) ? $box : (strlen($key)>0 ?  \app\models\Box::getBox($key) : []);
        //view($box);
        $check_box_code = isset($o['check_box_code']) && $o['check_box_code'] == true ? true : false;
        if($check_box_code && empty($box)) return false;
        //
        $p = isset($o['p']) && $o['p'] > 1 ? $o['p'] : 1;
        //view($o);
        $limit = isset($o['limit']) && $o['limit'] > 0 ? $o['limit'] : 0;
        $limit = $limit > 0 ? $limit : (!empty($box) && $box['limit'] > 0 ?  $box['limit'] : $limit);
        $limit = $limit > 0 ? $limit : 12;
        //view($limit);
        //view($box);
        $action_detail = isset($o['action_detail']) ? $o['action_detail']  : '';
        $count = isset($o['count']) && $o['count'] == true ? true : false;
        $igrone = isset($o['igrone']) ? $o['igrone'] : false;
        $offset = ($p-1) * $limit;
        $filter_tour_group = isset($o['filter_tour_group']) && $o['filter_tour_group'] > -1 ? $o['filter_tour_group'] : -1;
        
        $filters = [];
        
        // Check type
        if($type == 'auto'){
            //$type = $category_id > 0 ?  $this->_get_category_type($category,$url) : $this->_get_article_type($id,$url);
            $type = Slugs::getRoute($url, $category_id > 0 ? $category_id : $id,-1);
        }
        
        $vb = $box;
        if($box_id > 0){
            $vb =\app\models\Box::getBox($box_id);
            switch ($type){
                case 'tours':
                    
                    $fb = [];// \app\modules\admin\models\Box::getFilterExisted($box_id);
                    if(!empty($fb)){
                        $o['tour_category'] = $fb;
                        //view($fb);
                    }
                    break;
            }
        }
        $c['listItem']= [];
        $list_orderby = [$sort];
        // Check dl tu box
        if(!empty($vb)){
        	$c['listItem'] = $result = [];
            //$limit = $vb['limit'];
            
            
            if(isset($vb['form']) && $vb['form'] != ""){
                $type = $vb['form'];
            }
            if(isset($vb['attr']) && !empty($vb['attr'])){
                $attr = $vb['attr'];
            }
            if($vb['menu_id'] > 0){
                $m = \app\models\SiteMenu::getItem($vb['menu_id']);
                if(!empty($m)){
                    $type = $m['type'];
                    $action_detail = isset($m['action_detail']) && $m['action_detail'] != "" ? $m['action_detail'] : $action_detail;
                    $category_id = $vb['menu_id'];
                }
            }
            
            if(isset($vb['articles_list']) && !empty($vb['articles_list'])){
            	$c['listItem'] = $vb['articles_list'];
                
            } else{
            	$c['listItem'] = isset($m['listItem']) && !empty($m['listItem']) ? $m['listItem'] : $c['listItem'];
                
            }
            if(isset($vb['filter_by']) && !empty($vb['filter_by'])){
                $filters += is_array($vb['filter_by']) ? $vb['filter_by'] : [$vb['filter_by']];
            }
            
            if(isset($vb['order_by']) && !empty($vb['order_by'])){
                $sort = $vb['order_by'][0];
                $list_orderby = $vb['order_by'];
            }
            
            //$filters += \app\modules\admin\models\Box::getFilterExisted($vb['id'],'id');
            
        }
        //
        ///view($box_id);
        //view($c);
        $query = (new Query())->select(['a.*'])->from(['a'=>Articles::tableName()])
        ->where(['a.is_active'=>1, 'a.sid'=>__SID__])
        ->andWhere(['>','a.state',-2]);
        
        if($barcode != ""){
        	
        }
        
        if(!$detail){
            $query->andWhere(['a.is_invisibled'=>0]);
        }
        //
        switch ($type){
            case 'tours':
                $query->addSelect(['b.*'])
                ->innerJoin(['b'=>'{{%tours_attrs}}'],'a.id=b.item_id');
                break;
        }
        //
        if(isset($o['course_id'])){
            $query->andWhere(['a.id'=>(new Query())->from(['item_to_courses'])->where(['course_id'=>$o['course_id']])->select(['item_id'])]);
        }
        
        switch ($action_detail){
            case '{get_all_tour_1}': // Du lich trong nuoc
                //$where = "b.tour_type=1";
                //$query->andWhere(['b.tour_type'=>1]);
                break;
            case '{get_all_tour_2}': // Du lich nuoc ngoai
                //$where = "b.tour_type=2";
                //$query->andWhere(['b.tour_type'=>2]);
                break;
            case '{get_hot_item}': // Get item hot
                $attr = 'is_hot';
                break;
            default:
                if($detail){
                    if($id > 0){
                        $query->andWhere(['a.id'=>$id]);
                    }else{
                        $query->andWhere(['a.url'=>$url]);
                    }
                    
                }else{
                    $query->andWhere(['a.type'=>$type]);
                    //view($category_id);
                    if($category_id > 0){
                        $subQuery = (new Query())->select(['item_id'])->from(['{{%items_to_category}}'])->where([
                            'in','category_id',SiteMenu::getAllChildID($category_id)
                        ]);
                        
                        //$query->andWhere(['a.id'=>$subQuery]);
                        
                        
                        if(isset($c['listItem']) && !empty($c['listItem'])){
                        	//$query->orWhere(['in','a.id',$c['listItem']]);
                        	$query->andWhere(['or',[
                        			'a.id'=>$subQuery
                        	],[
                        			'a.id'=>$c['listItem']
                        	]]);
                        }else{
                        	$query->andWhere(['a.id'=>$subQuery]);
                        }
                        
                    }else{
                    	if(isset($c['listItem']) && !empty($c['listItem'])){
                    		$query->andWhere(['in','a.id',$c['listItem']]);
                    	}
                    	
                    }
                }
                break;
        }
        $recent = false;
        // Check Attr
        if($attr == 'recent'){
            $recent = true;  $attr = false;
        }
        
        if($attr !== false){
            if(is_array($attr) && !empty($attr)){
                $vtx = [];
                foreach ($attr as $kt=>$at){
                    if($at == 'recent'){
                        $recent = true;
                        unset($attr[$kt]);
                    }else{
                        $vt = $this->_get_item_id_by_attr($at);
                        $existed = [];
                        if(!empty($vt)){
                            foreach ($vt as $vv){
                                if($kt == 0){
                                    $vtx[] = $vv['item_id'];
                                }
                                $existed[] = $vv['item_id'];
                                
                            }
                        }
                        if(!empty($vtx)){
                            foreach ($vtx as $kt=>$vt){
                                if(!in_array($vt, $existed)){
                                    unset($vtx[$kt]);
                                }
                            }
                        }}
                        
                }
                if(empty($vtx)){
                    $vtx = array(0);
                }
                if(!$recent) $query->andWhere(['in','a.id',$vtx]);
                
            }else{
                $subQuery = (new Query())->select('g.item_id')->from(['g'=>'{{%articles_to_attrs}}'])
                ->innerJoin(['h'=>Articles::tableName()],'g.item_id=h.id')
                ->where(['h.sid'=>__SID__,'g.state'=>1,'g.attr_id'=>$attr]);
                $query->andWhere(['in','a.id',$subQuery]);
                
            }
        }
        //
        
        //
        
        if($search){
            $q = isset($o['q']) ? $o['q'] : getParam('q');
            if(strlen($q) > 1){
                $query->andWhere("a.code like '%$q%' or a.url like '%".unMark($q)."%' or a.short_title like '%".$q."%'");
                //$where .= " and (a.code like '%$q%' or a.url like '%".unMark($q)."%' or a.short_title like '%".$q."%')";
            }
        }
        //
        if($other != false){
            $query->andWhere(['not in','a.id',$other]);
        }
        // Filters
        
        $filter_tour_type = isset($o['filter_tour_type']) ? $o['filter_tour_type'] : '';
        $filter_location = isset($o['filter_location']) ? $o['filter_location'] : '';
        $filter_category = isset($o['filter_category']) ? $o['filter_category'] : '';
        $filter_radio = isset($o['filter_radio']) ? $o['filter_radio'] : '';
        $filters_xx = isset($o['filters']) ? $o['filters'] : '';
        if($filter_location != "" && !is_array($filter_location)){
            $filter_location = explode(',', $filter_location);
        }
        if($filters_xx != "" && !is_array($filters_xx)){
            $filters_xx = explode(',', $filters_xx);
        }
        if(is_array($filter_location) && !empty($filter_location)){
            foreach ($filter_location as $a){
                if($a > 0) $filters[] = $a;
            }
        }
        if(is_array($filters_xx) && !empty($filters_xx)){
            foreach ($filters_xx as $a){
                if($a > 0) $filters[] = $a;
            }
        }
        if($filter_radio != "" && !is_array($filter_radio)){
            $filter_radio = explode(',', $filter_radio);
        }
        if(is_array($filter_radio) && !empty($filter_radio)){
            foreach ($filter_radio as $a){
                if($a > 0) $filters[] = $a;
            }
        }
        
        if($filter_tour_type != "" && !is_array($filter_tour_type)){
            $filter_tour_type = explode(',', $filter_tour_type);
        }
        $filter_tour_type_value = 0;
        if(is_array($filter_tour_type) && count($filter_tour_type) == 1){
            if($filter_tour_type[0] > 0){
                $filters[] = $filter_tour_type[0];
                $ftx = Filters::getItem($filter_tour_type[0]);// $this->getFilters(array('id'=>$filter_tour_type[0],'parent_id'=>-1,'query'=>'Row'));
                if(!empty($ftx)){
                    $filter_tour_type_value = $ftx['value'];
                }
            }
        }elseif(is_array($filter_tour_type) && !empty($filter_tour_type)){
            foreach ($filter_tour_type as $a){
                if($a > 0) $filters[] = $a;
            }
        }
        /*/
         $spc = isset($o['spc']) ? $o['spc'] : 0;
         switch ($type){
         case 'tours':
         if($spc > 0 && $spc<3){
         $filters[] = \app\models\Filters::getFilterFromValue($spc, 'tour_category');
         }
         break;
         }
         
         /*/
        
        
        
        
        if(!empty($filters)){
            $fArrays = Filters::getFilters(['id'=>$filters,'parent_id'=>-1,'select'=>['a.id','a.menu_id','a.code','a.value','a.value1']]);
            $f1 = $f2 = [];
            if(!empty($fArrays)){
                foreach ($fArrays as $f){
                    switch ($f['code']){
                        case 'filter_prices':
                            $query->andWhere(['between','a.price2',$f['value'],$f['value1']]);
                            break;
                        default:
                            if($f['menu_id'] > 0){
                                $fxs = SiteMenu::getAllChildID($f['menu_id']);
                                if(!empty($fxs)){
                                    foreach ($fxs as $fx){
                                        $f1[] = $fx;
                                    }
                                }
                            }else{
                                $f2[] = $f['id'];
                            }
                            break;
                    }
                    
                    
                    
                    
                }
            }
            
            if(!empty($f1)){
                $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('items_to_category')->where(['in','category_id',$f1])->groupBy('item_id')]);
            }
            if(!empty($f2)){
                $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where(['in','filter_id',$f2])]);
            }
            
        }
        
        if($filter_category != "" && !is_array($filter_category)){
            $filter_category = explode(',', $filter_category);
        }
        if(is_array($filter_category) && !empty($filter_category)){
            $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('items_to_category')->where(['in','category_id',SiteMenu::getAllChildID($filter_category)])]);
            
        }
        $factor = 1; $price_begin = $price_end = 0;
        $price_range = $price_range != "" ? explode(',', $price_range) : [];
        /*/ Check price range
         $price_range = $price_range != "" ? explode(';', $price_range) : [];
         switch (__LANG__){
         case 'vi-VN':
         $price_begin = 1000;
         $price_end = 20000;
         $factor = 1000;
         break;
         default:
         $price_begin = 100;
         $price_end = 10000;
         $factor = 1;
         break;
         }
         /*/
        $p1 = $p2 = 0;
        if(!empty($price_range) && count($price_range) > 1){
            $p1 = $price_range[0] > $price_begin ? $price_range[0] : 0;
            $p2 = $price_range[1] > $price_begin ? $price_range[1] : 0;
            $p2 = $p2 == $price_end ? 0 : $p2;
            if($p2>$p1){
                $query->andWhere(['between','price2',$p1*$factor,$p2*$factor]);
                //$where .= " and (a.price2 between $p1*$factor and $p2*$factor)";
            }
        }
        //view($price_range);
        
        //
        switch ($type){
            case 'tours':
                $tstart = isset($o['tour_start']) ? $o['tour_start'] : 0;
                $destination = isset($o['destination']) ? $o['destination'] : 0;
                $tour_destination = isset($o['tour_destination']) ? $o['tour_destination'] : 0;
                $tour_group = isset($o['tour_guest_group']) ? $o['tour_guest_group'] : 0;
                $tour_type = isset($o['tour_type']) ? $o['tour_type'] : 0;
                $date_departure = isset($o['date_departure']) ? $o['date_departure'] : '';
                $tour_date_time = isset($o['tour_date_time']) ? $o['tour_date_time'] : '';
                $time = isset($o['time']) ? $o['time'] : getParam('time');
                $rating_service = isset($o['rating_service']) ? $o['rating_service'] : -1;
                $filters_tour_type = isset($o['filters_tour_type']) ? $o['filters_tour_type'] : -1;
                $time = explode('-', $time);
                $d = isset($time[0]) && $time[0] > 0 ? $time[0] : 0;
                $n = isset($time[1]) && $time[1] > 0 ? $time[1] : 0;
                
                $price = isset($o['price']) ? $o['price'] : getParam('price');
                $price = explode('-', $price);
                $pfr = isset($price[0]) && $price[0] > 0 ? $price[0] : 0;
                $pto = isset($price[1]) && $price[1] > 0 ? $price[1] : 999999;
                
                $pfr = $pfr * 1000000; $pto = $pto * 1000000;
                
                //$ft_x = [];
                
                if($tstart>0){
                    //$query->andWhere(['b.tour_type'=>$tour_type]);
                    $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where([
                        'filter_id'=>$tstart
                    ])]);
                }
                if($tour_destination>0){
                    //$query->andWhere(['b.tour_type'=>$tour_type]);
                    $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where([
                        'filter_id'=>$tour_destination
                    ])]);
                }
                if($destination>0){
                    $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('tours_to_destinations')->where(['destination_id'=>$destination,'type'=>0])]);
                }
                //if($tour_group>0){
                //	$query->andWhere(['b.tour_group'=>$tour_group]);
                //}
                if($tour_type>0){
                    //	$query->andWhere(['b.tour_type'=>$tour_type]);
                    $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where([
                        'filter_id'=>$tour_type
                    ])]);
                }
                
                
                $tour_type_value = isset($o['tour_type_value']) ? $o['tour_type_value'] : -1;
                if($tour_type_value>0){
                    //$query->andWhere(['b.tour_type'=>$tour_type]);
                    $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where([
                        'filter_id'=>(new Query())->select('id')->from('filters')->where([
                            'code'=>'tour_type',
                            'sid'=>__SID__,
                            'value'=>$tour_type_value
                        ])
                    ])]);
                    
                    
                }
                if($d>0){
                    $query->andWhere(['b.day'=>$d]);
                }
                
                if($n>0){
                    $query->andWhere(['b.night'=>$night]);
                }
                if($pfr>0){
                    $query->andWhere(['between','a.price2',$pfr,$pto]);
                }
                
                if(check_date_string($tour_date_time)){
                    $tour_date_time = ctime(['string'=>$tour_date_time,'format'=>'d/m/Y']);
                    $query->andWhere(['in','a.id',(new Query())->select('item_id')->from('articles_to_filters')->where([
                        'filter_id'=>(new Query())->select('id')->from('filters')->where([
                            'code'=>'tour_date_time',
                            'sid'=>__SID__,
                            'title'=>$tour_date_time
                        ])
                    ])]);
                }
                 
                if(is_numeric($departure) && $departure>0){
                    $query->andWhere(['or',['b.start'=>$departure]],['a.id'=>(new Query())->select('item_id')->from('tours_to_destinations')->where(['destination_id'=>$departure,'type'=>2])]);
                    //$where .= " and (b.start=$departure or a.id in(select item_id from {$this->table('tours_to_destinations')} where destination_id=$departure and type=2)) ";
                }
                
                if($check_dateprice && check_date_string($date_departure)){
                    //view($date_departure);
                    $date_departure_time = convertTime($date_departure,'Y-m-d',1);
                     
                }
                //echo $where;
                $query->addSelect(['price3'=>(new Query())->select('price')->from('articles_prices')->where([
                    'item_id'=>(new \yii\db\Expression('a.id')),
                    //'code'=>
                ])->limit(1)]);
                
                $expression = "CASE
						(select `value` from filters where code='tour_type'
						and id in(select filter_id from articles_to_filters where item_id=a.id and state=1))
						when 2
						then 'tour_type_2_%'
						when 5
						then 'tour_type_5_%'
						when 3 then 'tour_hotel_%'
						else 'tour_date_time_%'
						END
						";
                
                
                $query->addSelect(['price2'=>(new Query())
                	->select('price')
                	->from('articles_prices')
                    ->where([
                        'item_id'=>(new \yii\db\Expression('a.id')),
                        'state'=>1
                        //'code'=>(new \yii\db\Expression($expression))
                    ])
                		->andWhere(['like','code',(new \yii\db\Expression($expression)) ])
                		//->andWhere("if(1>0,unix_timestamp(select SUBSTRING(code from 16)),unix_timestamp(now())+86400) > unix_timestamp(now()) ")	
                    ->limit(1)->orderBy(['code'=>SORT_DESC])]);
                
              // view($query->createCommand()->getRawSql());    
                
                break;
            default:
                break;
        }
        
        
        
        if(isset($o['tour_category']) ){
            //view($o['tour_category']);
            if(is_numeric($o['tour_category']) && $o['tour_category']>0){
                $o['tour_category'] = [$o['tour_category']];
            }
            if(!empty($o['tour_category'])){
                $query->andWhere(['a.id'=>(new Query())->select('item_id')->from('articles_to_filters')->where([
                    'filter_id'=>$o['tour_category']
                    //(new Query())->select('id')->from('filters')->where(['code'=>'tour_category','value'=>$o['tour_category']])
                ])]);
            }
        }
        if(isset($o['tour_category_value']) ){
            //view($o['tour_category']);
            if(is_numeric($o['tour_category_value']) && $o['tour_category_value']>0){
                $o['tour_category_value'] = [$o['tour_category_value']];
            }
            if(!empty($o['tour_category_value'])){
                $query->andWhere(['a.id'=>(new Query())->select('item_id')->from('articles_to_filters')->where([
                    'filter_id'=>
                    (new Query())->select('id')->from('filters')->where(['code'=>'tour_category','value'=>$o['tour_category_value']])
                ])]);
            }
        }
        
        
        
        /*/
         1=>'Tên / tiêu đề (a-z)',
         2=>'Tên / tiêu đề (z-a)',
         3=>'Thời gian (tăng)',
         4=>'Thời gian (giảm)',
         5=>'Giá (tăng)',
         6=>'Giá (giảm)',
         100=>'Ngẫu nhiên',
         /*/
        $order_array = []; $order_rand = false;
        if(!empty($list_orderby)){
            foreach ($list_orderby as $order){
                switch ($order){
                    case 4: // Mới nhất
                        $order_array['a.updated_at'] = SORT_DESC;
                        break;
                    case 3: // Cux nhất
                        $order_array['a.updated_at'] = SORT_ASC;
                        break;
                    case 6: // Giá cao - thấp
                        //$order = ' a.price2 desc,a.position, a.time DESC';
                        $order_array['a.price2'] = SORT_DESC;
                        
                        break;
                    case 5: // Giá thấp - cao
                        //$order = ' a.price2 asc,a.position, a.time DESC';
                        $order_array['a.price2'] = SORT_ASC;
                        break;
                    case 1: // Tên a - z
                        //$order = ($sort_subtitle ? ' a.short_title asc,a.title asc, a.position, a.time DESC' : ' a.title asc, a.position, a.time DESC') ;
                        //$order_array[] =($sort_subtitle ? ['a.short_title'=>SORT_ASC,
                        //'a.title'=>SORT_ASC,'a.position'=>SORT_ASC,'a.time'=>SORT_DESC] : ['a.title'=>SORT_ASC,'a.position'=>SORT_ASC,'a.time'=>SORT_DESC]);
                        if($sort_subtitle){
                            $order_array['a.short_title'] = SORT_ASC;
                            $order_array['a.title'] = SORT_ASC;
                        }else{
                            $order_array['a.title'] = SORT_ASC;
                        }
                        break;
                    case 2: // Tên z - a
                        //$order_array[] =($sort_subtitle ? ['a.short_title'=>SORT_DESC,'a.title'=>SORT_DESC,'a.position'=>SORT_ASC,'a.time'=>SORT_DESC] : ['a.title'=>SORT_DESC,'a.position'=>SORT_ASC,'a.time'=>SORT_DESC]);
                        if($sort_subtitle){
                            $order_array['a.short_title'] = SORT_DESC;
                            $order_array['a.title'] = SORT_DESC;
                        }else{
                            $order_array['a.title'] = SORT_DESC;
                        }
                        break;
                    case 100:
                        //$order_array = new \yii\db\Expression('rand()');
                        $order_rand = true;
                        
                        break;
                        
                }
            }
        }
        
        $order_array['a.position'] = SORT_ASC;
        if(!$order_rand && !isset($order_array['a.time'])){
            $order_array['a.updated_at'] = SORT_DESC;
        }
        
        
        
        if($tag !== false){
            //&& substr($tag, 0,4) == 'tag-'){
            $tag_id = substr($tag, 4);
            if(is_numeric($tag_id)){
                $query->andWhere(['a.id'=>(new Query())->select('item_id')->from('item_to_tag')->where(['tag_id'=>$tag_id])]);
            }else{
                $query->andWhere(['a.id'=>(new Query())->select('item_id')->from('item_to_tag')->where(['tag_id'=>0])]);
            }
        }
        
        if($order_rand){
            $order_array = new \yii\db\Expression('rand()');
        }
        $query->orderBy($order_array);
        if($recent){
            //view($query->createCommand()->getRawSql());
            $cookies1 = Yii::$app->request->cookies;
            $r = $cookies1->getValue('recent_viewed', []);
            $query->andWhere(['a.id'=>(array_slice($r, 0, $limit))]);
        }
        
        $query->addSelect(['post_by_name'=>'concat(z.lname, \' \' , z.fname)']);
        $query->leftJoin(['z'=>'{{%users}}'],'a.created_by=z.id');
        
        if(!$detail){
            $query->andWhere(['a.lang'=>__LANG__]);
        }
        
        
        $count = $query->count(1);
        
        $query->offset($offset);
        if($limit>0) $query->limit($limit);
        
        //view($query->createCommand()->getRawSql()); 
        
        $rs = $query->all();
        // view($rs);
        if($detail ){
            if(!empty($rs)){
                if($tabs){
                    $rs[0]['tabs'] = $this->getDetailTabs($rs[0]['id']);
                }
                return $rs[0];
            }else{
                //view($query->createCommand()->getRawSql());
                return [];
            }
        }
        return array(
            'listItem'=>$rs,
            'totalItem'=>$count,
            'total_record'=>$count,
            'total_records'=>$count,
            'total_pages'=> $limit > 0 ? ceil($count/$limit) : 1,
            'totalPage'=> $limit > 0 ? ceil($count/$limit) : 1,
            'p'=>$p,'key'=>$key,
            'limit'=>$limit,
            'box'=>$vb
        );
    }
    
    public function getFilterTourType1NextDay($item_id){
    	//$ex = new \yii\db\Expression(" REPLACE(`code`, \"tour_date_time_\", \"\")");
    	$query = (new Query())->from(['a'=>'filters'])
    	->innerJoin(['b'=>'articles_to_filters'],'a.id=b.filter_id')
    	->where(['b.item_id'=>$item_id,'a.code'=>'tour_date_time'])
    	->andWhere(['>','b.to_date',date('Y-m-d H:i:s')]) 
    	->orderBy(['b.to_date'=>SORT_ASC])->limit(1)
    	;
    	return $query->one();
    }
    
    private static function _get_item_id_by_attr($attr = ''){
        return (new Query())
        ->select('a.item_id')
        ->from(['a'=>'{{%articles_to_attrs}}'])
        ->innerJoin(['b'=>'{{%articles}}'],'a.item_id=b.id')
        ->where([
            'b.sid'=>__SID__,
            'a.state'=>1,
            'a.attr_id'=>$attr
        ])->all();
    }
    ////////////
    public function getBoxIndex($o = []){
        if(!is_array($o)){
            $type = $o;
            $o = [];
        }
        $attr = isset($o['attr']) ? $o['attr'] : false;
        $type = isset($o['type']) ? $o['type'] : 'products';
        $module = isset($o['module']) ? $o['module'] : 'index';
        $listSubMenu = isset($o['listSubMenu']) && $o['listSubMenu'] == true ? true : false;
        $limitSub= isset($o['limitSub']) ? $o['limitSub'] : 0;
        
        
        $list_box = \app\models\Box::getBoxIndex($module, $o);
        //view($list_box);
        
        $action_detail = '';
        $result = [];
        if(!empty($list_box)){
            foreach($list_box as $kb=>$vb){
                $r = $this->getArticles([
                    'box'=>$vb,
                    'category_id'=>0
                    
                ]+$o);
                if($listSubMenu && $vb['menu_id'] > 0){
                    $r['listSubMenu'] = \app\models\SiteMenu::getList([
                        'parent_id'=>$vb['menu_id'],
                        'limit'=>$limitSub
                    ]);
                }
                $result[$vb['code']] = $r;
            }
        }
        return $result;
    }
    
    public function getItemPrice($o = []){
        $a = ['tour_type','tour_start','tour_guest_group','tour_hotel','tour_date_time','item_id'];
        foreach ($a as $b){
            $$b = isset($o[$b]) ? $o[$b] : 0;
        }
        $price = 0; $currency = 1;
        $item = \app\modules\admin\models\Content::getItem($item_id);
        $f_tour_type = \app\modules\admin\models\Filters::getItem($tour_type);
        //view($f_tour_type);
        switch ($f_tour_type['value']){
            case 1: // Ghép theo lịch
                $tour_date_time = ctime(['string'=>$tour_date_time,'format'=>'Y-m-d']);
                $code = 'tour_date_time_'.  $tour_date_time;
                $p = \app\models\Articles::getItemPrice($item_id, $code);
                if(!empty($p)){
                    if($currency == $p['currency']){
                        $price = $p['price'];
                    }
                    //$currency = $p['currency'];
                    
                }
                break;
            case 3: // Đoàn riêng trọn gói
                $code = 'tour_hotel_' . $tour_hotel . '_' . $tour_guest_group;
                $p = \app\models\Articles::getItemPrice($item_id, $code);
                if(!empty($p)){
                    $price = $p['price'];
                    $currency = $p['currency'];
                    
                }
                break;
            default:
                $code = 'tour_type_'.$f_tour_type['value'] . '_' . $tour_type;
                $p = \app\models\Articles::getItemPrice($item_id, $code);
                if(!empty($p)){
                    if($currency == $p['currency']){
                        $price = $p['price'];
                    }
                    //$currency = $p['currency'];
                    
                }
                break;
                
        }
        $code = 'tour_start_'.$tour_start;
        $p = \app\models\Articles::getItemPrice($item_id, $code);
        if(!empty($p)){
            if($currency == $p['currency']){
                $price += $p['price'];
            }
            //$currency = $p['currency'];
            
        }
        return [
            'price'=>$price,
            'currency'=>$currency
        ];
    }
    
    public function getBoxCode($o = []){
        $module = '';
        if(!is_array($o)){
            $module = $o;
            $o = [];
        }
        $attr = isset($o['attr']) ? $o['attr'] : false;
        //$type = isset($o['type']) ? $o['type'] : 'products';
        $module = isset($o['code']) ? $o['code'] : $module;
        $list_box = \app\models\Box::getBox($module);
        return $this->getArticles(['box'=>$list_box,'category_id'=>0]+$o);
        
    }
    
    public function get_tree_menu(){
        $l = SiteMenu::get_tree_menu();
        if(!empty($l)){
            foreach ($l as $k=>$v){
                $l[$k]['link'] = isset($v['url_link']) ? $v['url_link'] : cu(DS.$v['url']);
            }
        }
        return $l;
    }
    
    //
    public function updateCart($action = 'add',$id=0,$amount=1){
        $amount = $amount > 0 ? $amount : 1;
        
        $c = isset($_SESSION[__SITE_NAME__]['cart']) ? $_SESSION[__SITE_NAME__]['cart'] : array($id=>array('amount'=>0,'total'=>0,'price'=>0));
        if(!isset($c[$id])) $c[$id]=array('amount'=>0,'total'=>0,'price'=>0);
        $item = Articles::getItem($id);
        
        switch($action){
            case 'add':
                $c[$id]['amount'] += $amount;
                break;
            case 'update':
                $c[$id]['amount'] = $amount;
                break;
            case 'delete':
                $c[$id]['amount'] = 0;
                unset($c[$id]);
                break;
        }
        if(!empty($c) && isset($c[$id])){
            if($item === false){
                $c[$id]['amount'] = 0;
                unset($c['cart'][$id]);
                return false;
            }else{
                $c[$id]['price']= $item['price2'];
                $c[$id]['total']= $item['price2'] * $c[$id]['amount'];
                $c[$id]['item'] = $item;
            }
        }
        if(!empty($c)){
            foreach($c as $k=>$v){
                if($v['amount']==0 || $v['total']==0){
                    // unset($c[$k]);
                }
            }
        }
        $_SESSION[__SITE_NAME__]['cart'] = $c;
        return true;
    }
    
    
    
    public function getCart(){
        $c = isset($_SESSION[__SITE_NAME__]['cart']) ? $_SESSION[__SITE_NAME__]['cart'] : array(0=>array('amount'=>0,'total'=>0,'price'=>0));
        
        $listItem = [];
        $totalItem = 0;
        $totalPrice = 0;
        if(!empty($c)){
            foreach($c as $k=>$v){
                if($k>0){
                    $listItem[] = $v;
                    $totalItem ++;
                    $totalPrice += $v['total'];
                }
            }
        }
        
        $cart = array(
            'totalItem'=>$totalItem,
            'totalPrice'=>$totalPrice,
            'listItem'=>$listItem,
        );
        
        return $cart;
    }
    public function unsetCart(){
        $_SESSION[__SITE_NAME__]['cart'] = null;
        unset($_SESSION[__SITE_NAME__]['cart']);
    }
    public function sendEmail($o=[]){
        return Yii::$app->sendEmail($o);
    }
    public function sentEmail($o=[]){
        return Yii::$app->sentEmail($o);
    }
    public function getConfigs($code = false, $lang = __LANG__,$sid=__SID__,$cached=true){
        return \app\models\SiteConfigs::getConfigs($code,$lang,$sid,$cached);
    }
    public function getTextRespon($o = []){
        $id = is_array($o) && isset($o['id']) ? $o['id'] : 0;
        $sid = is_array($o) && isset($o['sid']) ? $o['sid'] : __SID__;
        $category_id = is_array($o) && isset($o['category_id']) ? $o['category_id'] : 0;
        $lang = is_array($o) && isset($o['lang']) ? $o['lang'] : __LANG__;
        //view(isset($o['lang']));
        $default = is_array($o) && isset($o['default']) && $o['default'] == true ? true : false;
        $code = is_array($o) && isset($o['code']) ? $o['code'] : false;
        $list = is_array($o) && isset($o['list']) && $o['list'] == true ? true : false;
        $show = is_array($o) && isset($o['show']) && $o['show'] == false ? false : true;
        if(is_numeric($o) && $o > 0){
            $id = $o;
        }elseif (is_array($o)){
            
        }else {
            $code = $o;
        }
        
        $query = (new Query())->from(['a'=>'{{%form_design}}'])->where(['a.is_active'=>1,'a.lang'=>$lang]);
        if($show == false){
            $query->select(['a.*']);
        }else{
            $query->select(['a.value']);
        }
        if($code !== false){
            $query->innerJoin(['b'=>'{{%form_design_category}}'],'a.category_id=b.id');
            $query->andWhere(['b.code'=>$code]);
        }
        if($id>0) $query->andWhere(['a.id'=>$id]);
        if($category_id>0) $query->andWhere(['a.category_id'=>$category_id]);
        if($default){
            $query->andWhere(['a.state'=>2]);
        }else{
            $query->andWhere(['and', 'a.sid=' . $sid,['>','a.state',-2]]);
        }
        $query->orderBy(['a.title'=>SORT_ASC]);
        if($show) {
            $l = $query->scalar();
            //$l = Zii::$db->queryScalar($sql);
        }
        if($list){
            $l = $query->all();
            //$l = Zii::$db->queryAll($sql);
        }else{
            $l = $query->one();
            //$l = Zii::$db->queryRow($sql);
        }
        if(empty($l) && is_array($o) && !$default){
            $o['default'] = true;
            return $this->getTextRespon($o);
        }
        return $l;
    }
    
    public function getDetailTabs($id = 0, $return_mode = 0){
        
        $l = (new Query())->from('{{%tab_details}}')->where(['item_id'=>$id])->all();
        
        switch($return_mode){
            case 1:
                // return id only
                $rs = [];
                if(!empty($l)){
                    foreach($l as $k=>$v){
                        $rs[] = $v['id'];
                    }
                }
                return $rs;
                break;
            default: return $l; break;
        }
    }
    
    public function genCustomerCode($type_id = TYPE_ID_CUS){
        $pre = '';
        switch ($type_id){
            case 0: // Học viên
                $pre = 'ST';
                break;
            case 2: // Giáo viên
                $pre = 'TE';
                break;
            case 3: // Trợ giảng
                $pre = 'TA';
                break;
        }
        $code = $pre . danhso(rand(1,999999));
        while ((new Query)->from('customers')->where(array('code'=>$code,'sid'=>__SID__))->count(1) > 0){
            $code = $pre . danhso(rand(1,999999));
        }
        return $code;
    }
    
    
    public function getCVideos($o = []){
        $limit = isset($o['limit']) ? $o['limit'] : 15;
        $sql = "select * from cvideos as a where a.state>-2 and a.sid=".__SID__ . " and a.lang='".__LANG__."'";
        $sql .= " order by a.position, a.time desc";
        $sql .= $limit > 0 ? " limit $limit" : '';
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
    public function getLocals(){
        
    }
    public function getBox($code = '',$o = []){
        return \app\models\Box::getBox($code,$o);
    }
    // Database
    public function insert($table, $data, $id = 'id'){
        Yii::$app->db->createCommand()->insert($table,$data)->execute();
        if($id !== false){
            return (new Query())->select('max('.$id.')')->from($table)->scalar();
        }
        
    }
    public function update($table, $data, $condition){
        return Yii::$app->db->createCommand()->update($table,$data,$condition)->execute();
    }
    
    public function get_exrate($o = []){
        $from = isset($o['from']) ? $o['from'] : 0;
        $to = isset($o['to']) ? $o['to'] : 0;
        $time = isset($o['time']) ? $o['time'] : false;
        $from = is_numeric($from) ? $from : $this->get_id_from_code($from);
        $to = is_numeric($to) ? $to : $this->get_id_from_code($to);
        if(!is_numeric($from)){
            $c = $this->getCurrencyByCode($from);
            $from = $c['id'];
        }
        if(!is_numeric($to)){
            $c = $this->getCurrencyByCode($to);
            $to = $c['id'];
        }
        
        $time = check_date_string($time) ? ctime(array('string'=>$time ,'return_type'=>1)) : false;
        $sql = "select a.to_currency,a.value,a.from_date from exchange_rate as a where a.from_currency=$from";
        $sql .= $to > 0 ? " and a.to_currency=$to" : "";
        $sql .= $time !== false ? " and DAYOFYEAR(a.from_date)=".date('z',$time) . " and YEAR(a.from_date)=" . date('Y',$time) : '';
        $sql .= " order by a.from_date desc";
        if(isset($o['return']) && $o['return'] == 'last'){
            $sql .= " limit 1";
            return Yii::$app->db->createCommand($sql)->queryOne();
        }
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
    
    public function getListContractType(){
        $sql = "select id,name from `contract_type` where state>0 and sid=".__SID__;
        $sql .= " order by name";
        $l = Zii::$db->queryAll($sql);
        return $l;
    }
    
    public function getExchangeRate($from = 2, $to = 1,$o = []){
        if($from == $to) return 1;
        $query = (new Query())->select(['value'])
        ->from('exchange_rate')
        ->where(['from_currency'=>$from,'to_currency'=>$to])
        ->orderBy(['from_date'=>SORT_DESC])->limit(1);
        if(isset($o['time']) && check_date_string($o['time'])){
            $expression = new \yii\db\Expression('UNIX_TIMESTAMP(from_date)');
            $query->andWhere(['<',$expression,strtotime($o['time'])]);
        }
        //return $query->getSql();
        return $query->scalar();
    }
    
    
    public function getServicePrice($price = 0, $o = []){
        $from = isset($o['from']) ? $o['from'] : 1;
        $to = isset($o['to']) ? $o['to'] : 1;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        //
        if($from == $to) return [
            'price'=>$price,
            'decimal'=>$this->showCurrency($to,3),
            'changed'=>false
        ];
        //
        return [
            'price'=>$price * $this->getItemExchangeRate($o),
            'decimal'=>$this->showCurrency($to,3),
            'changed'=>true,
            'old_price'=>$this->showPrice($price, $from),
        ];
    }
    
    public function getItemExchangeRate($o = []){
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $from = isset($o['from']) ? $o['from'] : 1;
        $to = isset($o['to']) ? $o['to'] : 1;
        $time = isset($o['time']) ? $o['time'] : date("Y-m-d H:i:s");
        $query = (new Query())
        ->from('tours_programs_exchange_rate')
        ->where(['from_currency'=>$from,'to_currency'=>$to, 'item_id'=>$item_id]);
        $item = $query->one();
        if(!empty($item)){
            return $item['value'];
        }
        return $this->getExchangeRate($from,$to,$o);
    }
    
    public function getServiceDetailDayPrices($o = []){
        
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $sub_item_id = isset($o['sub_item_id']) ? $o['sub_item_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : 0;
        $state = isset($o['state']) ? $o['state'] : 1;
        $package_id = isset($o['package_id']) ? $o['package_id'] : 0;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $nationality = isset($o['nationality']) ? $o['nationality'] : 0;
        $time_id = isset($o['time_id']) ? $o['time_id'] : -1;
        $day_id = isset($o['day_id']) ? $o['day_id'] : 0;
        $item = [];
        $loadDefault = isset($o['loadDefault']) ? $o['loadDefault'] : false;
        switch ($type_id){
            case TYPE_ID_HOTEL: case TYPE_ID_REST: case TYPE_ID_SHIP_HOTEL:
                $item = \app\modules\admin\models\Customers::getItem($service_id);
                break;
            case TYPE_CODE_DISTANCE:
                $item = \app\modules\admin\models\Distances::getItem($service_id);
                break;
            case TYPE_ID_SCEN: case TYPE_ID_TRAIN:
                $item = \app\modules\admin\models\Tickets::getItem($service_id);
                break;
            case TYPE_ID_GUIDES:
                $item = \app\modules\admin\models\Guides::getItem($this->getSupplierIDFromService($service_id,TYPE_ID_GUIDES));
                break;
            case TYPE_ID_SHIP:
                $item = \app\modules\admin\models\Customers::getItem($this->getSupplierIDFromService($service_id,TYPE_ID_SHIP));
                ///$item['aaaaaa'] = json_encode($this->getSupplierIDFromService($service_id));
                break;
            default: $item = \app\modules\admin\models\Customers::getItem($supplier_id); break;
        }
        
        $query = (new Query())->from(['a'=>'tours_programs_services_prices'])
        ->where([
            'a.item_id'=>$item_id,
            //'supplier_id'=>$supplier_id,
            //'a.state'=>$state,
            //'day'=>$day,
            'package_id'=>$package_id,
            'a.type_id'=>$type_id
        ]);
        //
        
        if($day_id > -1){
            $query->andWhere(['a.day_id'=>$day_id]);
        }
        if($time_id > -1){
            $query->andWhere(['a.time_id'=>$time_id]);
        }
        //
        if($service_id>0){
            $query->andWhere(['a.service_id'=>$service_id]);
        }
        //
        if($supplier_id>0){
            $query->andWhere(['a.supplier_id'=>$supplier_id]);
        }
        if($sub_item_id>0){
            $query->andWhere(['a.sub_item_id'=>$sub_item_id]);
        }
        //
        // view($query->createCommand()->getRawSql()); 
        $r = $query->one();
        //return $item;
        if(!empty($r)){
            $r['supplier'] = $item;
            //$r['s'] = $s;
        }else {
            //return $s;
        }
        return $r;
        
    }
    
    public function getProgramGuidesPrices($o = []){
        //\\//\\ *.* //\\//\\
        $from_date = isset($o['from_date']) && check_date_string($o['from_date']) ? $o['from_date'] : false;
        $day = isset($o['day']) ? $o['day'] : -1;
        $time = isset($o['time']) ? $o['time'] : -1;
        
        $day_id = isset($o['day_id']) ? $o['day_id'] : $day;
        $time_id = isset($o['time_id']) ? $o['time_id'] : $time;
        $season_time_id = isset($o['season_time_id']) ? $o['season_time_id'] : $time_id;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : TYPE_ID_GUIDES;
        $state = isset($o['state']) ? $o['state'] : 1;
        $package_id = isset($o['package_id']) ? $o['package_id'] : 0;
        $sub_item_id = isset($o['sub_item_id']) ? $o['sub_item_id'] : 0;
        $segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $nationality = isset($o['nationality']) ? $o['nationality'] : 0;
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : $nationality;
        $loadDefault = isset($o['loadDefault']) ? $o['loadDefault'] : false;
        $updateDatabase = isset($o['updateDatabase']) ? $o['updateDatabase'] : true;
        $guide_type = isset($o['guide_type']) ? $o['guide_type'] : 2;
        $root_guide_type = isset($o['root_guide_type']) ? $o['root_guide_type'] : 2;
        $item = \app\modules\admin\models\ToursPrograms::getItem($item_id);
        if($from_date === false && $item_id>0){
            
            $from_date = date('Y-m-d', mktime(0,0,0,
                date('m',strtotime($item['from_date'])),
                date('d',strtotime($item['from_date']))+$day_id,
                date('Y',strtotime($item['from_date']))));
        }
        //$updateDatabase = false;
        $supplier_id = $this->getSupplierIDFromService($service_id,$type_id);
        //$supplier_id = $supplier_id > 0 ? $supplier_id : $service_id;
        //\\//\\ *.* //\\//\\
        if(isset($o['quotation']) && !empty($o['quotation'])){
            $quotation = $o['quotation'];
        }else{
            $quotation = \app\modules\admin\models\Suppliers::getQuotation([
                'supplier_id'=>$supplier_id,
                'date'=>$from_date
            ]);
        }
        if(isset($o['nationality_group']) && !empty($o['nationality_group'])){
            $nationality_group = $o['nationality_group'];
        }else{
            if(!($nationality_id>0)){
                $nationality_id = $item['nationality'];
            }
            $nationality_group = \app\modules\admin\models\Suppliers::getNationalityGroup([
                'supplier_id'=>$supplier_id,
                'nationality_id'=>$nationality_id,
            ]);
        }
        if(isset($o['seasons']) && !empty($o['seasons'])){
            $seasons = $o['seasons'];
        }else{
            $seasons = \app\modules\admin\models\Suppliers::getSeasons([
                'supplier_id'=>$supplier_id,
                'date'=>$from_date,
                'time_id'=>$time_id
            ]);
        }
        if(isset($o['groups']) && !empty($o['groups'])){
            $groups = $o['groups'];
        }else{
            $groups = \app\modules\admin\models\Suppliers::getGuestGroup([
                'supplier_id'=>$supplier_id,
                
                'date'=>$from_date,
                'time_id'=>$time_id
            ]);
        }
        if(!$loadDefault && $item_id>0){
            // Lấy giá đã lưu riêng
            
            $query = (new Query())->from(['a'=>'tours_programs_guides_prices'])
            ->where([
                'a.item_id'=>$item_id,
                //'supplier_id'=>$supplier_id,
                //'a.state'=>$state,
                //'day'=>$day,
                //'time'=>$time,
                'a.package_id'=>$package_id,
                'a.type_id'=>$guide_type
            ]);
            //
            if($segment_id > 0){
                $query->andWhere(['a.segment_id'=>$segment_id]);
            }
            if($time_id > -1){
                //$query->andWhere(['a.time_id'=>$time_id]);
            }
            //
            if($service_id>0){
                $query->andWhere(['a.service_id'=>$service_id]);
            }
            //
            if($supplier_id>0){
                $query->andWhere(['a.supplier_id'=>$supplier_id]);
            }

            //
            $r = $query->one();
           // view($r);
            if(!empty($r)){
                $loadDefault = false;
                if($service_id > 0 ){
                    //$loadDefault = true; $updateDatabase = true;
                }
            }else{
                $loadDefault = true; $updateDatabase = true;
            }
            //
            //view($r);
        }
        //view($loadDefault);
        if($loadDefault){
            $number_of_day = isset($o['number_of_day']) ? $o['number_of_day'] : 0;
            $number_of_day = \app\modules\admin\models\ProgramSegments::countDayOfParent($item_id, $segment_id, ['guide_type'=>$guide_type]);
            $quantity = isset($o['quantity']) ? $o['quantity'] : 0;
            
            $x = \app\modules\admin\models\ToursPrograms::getAutoGuideQuantity([
                'item_id'=>$item_id,
                'segment_id'=>$segment_id
            ]);
            
            $r = $this->getDefaultServicePrices([
                'controller_code'=>TYPE_ID_GUIDES,
                'quotation_id'=>$quotation['id'],
                'nationality_id'=>$nationality_group['id'],
                'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
                'supplier_id'=>$supplier_id,
                'total_pax'=>$total_pax,
                'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
                'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
                'package_id'=>$package_id,
                'item_id'=>$service_id,
                'sub_item_id'=>$service_id,
                'season_time_id'=>$season_time_id,
                'seasons'=>$seasons,
            ]);
            
            //view($r,true);
            if(!empty($r)){
                $r['quantity'] = $x['quantity'];
                $r['number_of_day'] = $x['number_of_day'];
            }
            
            if(0>1 && $updateDatabase && !empty($r)){
                
                
                if((new Query())->from('tours_programs_guides')->where([
                    'item_id'=>$item_id,
                    'supplier_id'=>$supplier_id,
                    'segment_id'=>$segment_id,
                    'guide_id'=>$service_id,
                    'package_id'=>$package_id,
                    'type_id'=>$guide_type
                ])->count(1) == 0){
                    Yii::$app->db->createCommand()->insert('tours_programs_guides', [
                        'item_id'=>$item_id,
                        'supplier_id'=>$supplier_id,
                        'segment_id'=>$segment_id,
                        'guide_id'=>$service_id,
                        'package_id'=>$package_id,
                        'quantity'=>$r['quantity'],
                        'type_id'=>$guide_type
                        
                        
                    ])->execute();
                }else{
                    Yii::$app->db->createCommand()->update('tours_programs_guides', [
                        'quantity'=>$r['quantity'],
                    ],[
                        'item_id'=>$item_id,
                        'supplier_id'=>$supplier_id,
                        'segment_id'=>$segment_id,
                        'guide_id'=>$service_id,
                        'package_id'=>$package_id,
                        'type_id'=>$guide_type
                    ])->execute();
                }
                ///////////////////////////////////////////
                if((new Query())->from('tours_programs_guides_prices')->where([
                    'item_id'=>$item_id,
                    'supplier_id'=>$supplier_id,
                    'segment_id'=>$segment_id,
                    'service_id'=>$service_id,
                    'package_id'=>$package_id,
                    'type_id'=>$guide_type
                ])->count(1) == 0){
                    Yii::$app->db->createCommand()->insert('tours_programs_guides_prices', [
                        'item_id'=>$item_id,
                        'supplier_id'=>$supplier_id,
                        'segment_id'=>$segment_id,
                        'service_id'=>$service_id,
                        'package_id'=>$package_id,
                        'quantity'=>$r['quantity'],
                        'number_of_day'=>$r['number_of_day']>0 ? $r['number_of_day'] : 0,
                        'price1'=>$r['price1'],
                        'currency'=>$r['currency'],
                        'type_id'=>$guide_type
                        
                        
                    ])->execute();
                }else{
                    Yii::$app->db->createCommand()->update('tours_programs_guides_prices', [
                        'quantity'=>$r['quantity'],
                        'number_of_day'=>$r['number_of_day']>0 ? $r['number_of_day'] : 0,
                        'price1'=>$r['price1'],
                        'currency'=>$r['currency'],
                    ],[
                        'item_id'=>$item_id,
                        'supplier_id'=>$supplier_id,
                        'segment_id'=>$segment_id,
                        'service_id'=>$service_id,
                        'package_id'=>$package_id,
                        'type_id'=>$guide_type
                    ])->execute();
                }
                
            }
            
        }
        return $r;
    }
    
    
    
    
    public function getServiceDetailPrices($o = []){
        //\\//\\ *.* //\\//\\
        $from_date = isset($o['from_date']) && check_date_string($o['from_date']) ? $o['from_date'] : false;
        $day = isset($o['day']) ? $o['day'] : -1;
        $time = isset($o['time']) ? $o['time'] : -1;
        //$service_id
        $day_id = isset($o['day_id']) ? $o['day_id'] : $day;
        $time_id = isset($o['time_id']) ? $o['time_id'] : $time;
        $season_time_id = isset($o['season_time_id']) ? $o['season_time_id'] : $time_id;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : 0;
        $state = isset($o['state']) ? $o['state'] : 1;
        $package_id = isset($o['package_id']) ? $o['package_id'] : 0;
        $sub_item_id = isset($o['sub_item_id']) ? $o['sub_item_id'] : 0;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $nationality = isset($o['nationality']) ? $o['nationality'] : 0;
        $quantity = isset($o['quantity']) ? $o['quantity'] : 0;
        $nationality_id = isset($o['nationality_id']) ? $o['nationality_id'] : $nationality;
        $loadDefault = isset($o['loadDefault']) ? $o['loadDefault'] : false;
        $updateDatabase = isset($o['updateDatabase']) ? $o['updateDatabase'] : true;
        if($from_date === false && $item_id>0){
            $item = \app\modules\admin\models\ToursPrograms::getItem($item_id);
            $from_date = date('Y-m-d', mktime(0,0,0,
                date('m',strtotime($item['from_date'])),
                date('d',strtotime($item['from_date']))+$day_id,
                date('Y',strtotime($item['from_date']))));
        }
        //$updateDatabase = false;
        $supplier_id = $this->getSupplierIDFromService($service_id,$type_id);
        //$supplier_id = $supplier_id > 0 ? $supplier_id : $service_id;
        //\\//\\ *.* //\\//\\
        $quotation = \app\modules\admin\models\Suppliers::getQuotation([
            'supplier_id'=>$supplier_id,
            'date'=>$from_date
        ]);
         
        //
        $nationality_group = \app\modules\admin\models\Suppliers::getNationalityGroup([
            'supplier_id'=>$supplier_id,
            'nationality_id'=>$nationality_id,
        ]);
        //
        $seasons = \app\modules\admin\models\Suppliers::getSeasons([
            'supplier_id'=>$supplier_id,
            'date'=>$from_date,
            'time_id'=>$time_id
        ]);
       //view($seasons);
        $groups = \app\modules\admin\models\Suppliers::getGuestGroup([
            'supplier_id'=>$supplier_id,
            
            'date'=>$from_date,
            'time_id'=>$time_id
        ]);
        
        if(!$loadDefault && $item_id>0){
            // Lấy giá đã lưu riêng
            
            $query = (new Query())->from(['a'=>'tours_programs_services_prices'])
            ->where([
                'a.item_id'=>$item_id,
                //'supplier_id'=>$supplier_id,
                //'a.state'=>$state,
                //'day'=>$day,
                //'time'=>$time,
                'a.package_id'=>$package_id,
                'a.type_id'=>$type_id
            ]);
            //
            if($day_id > -1){
                $query->andWhere(['a.day_id'=>$day_id]);
            }
            if($time_id > -1){
                $query->andWhere(['a.time_id'=>$time_id]);
            }
            //
            if($service_id>0){
                $query->andWhere(['a.service_id'=>$service_id]);
            }
            //
            if($supplier_id>0){
                $query->andWhere(['a.supplier_id'=>$supplier_id]);
            }
            //
            //view($query->createCommand()->getRawSql());
            //
            $r = $service_id > 0 ? $query->one() : $query->all();
            
            if(!empty($r)){
                $loadDefault = false;
                if($service_id > 0 && $r['state'] == 2){
                    //$loadDefault = true;
                }
            }else{
                $loadDefault = true;
            }
        }
        
        if($loadDefault){
            // Lấy giá từ hệ thống
            
            $r = [];
            
            switch ($type_id){
                case TYPE_ID_SCEN : // Vé tham quan
                    $r = \app\modules\admin\models\Tickets::getPrice([
                    'item_id'=>$service_id,
                    'nationality'=>$nationality_id
                    ]);
                    $r['quantity'] = $total_pax;
                    $r['item_id'] = $service_id;
                    $r['sub_item_id'] = $service_id;
                    break;
                case TYPE_ID_HOTEL:
                case TYPE_ID_SHIP_HOTEL: // Khách sạn
                    
                    $r = $this->getDefaultHotelPrices([
                    'quotation_id'=>$quotation['id'],
                    'nationality_id'=>$nationality_group['id'],
                    'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax,
                    'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
                    'package_id'=>$package_id,
                    'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
                    'season_time_id'=>$season_time_id,
                    'seasons'=>$seasons,
                    ]);
                    
                    break;
                    
                case TYPE_ID_REST: // nhà hàng
                    $r = $this->getDefaultServicePrices([
                    'controller_code'=>TYPE_ID_REST,
                    'quotation_id'=>$quotation['id'],
                    'nationality_id'=>$nationality_group['id'],
                    'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax,
                    'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
                    'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
                    'package_id'=>$package_id,
                    'season_time_id'=>$season_time_id,
                    'seasons'=>$seasons,
                    ]);
                    
                    break;
                case TYPE_ID_GUIDES:
                    $r = $this->getDefaultServicePrices([
                    'controller_code'=>TYPE_ID_GUIDES,
                    'quotation_id'=>$quotation['id'],
                    'nationality_id'=>$nationality_group['id'],
                    'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax,
                    'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
                    'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
                    'package_id'=>$package_id,
                    'item_id'=>$service_id,
                    'sub_item_id'=>$service_id,
                    'season_time_id'=>$season_time_id,
                    'seasons'=>$seasons,
                    ]);
                    
                    $r['quantity'] = $quantity;
                    break;
                case TYPE_ID_TRAIN:
                    
                    $r = $this->getDefaultTrainTicketPrices([
                    'controller_code'=>TYPE_ID_TRAIN,
                    'quotation_id'=>$quotation['id'],
                    'nationality_id'=>$nationality_group['id'],
                    'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax,
                    'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
                    'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
                    'package_id'=>$package_id,
                    'item_id'=>$item_id,
                    'service_id'=>$service_id,
                    'season_time_id'=>$season_time_id,
                    'seasons'=>$seasons,
                    ]);
                    
                    
                    //$r['quantity'] = $quantity;
                    break;
                case TYPE_ID_TEXT:
                    $r = [];
                    $r['type_id'] = TYPE_ID_TEXT;
                    $r['quantity'] = 0;
                    break;
                default:
                    $r = $this->getDefaultServicePrices([
                    'controller_code'=>$type_id,
                    'quotation_id'=>$quotation['id'],
                    'nationality_id'=>$nationality_group['id'],
                    'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax,
                    'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
                    'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
                    'package_id'=>$package_id,
                    'sub_item_id'=>$sub_item_id,
                    'item_id'=>$sub_item_id,
                    'season_time_id'=>$season_time_id,
                    'seasons'=>$seasons,
                    'service_id'=>$service_id
                    
                    ]);
                    //view($type_id);
                    //view($r,true); exit;
                    break;
                    
            }
            // Cập nhật vào DB
            
            if($updateDatabase && !empty($r)){
                
                if((new Query())->from('tours_programs_services_prices')->where([
                    'item_id'=>$item_id,
                    'package_id'=>$package_id,
                    'service_id'=>$service_id,
                    'day_id'=>$day_id,
                    'time_id'=>$time_id,
                    'type_id'=>$type_id,
                ])->count(1) == 0){
                
                Yii::$app->db->createCommand()->insert('tours_programs_services_prices',[
                    'item_id'=>$item_id,
                    'supplier_id'=>$supplier_id,
                    'sub_item_id'=>isset($r['sub_item_id']) ? $r['sub_item_id'] : 0,
                    'service_id'=>$service_id,
                    'day_id'=>$day_id,
                    'time_id'=>$time_id,
                    'type_id'=>$type_id,
                    'quantity'=>isset($r['quantity']) ? $r['quantity'] : 0,
                    'price1'=>isset($r['price1']) ? $r['price1'] : 0,
                    'currency'=>isset($r['currency']) ? $r['currency'] : 1,
                    'package_id'=>$package_id,
                    
                ])->execute();
                
                }else{
                	Yii::$app->db->createCommand()->update('tours_programs_services_prices',[
                			 
                			'supplier_id'=>$supplier_id,
                			'sub_item_id'=>isset($r['sub_item_id']) ? $r['sub_item_id'] : 0,
                			 
                			 
                			'quantity'=>isset($r['quantity']) ? $r['quantity'] : 0,
                			//'price1'=>isset($r['price1']) ? $r['price1'] : 0,
                			'currency'=>isset($r['currency']) ? $r['currency'] : 1,
                			 
                			
                	],
                			[
                					'item_id'=>$item_id,
                					'package_id'=>$package_id,
                					'service_id'=>$service_id,
                					'day_id'=>$day_id,
                					'time_id'=>$time_id,
                					'type_id'=>$type_id,
                			]
                	)->execute();
                	if(isset($r['price1']) && $r['price1'] > 0){
                			Yii::$app->db->createCommand()->update('tours_programs_services_prices',[
                					
                					//'supplier_id'=>$supplier_id,
                					//'sub_item_id'=>isset($r['sub_item_id']) ? $r['sub_item_id'] : 0,
                					
                					
                					//'quantity'=>isset($r['quantity']) ? $r['quantity'] : 0,
                					'price1'=>isset($r['price1']) ? $r['price1'] : 0,
                					//'currency'=>isset($r['currency']) ? $r['currency'] : 1,
                					
                					
                			],
                					[
                							'item_id'=>$item_id,
                							'package_id'=>$package_id,
                							'service_id'=>$service_id,
                							'day_id'=>$day_id,
                							'time_id'=>$time_id,
                							'type_id'=>$type_id,
                							'price1'=>0
                					]
                					)->execute();
                	}
                			
                			
                }
            }
        }
        
        
        
        $r['quantity'] = isset($r['quantity']) ? $r['quantity'] : $total_pax;
        switch ($type_id){
            case TYPE_ID_HOTEL: case TYPE_ID_REST: case TYPE_ID_SHIP_HOTEL:
                $item = \app\modules\admin\models\Customers::getItem($service_id);
                break;
            case TYPE_CODE_DISTANCE:
                $item = \app\modules\admin\models\Distances::getItem($service_id);
                break;
            case TYPE_ID_SCEN:
                $item = \app\modules\admin\models\Tickets::getItem($service_id);
                break;
            case TYPE_ID_GUIDES:
                $item = \app\modules\admin\models\Guides::getItem($this->getSupplierIDFromService($service_id,$type_id));
                break;
            case TYPE_ID_SHIP:
                $item = \app\modules\admin\models\Customers::getItem($this->getSupplierIDFromService($service_id,$type_id));
                ///$item['aaaaaa'] = json_encode($this->getSupplierIDFromService($service_id));
                break;
            default: $item = \app\modules\admin\models\Customers::getItem($supplier_id); break;
        }
        $r['supplier'] = $item;
        
        return $r;
    }
    
    public function getDefaultHotelPrices($o = []){
        
        
        
        $result = ['price1'=>0,'currency'=>1,'quantity'=>0,'priority'=>-1];
        $time_id = isset($o['time_id']) ? $o['time_id'] : -1;
        $season_time_id = isset($o['season_time_id']) ? $o['season_time_id'] : -1;
        $state = isset($o['state']) ? $o['state'] : 1;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $room_id = isset($o['room_id']) ? $o['room_id'] : 0;
        $seasons = isset($o['seasons']) ? $o['seasons'] : [];
        $a = [
            'quotation_id',
            'package_id',
            'supplier_id',
            'nationality_id',
            'season_id',
            'group_id',
            'weekend_id',
            //'item_id'
        ];
        //view($time_id);
        foreach ($a as $b){
            $$b = isset($o[$b]) ? $o[$b] : 0;
        }
        if(empty($seasons['season_direct_prices'])){
            $seasons['season_direct_prices'] = [[
                'id'=>$season_id,
                'price_incurred1'=>1,
            ]];
        }
        
     /// view($o);
        
        foreach ($seasons['season_direct_prices'] as $season){
            
            // lấy phòng default
            $query = (new Query())->from(['a'=>\app\modules\admin\models\Hotels::tablePrice()])
            ->innerJoin(['b'=>\app\modules\admin\models\RoomsCategorys::tableName()],'a.item_id=b.id')
            ->where([
                //'a.is_default'=>1,
                'a.quotation_id'=>$quotation_id,
                'a.package_id'=>$package_id,
                'a.supplier_id'=>$supplier_id,
                'a.nationality_id'=>$nationality_id,
                'a.season_id'=>$season['id'],
                //'a.group_id'=>$group_id,
                //'a.weekend_id'=>$weekend_id,
                //'a.time_id'=>$time_id,
                //'a.quotation_id'=>$quotation_id,
                
            ])
            ->select(['b.*','a.price1','a.currency']);
            if($room_id>0){
            	$query->andWhere(['a.item_id'=>$room_id]);
            }else{
            	$query->andWhere(['a.is_default'=>1]);
            }
            ///view($query->createCommand()->getRawSql());
            $item = $query->one();
            //view($item);
            $item_id = 0;
            if(!empty($item)){
                //
                
                $item_id = $item['id'] ;
                $total_rooms = ceil($total_pax/$item['seats']);
               // view($total_rooms);
                $groups = \app\modules\admin\models\Suppliers::getGuestGroup([
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_rooms
                ]);
                $group_id = isset($groups['id']) ? $groups['id'] : 0;
                //view($groups);
                //
                $query = (new Query())->from(['a'=>\app\modules\admin\models\Hotels::tablePrice()])
                ->innerJoin(['b'=>\app\modules\admin\models\RoomsCategorys::tableName()],'a.item_id=b.id')
                ->where([
                    //'a.is_default'=>1,
                    'a.quotation_id'=>$quotation_id,
                    'a.package_id'=>$package_id,
                    'a.supplier_id'=>$supplier_id,
                    'a.nationality_id'=>$nationality_id,
                    'a.season_id'=>$season['id'],
                    'a.group_id'=>$group_id,
                    'a.weekend_id'=>$weekend_id,
                    'a.time_id'=>$time_id,
                    'a.quotation_id'=>$quotation_id,
                    'a.item_id'=>$item_id
                    
                ])
                ->select(['b.*','a.price1','a.currency','is_default'=>'a.is_default'])
                ;
                
              //  view ($query->createCommand()->getRawSql());
                $season['price_incurred1'] = isset($season['price_incurred1']) && $season['price_incurred1'] > 0 ? $season['price_incurred1'] : 1;
                $item = $query->one();
                $item['sub_item_id'] = $item_id;
                $item['quantity'] = $total_rooms;
                $item['price1'] = isset($item['price1']) ? $item['price1'] * ($season['price_incurred1'] > 0 ? $season['price_incurred1'] : 1) : 0;
                $item['price_incurred1'] = $season['price_incurred1'];
                
                $item['currency'] = isset($item['currency']) ? $item['currency'] : 1;
                
                if($season['priority'] > $result['priority']){
                    $item['priority'] = $season['priority'];
                    $result = $item;
                }elseif($season['priority'] == $result['priority'] && $item['price1']>$result['price1']){
                    $item['priority'] = $season['priority'];
                    $result = $item;
                };

            }
        }
        // lấy giá phụ thu
        if(isset($seasons['seasons_price_type_2'])){
            foreach ($seasons['seasons_price_type_2'] as $season){
                switch ($season['unit_price']){
                    case 1: // Phong
                        if(in_array($season['time_id'], [-1,$season_time_id])){
                            if($season['currency'] == $result['currency']){
                                $result['price1'] += $season['price_incurred'];
                            }else{
                                
                            }
                        }
                        break;
                        
                    case 2: // khach
                        //
                        $total1 = ($season['price_incurred'] * $total_pax) / $total_rooms;
                        //
                        if(in_array($season['time_id'], [-1,$season_time_id])){
                            if($season['currency'] == $result['currency']){
                                $result['price1'] += $total1;
                            }else{
                                
                            }
                        }
                        break;
                        
                }
                
            }
        }
        
        return $result;
    }
    
    
    public function getDefaultServicePrices($o = []){
        $result = ['price1'=>0,'currency'=>1];
        $season_time_id = isset($o['season_time_id']) ? $o['season_time_id'] : -1;
        $time_id = isset($o['time_id']) ? $o['time_id'] : -1;
        $group_id = isset($o['group_id']) ? $o['group_id'] : -1;
        
        $state = isset($o['state']) ? $o['state'] : 1;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : 0;
        $controller_code = isset($o['controller_code']) ? $o['controller_code'] : $type_id;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $sub_item_id = isset($o['sub_item_id']) ? $o['sub_item_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $seasons = isset($o['seasons']) ? $o['seasons'] : [];
        $a = [
            'quotation_id',
            'package_id',
            'supplier_id',
            'nationality_id',
            'season_id',
            //'group_id',
            'weekend_id',
        	'vehicle_id'
        ];
        foreach ($a as $b){
            $$b = isset($o[$b]) ? $o[$b] : 0;
        }
        
        
        
        if(empty($seasons['season_direct_prices'])){
            $seasons['season_direct_prices'] = [[
                'id'=>$season_id,
                'price_incurred1'=>1,
            ]];
        }
        
        foreach ($seasons['season_direct_prices'] as $season){
            // lấy item default
            $default = true;
            switch ($controller_code){
                case TYPE_ID_REST:
                    $t1 = \app\modules\admin\models\Menus::tableToPrice();
                    $t2 = \app\modules\admin\models\Menus::tableName();
                    break;
                case TYPE_ID_GUIDES:
                    $t1 = \app\modules\admin\models\Guides::tableToPrice();
                    $t2 = \app\modules\admin\models\Guides::tableGuide();
                    break;
                case TYPE_ID_SHIP:
                    $t1 = \app\modules\admin\models\Distances::table_to_prices();
                    $t2 = \app\modules\admin\models\Distances::tableName();
                    $default = false;
                    $c = Yii::$app->zii->getVehicleAuto([
                        'total_pax'=>$total_pax,
                        'nationality_id'=>$nationality_id,
                        'supplier_id'=>$supplier_id,
                        'auto'=>true,
                        
                    ]);
                     
                    $sub_item_id = 0;
                    if(!empty($c)){
                        $total_pax = $c[0]['quantity'];
                        $vehicle_id = $c[0]['id'];
                    }
                    break;
                case TYPE_ID_TRAIN:
                    //
                    $t1 = 'tickets';
                    $t2 = 'trains_to_prices';
                    break;
            }
            //
            //view('a-' . $controller_code,true);
           // $item_id= 0;
            //
            if($sub_item_id >0){
                $query = (new Query())->from(['a'=>$t2])
                //->innerJoin(['b'=>$t2],'a.item_id=b.id')
                ->where([
                		'a.id'=>$sub_item_id,
                ]) ;
                
                //view($query->createCommand()->getRawSql());
                
                
                $item = $query->one();
            }else {
                $query = (new Query())->from(['a'=>$t1])
                ->innerJoin(['b'=>$t2],'a.item_id=b.id')
                ->where([
                    
                    'a.quotation_id'=>$quotation_id,
                    'a.package_id'=>$package_id,
                    'a.supplier_id'=>$supplier_id,
                    'a.nationality_id'=>$nationality_id,
                    //'a.season_id'=>$season_id,
                    //'a.group_id'=>$group_id,
                    //'a.weekend_id'=>$weekend_id,
                    //'a.time_id'=>$time_id,
                    //'a.quotation_id'=>$quotation_id,
                    
                ])
                ->select(['b.*','a.price1','a.currency']);
                
                if(is_numeric($group_id) && $group_id> -1){
                    $query->andWhere(['group_id'=>$group_id]);
                }
                 
                if(isset($o['menu_id']) && $o['menu_id']>0){
                    switch ($controller_code){
                        case TYPE_ID_REST:
                            $default = false;
                            $query->andWhere(['a.item_id'=>$o['menu_id']]);
                            break;
                    }
                }
                
                if($default ){
                    $query->andWhere(['a.is_default'=>1,]);
                }
                //view($service_id);
                //view($query->createCommand()->getRawSql());
                //view(TYPE_ID_SHIP);
                switch ($controller_code){
                    case TYPE_ID_SHIP:
                        if($service_id>0){
                            $query->andWhere(['b.id'=>$service_id]);
                        }
                        if($vehicle_id>0){
                        	$query->andWhere(['a.vehicle_id'=>$vehicle_id]);
                        }
                        break;
                }
                $item = $query->one();
                //view($service_id);
                //view($query->select(['a.*'])->all());
               // view($item);
            }
            
            if(!empty($item)){
                //
                $item_id = $item['id'];
                $groups = \app\modules\admin\models\Suppliers::getGuestGroup([
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax
                ]);
                $group_id = isset($groups['id']) ? $groups['id'] : 0;
                //
                $query = (new Query())->from(['a'=>$t1])
                ->innerJoin(['b'=>$t2],'a.item_id=b.id')
                ->where([
                    //'a.is_default'=>1,
                    'a.quotation_id'=>$quotation_id,
                    'a.package_id'=>$package_id,
                    'a.supplier_id'=>$supplier_id,
                    'a.nationality_id'=>$nationality_id,
                    'a.season_id'=>$season['id'],
                    'a.group_id'=>$group_id,
                    'a.weekend_id'=>$weekend_id,
                    'a.time_id'=>$time_id,
                    'a.quotation_id'=>$quotation_id,
                    'a.item_id'=>$item['id']
                    
                ])
                ->select(['b.*','a.price1','a.currency'])
                ;
              
                switch ($controller_code){
                	case TYPE_ID_SHIP:
                		if($service_id>0){
                			$query->andWhere(['b.id'=>$service_id]);
                		}
                		if($vehicle_id>0){
                			$query->andWhere(['a.vehicle_id'=>$vehicle_id]);
                		}
                		break;
                }
                
                $item = $query->one();
                //
                $item['price1'] = isset($item['price1']) ? $item['price1'] : 0;
                $season['price_incurred1'] = isset($season['price_incurred1']) ? $season['price_incurred1'] : 1;
                $item['sub_item_id'] = $item_id;
                $item['quantity'] = $total_pax;
                $item['price1'] = $item['price1'] * ($season['price_incurred1']);
                $item['price_incurred1'] = $season['price_incurred1'];
                
                if($item['price1']>$result['price1'])$result = $item;
            }
        }
        // lấy giá phụ thu
        if(isset($seasons['seasons_price_type_2'])){
            foreach ($seasons['seasons_price_type_2'] as $season){
                switch ($season['unit_price']){
                    case 1: // Phong
                        if(in_array($season['time_id'], [-1,$season_time_id])){
                            if($season['currency'] == $result['currency']){
                                $result['price1'] += $season['price_incurred'];
                            }else{
                                
                            }
                        }
                        break;
                        
                    case 2: // khach
                        //
                        $total1 = ($season['price_incurred'] * $total_pax) / $total_pax;
                        //
                        if(in_array($season['time_id'], [-1,$season_time_id])){
                            if($season['currency'] == $result['currency']){
                                $result['price1'] += $total1;
                            }else{
                                
                            }
                        }
                        break;
                        
                }
                
            }
        }
        return $result;
    }
    
    
    public function getDefaultTrainTicketPrices($o = []){
        $result = ['price1'=>0];
        $season_time_id = isset($o['season_time_id']) ? $o['season_time_id'] : -1;
        $time_id = isset($o['time_id']) ? $o['time_id'] : -1;
        $state = isset($o['state']) ? $o['state'] : 1;
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $controller_code = isset($o['controller_code']) ? $o['controller_code'] : TYPE_ID_TRAIN;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $seasons = isset($o['seasons']) ? $o['seasons'] : [];
        $quotation_id = isset($o['quotation_id']) ? $o['quotation_id'] : 0;
        $from_date = isset($o['from_date']) ? $o['from_date'] : date('Y-m-d');
        $room_id = isset($o['room_id']) && $o['room_id']>0 ? $o['room_id'] : 0;
        $station_to = isset($o['station_to']) && $o['station_to']>0 ? $o['station_to'] : 0;
        $station_from = isset($o['station_from']) && $o['station_from']>0 ? $o['station_from'] : 0;
        
        
        
        $a = [
            'quotation_id',
            'package_id',
            'supplier_id',
            'nationality_id',
            'season_id',
            'group_id',
            'weekend_id',
            //'item_id'
        ];
        foreach ($a as $b){
            $$b = isset($o[$b]) ? $o[$b] : 0;
        }
        
        if($supplier_id == 0 && $service_id>0){
        	$supplier_id = $this->getSupplierIDFromService($service_id,TYPE_ID_TRAIN);
        }
        
        if($quotation_id == 0){
        	$quotation = \app\modules\admin\models\Suppliers::getQuotation([
        			'supplier_id'=>$supplier_id,
        			'date'=>$from_date
        	]);
        	if(!empty($quotation)){
        		$quotation_id = $quotation['id'];
        	}
        }
        if(empty($seasons)){
	        $seasons = \app\modules\admin\models\Suppliers::getSeasons([
	        		'supplier_id'=>$supplier_id,
	        		'date'=>$from_date,
	        		'time_id'=>$time_id
	        ]);
        }
        if(empty($seasons['season_direct_prices'])){
            $seasons['season_direct_prices'] = [[
                'id'=>$season_id,
                'price_incurred1'=>1,
            ]];
        }

         
        foreach ($seasons['season_direct_prices'] as $season){
            // lấy item default
            
            
            
            $default = true;
            switch ($controller_code){
                
                case TYPE_ID_TRAIN:
                    //
                    $t1 = 'tickets';
                    $t2 = 'trains_to_prices';
                    break;
            }
            // Lay thong tin service
            if($room_id>0){
            	$item = (new Query())->from('rooms_to_hotel')->where([
            			'room_id'=>$room_id,
            			'parent_id'=>$supplier_id,
            	])->one();
            }else{
	            $item = (new Query())->from('rooms_to_hotel')->where([
	                'parent_id'=>$supplier_id,
	                'is_default'=>1
	            ])->one();
            }
            if(empty($item)){
                $item = (new Query())->from('rooms_to_hotel')->where([
                    'parent_id'=>$supplier_id,
                ])->one();
            }
            
            if(!empty($item)){
                //
                $item_id = $item['room_id'];
                //
                $groups = \app\modules\admin\models\Suppliers::getGuestGroup([
                    'supplier_id'=>$supplier_id,
                    'total_pax'=>$total_pax
                ]);
                $group_id = isset($groups['id']) ? $groups['id'] : 0;
                //
                $query = (new Query())->from(['a'=>$t2])
                ->innerJoin(['b'=>$t1],'b.id=a.ticket_id')
                ->where([
                    //'a.is_default'=>1,
                    'a.quotation_id'=>$quotation_id,
                    //'a.package_id'=>$package_id,
                    'a.supplier_id'=>$supplier_id,
                    //'a.nationality_id'=>$nationality_id,
                    'a.season_id'=>$season['id'],
                    'a.group_id'=>$group_id,
                    'a.weekend_id'=>$weekend_id,
                    //'a.time_id'=>$time_id,
                    'a.quotation_id'=>$quotation_id,
                    'a.item_id'=>$item_id,
                    
                    
                ])
                ->select(['a.*','b.id','b.title'])
                ;
                
                if($package_id>0){
                	$query->andWhere(['a.package_id'=>$package_id]);
                }
                if($service_id>0){
                 	$query->andWhere(['a.ticket_id'=>$service_id]);
                }
                if($station_from>0){
                	$query->andWhere(['a.station_from'=>$station_from]);
                }
                if($station_to>0){
                	$query->andWhere(['a.station_to'=>$station_to]);
                }
                
               //view($query->createCommand()->getRawSql());
                $item = $query->one();  
                //view($item);
                //
                $item['price1'] = isset($item['price1']) ? $item['price1'] : 0;
                $season['price_incurred1'] = isset($season['price_incurred1']) ? $season['price_incurred1'] : 1;
                $item['sub_item_id'] = $item_id;
                $item['quantity'] = $total_pax;
                $item['price1'] = $item['price1'] * ($season['price_incurred1']);
                $item['price_incurred1'] = $season['price_incurred1'];
                
                if($item['price1']>$result['price1'])$result = $item;
                
            }
        }
        // lấy giá phụ thu
        if(isset($seasons['seasons_price_type_2'])){
            foreach ($seasons['seasons_price_type_2'] as $season){
                switch ($season['unit_price']){
                    case 1: // Phong
                        if(in_array($season['time_id'], [-1,$season_time_id])){
                            if($season['currency'] == $result['currency']){
                                $result['price1'] += $season['price_incurred'];
                            }else{
                                
                            }
                        }
                        break;
                        
                    case 2: // khach
                        //
                        $total1 = ($season['price_incurred'] * $total_pax) / $total_pax;
                        //
                        if(in_array($season['time_id'], [-1,$season_time_id])){
                            if($season['currency'] == $result['currency']){
                                $result['price1'] += $total1;
                            }else{
                                
                            }
                        }
                        break;
                        
                }
                
            }
        }
        
        
        
        return $result;
    }
    
    private function getPriceInfoFromDate($supplier_id, $date){
        // Check quotation
        $from_date = isset($o['from_date']) && check_date_string($o['from_date']) ? $o['from_date'] : date('Y-m-d');
        $day = isset($o['day']) ? $o['day'] : -1;
        $time = isset($o['time']) ? $o['time'] : -1;
        $day = isset($o['day']) ? $o['day'] : -1;
        $time = isset($o['time']) ? $o['time'] : -1;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $service_id = isset($o['service_id']) ? $o['service_id'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : 0;
        $state = isset($o['state']) ? $o['state'] : 1;
        
        $total_pax = isset($o['total_pax']) ? $o['total_pax'] : 0;
        $nationality = isset($o['nationality']) ? $o['nationality'] : 0;
        //
        
    }
    
    public function getTablePrice($code,$price_type=1){
        switch ($code){
            case TYPE_ID_HOTEL:
            case TYPE_ID_SHIP_HOTEL:
                
                return '{{%rooms_to_prices}}';
                break;
            case TYPE_ID_REST:
                return '{{%menus_to_prices}}';
                break;
            case TYPE_ID_GUIDES:
                return '{{%guides_to_prices}}';
                break;
                
            case TYPE_ID_VECL:
            case TYPE_ID_SHIP:
                return $price_type == 2 ? '{{%distances_to_prices}}' : '{{%vehicles_to_prices}}';
                break;
            case TYPE_ID_TRAIN:
                return '{{%trains_to_prices}}';
                break;
                
                
                
        }
        return false;
    }
    // File manager
    public function mkDir($path){
        if(!file_exists($path)){
            return @mkdir($path,0755,true);
        }
        return false;
    }
    
    public function getBoxCss($box){
        $b = $this->getBox($box);
        if(!empty($b)){
            $css = '<style type="text/css">';
            if(isset($b['css']) && !empty($b['css'])){
                
            }
            $css .= '</style>';
        }
        return false;
    }
    
    public function getLichKhoiHanhTour($id, $o = []){
    	$html = '<table class="tour-detail-calendar table table-bordered table-hover table-responsive vmiddle"><thead><tr class="bold center">';
    	$html .= '<th class="center" >Stt</th>
<th class="center">Ngày khởi hành</th><th class="center">Đặc điểm</th>
<th class="center">Giá từ</th>
<th class="center">Số chỗ</th><th class="center">Đặt tour</th> </tr>

</thead><tbody>';
    	
    	$date_list = [];
//     	\app\modules\admin\models\Filters::getFilters([
//     			//'parent_id'=>post('id',0),
//     			'min_date'=> date('Y-m-d'),
//     			'item_id'=>$id,
//     			'code'=>'tour_date_time',
//     			'orderBy'=>['a.date'=>SORT_ASC,'a.position'=>SORT_ASC,'a.title'=>SORT_ASC]
//     	]);
    	
    	if(!empty($date_list)){
    		foreach ($date_list as $k=>$v){
    			$filter = \app\models\Articles::getItemFilter($id,$v['id']);
    			$code = 'tour_date_time_'.  $v['date'];
    			$p = \app\models\Articles::getItemPrice($id, $code);
    			$item_price =$old_item_price= 0; $currency = 1;
    			if(!empty($p)){
    				//if($currency == $p['currency']){
    				$item_price= $p['price'];
    				//}
    				$currency = $p['currency'];
    				
    			}
    			
    			// old_price_tour_date_time_2017-09-21
    			$p2 = \app\models\Articles::getItemPrice($id, 'old_price_'. $code);
    			if(!empty($p2)){
    				//if($currency == $p['currency']){
    				$old_item_price= $p2['price'];
    				//}
    				//$currency = $p['currency'];
    				
    			}
    			$t = (new Query())->from('articles_to_filters')->where([
    					'item_id'=>$id,
    					'filter_id'=>$v['id']
    			])->one();
    			
    			$status = isset($filter['status']) ? $filter['status'] : '-';
    			$html .= '<tr >
<td class="center">'.($k+1).'</td>
<td class="center">'.readDate(date('d/m/Y',strtotime($v['date'])),['spc'=>' - ']).'</td>
<td class="center">'.(isset($t['detail']['title']) ? uh($t['detail']['title']) : '').'</td> 
<td class="center">
<b class="red">'.Yii::$app->zii->showPrice($item_price,$currency).'</b>
'.($old_item_price>$item_price? '<p class="old-price old_price">'.Yii::$app->zii->showPrice($old_item_price,$currency).'</p>' : '').' 
</td>
<td class="center">'.(isset($filter['status']) ? Yii::$app->t->translate($filter['status'],__LANG__,['default'=>$filter['status']]) : '-').'</td>
<td class="center">';
    			if($filter['to_date'] == '0000-00-00 00:00:00'){
    				$filter['to_date'] = $v['date'];
    			}
    			if(time() - 86400 < strtotime($filter['to_date']) && in_array( $status,['label_available_seat']) ){
$html .= '
<button class="btn btn-xs btn-success"
data-to_date="'.$filter['to_date'].'"
data-id="'.$id.'" 
data-item_id="'.$id.'"  
data-tour_hotel="80" 
data-tour_date_time="'.$v['date'].'" 
data-tour_start="'.(isset($o['tour_start']) ? $o['tour_start'] : 0).'" 
data-tour_type="'.(isset($o['tour_type']) ? $o['tour_type'] : 0).'" 
onclick="btn_book_tour(this);" 
type="button">
<i class="fa fa-hand-o-right"></i> Đặt tour
</button>&nbsp;';

$html .= '
<button class="btn btn-xs btn-danger"
data-to_date="'.$filter['to_date'].'"
data-id="'.$id.'"
data-item_id="'.$id.'"
data-filter_id="'.$filter['id'].'"
data-tour_date_time="'.$v['date'].'"
data-tour_start="'.(isset($o['tour_start']) ? $o['tour_start'] : 0).'"
data-tour_type="'.(isset($o['tour_type']) ? $o['tour_type'] : 0).'"
onclick="call_ajax_function(this);"
data-ajax-action="/sajax"
data-action="view_detail_tour_date2"
type="button">
<i class="fa fa-list-alt"></i> Chi tiết
</button>';

    			}else{
    				$b = Yii::$app->zii->getBox('hotline_number');
    				if(!empty($b)){
    					$hl = '<a href="tel:'. unMark($b['text'],'') .'" class="btn-link"><i class="fa fa-phone"> ' . uh($b['text']) .'</i></a>';
    				}else{
    					$hl = '<i class="fa "> Liên hệ</i>';
    				} 
    				$html .= ($hl);
    			}
$html .= '</td></tr>';
    		}
    	}
    	$html .= '</tbody></table>';
    	return $html;    
    }
    
    
    public function showTextDetail($text = '',$id = 0, $o = []){
    	$regex = [
    			'http://' => SCHEME . '://',
    			'https://' => SCHEME . '://',
    			'"//' => '"' . SCHEME . '://',
    			'{LICH_KHOI_HANH}' => '',
    			'{LICH_KHAI_GIANG}' =>  $this->getLichKhaiGiang2(),
    			'{{LICH_KHOI_HANH_TOUR}}' => $this->getLichKhoiHanhTour($id,$o),
    			'{{CHI_TIET_TOUR}}' => $this->getChiTietTour($id,$o),
    	];
    	
    	
        return str_replace(array_keys($regex), array_values($regex) , $text);
    }
    
    public function getChiTietTour($id, $o = []){
    	$item = isset($o['item']) && !empty($o['item']) ? $o['item'] : [];
    	$origin_item = isset($o['origin_item']) && !empty($o['origin_item']) ? $o['origin_item'] : [];
    	$start_date = isset($o['start_date']) ? $o['start_date'] : '';
    	//
    	if($start_date != ''){
    		$item1 = \app\models\Articles::getItemFilterDateDetail($id,$start_date);
    		$item1 = isset($item1['detail']) ? $item1['detail'] : [];
    		if(!empty($item1)){
    			$item = $item1;
    		}
    	}
    	//
    	$html = '';
    	
    	if(!empty($item)){
    		
    		$tour_other_detail = isset($item['tour_other_detail']) ? $item['tour_other_detail'] : [];

    		if(!empty($tour_other_detail)){
    			$html .= '<div class="tour-detail-day tour-detail-day-'.$start_date.'">';
    			
    			foreach ($tour_other_detail as $k=>$v){
    				$i = getTourInfoCategoryDetail($k);
    				if(isset($v['is_active']) && $v['is_active'] == 'on'){
    				$html .= '<p class="t2-detail-header upper bold"><i class="'.$i['icon'].'"></i> '.($i['title']).'</p>';
    				$html .= isset($v['text']) ? $v['text'] : '';
    				}
    			}
    			$html .= '</div>';
    		}
    	}
    	return $html;
    }
    
    public function getLichKhaiGiang(){
        $b = load_model('branches');
        $l = $b->getLichKG(array('is_active'=>1));
        $html = '<div class="row">';
        if(!empty($l)){
            foreach ($l as $k=>$v){
                //if(isset($v['lich_kg']) && !empty($v['lich_kg'])){
                $html .= '<div class="ibox7 col-sm-12 col-xs-12"><p class="title7 clear"><b class="upper">'.uh($v['name']).' :</b> '.uh($v['address']).'</p></div>
<div class="col-sm-12 col-xs-12 ovs ibox7">
<table class="table lichkg vmiddle table-hover table-bordered f12px"> <thead>
<tr>
<th class="center w100p">Buổi học</th>
<th class="center w50p">Ca</th>
<th class="center w200p">Tên lớp</th>
<th class="center w300p"> Khai giảng</th>
<th class="center">Lịch học </th>
<th class="center">Học phí</th>
<th class="center">Đăng ký</th>
                    
 </tr> </thead> <tbody>';
                if(isset($v['class']) && !empty($v['class'])){
                    foreach ($v['class'] as $k1=>$v1){
                        $html .= '<tr><td class="center">'.uh($v1['date_part']).'</td>
<td class="center">'.uh($v1['time_part']).'</td>
<td class="center bold">'.uh($v1['name']).'</td>
<td class="center">'.uh($v1['begin_text']).'</td>
<td class="center">'.uh($v1['calendar']).'</td>
<td class="center bold red">'.number_format($v1['price']).$this->showCurrency($v1['currency']).'</td>
<td class="center"><a target="_blank" class="areg btn btn-link" href="'.uh($v1['reg_link']).'">Đăng ký ngay</a></td></tr> ';
                    }
                }
                $html .= '</tbody></table></div>';
                //}
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    
    public function getLichKhaiGiang2(){
    	
        $b = new \app\models\Branches();
        $l = $b->getLichKG2(['is_active'=>1]);
        $html = '<div class="row">';
        if(!empty($l)){
            foreach ($l as $k=>$v){ 
                //if(isset($v['lich_kg']) && !empty($v['lich_kg'])){
                $html .= '<div class="ibox7 col-sm-12 col-xs-12"><p class="title7 clear"><b class="upper">'.uh($v['name']).' :</b> '.uh($v['address']).'</p></div>
<div class="col-sm-12 col-xs-12 ovs ibox7">
<table class="table lichkg vmiddle table-hover table-bordered f12px"> <thead>
<tr class="bold green">
<th class="center w250p">Khóa học</th>
                    
<th class="center w200p">Lớp</th>
<th class="center w300p"> Khai giảng</th>
<th class="center">Lịch học </th>
                    
                    
<th class="center">Đăng ký</th>
 </tr> </thead> <tbody>';
                if(isset($v['courses']) && !empty($v['courses'])){
                    foreach ($v['courses'] as $k2=>$v2){
                        if(isset($v2['class']) && !empty($v2['class'])){
                            $rows = count($v2['class'])+1;
                            if($rows  == 1 || count($v2['class']) == 1){
                                $html .= '<tr><td class="center blue" ><a href="'.(isset($v2['url_link']) ? $v2['url_link'] : '#').'" target="_blank">'.uh($v2['title']).'</a>
								<p class=" center mgt5">'.($v2['price2'] > 0 ?
								    'Học phí: <b class="red">'.number_format($v2['price2']).$this->showCurrency($v2['currency']) .'</b>
										'.($v2['price1'] > $v2['price2'] ? ' <i class="mgl15 old-price-df">'.number_format($v2['price1']).$this->showCurrency($v2['currency']) .'</i>' : '')
								    : '').' </p>
								</td>';
								    if(isset($v2['class']) && !empty($v2['class'])){
								        foreach ($v2['class'] as $k1=>$v1){
								            $html .= '
								                
<td class="center bold">'.uh($v1['name']).'</td>
<td class="center">'.uh($v1['begin_text']).'</td>
<td class="center">'.uh($v1['calendar']).'</td>
    
    
<td class="center"><a target="_blank" class="areg btn btn-link" href="'.uh($v1['reg_link']).'">Đăng ký ngay</a></td> ';
								            
								        }
								    }else{
								        $html .= '<td class="center" colspan="4"><i>Sắp mở lớp</i></td>';
								        //$html .= '<td class="center"> </td>';
								    }
								    $html .= '</tr>';
								    
                            }else{
                                
                                
                                //'<p class="bold red center">'.number_format($v1['price']).$this->showCurrency($v1['currency']).'</p>';
                                $html .= '<tr><td class="center blue" rowspan="'.$rows.'"><a href="'.(isset($v2['url_link']) ? $v2['url_link'] : '#').'" target="_blank">'.uh($v2['title']).'</a>
								<p class=" center mgt5">'.($v2['price2'] > 0 ?
								    'Học phí: '.($v2['price1'] > $v2['price2'] ? ' <i class="mgr15 old-price-df">'.number_format($v2['price1']).$this->showCurrency($v2['currency']) .'</i>' : '') .'
										<b class="red">'.number_format($v2['price2']).$this->showCurrency($v2['currency']) .'</b>' : '').' </p>
								</td></tr>';
                                
                                if(isset($v2['class']) && !empty($v2['class'])){
                                    foreach ($v2['class'] as $k1=>$v1){
                                        $html .= '<tr>
                                            
<td class="center bold">'.uh($v1['name']).'</td>
<td class="center">'.uh($v1['begin_text']).'</td>
<td class="center">'.uh($v1['calendar']).'</td>
    
    
<td class="center"><a target="_blank" class="areg btn btn-link" href="'.uh($v1['reg_link']).'">Đăng ký ngay</a></td></tr> ';
                                        
                                    }
                                }else{
                                    $html .= '<tr><td class="" colspan="4"><i>Chưa có lớp</i></td></tr>';
                                }
                                
                            }
                            // end
                        }
                    }
                }
                $html .= '</tbody></table></div>';
                //}
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    
    
    public function getUrl($url = '',$cate_id = 0){
        $url_link = '';
        $item = (new Query())->from('slugs')->where(['url'=>$url,'sid'=>__SID__])->andWhere(['>','state',-2])->one();
        $url_type = isset(Yii::$app->config['seo']['url_config']['type']) ? Yii::$app->config['seo']['url_config']['type'] : 2;
        //view2($url_type);
        if($url_type == 2){
        	return cu([DS. $url]);
        }
        if(!empty($item)){
            if($item['item_type'] == 0) {// menu
                $item_id = $item['item_id'];
            }else{
                $item_id = $cate_id > 0 ? $cate_id : (new Query())->select('category_id')->from('items_to_category')->where(['item_id'=>$item['item_id']])->scalar();
            }
            //
            
            
            switch ($url_type){
                case 1: // Full
                    $c = [];
                    foreach (\app\models\Slugs::getAllParent($item_id) as $k=>$v){
                        //view($v['url']);
                        $c[] = $v['url'];
                    }
                    if($item['item_type'] == 1) {
                        $c[] = $url;
                    }
                    return cu([DS . implode('/', $c)]);
                    break;
                case 3: // 1 cate
                    $c = [(new Query())->select('url')->from('site_menu')->where(['id'=>$item_id])->scalar()];
                    if($item['item_type'] == 1) {
                        $c[] = $url;
                    }
                    return cu([DS . implode('/', $c)]);
                    break;
                default:
                    return cu([DS. $item['url']]);
                    break;
            }
            
            
        }else{
        	return cu([DS. $url]);
        }
        
    }
    public function updateAllUrlLink(){
        $l = (new Query())->from('site_menu')->where(['sid'=>__SID__])->all();
        if(!empty($l)){
            foreach ($l as $k=>$v){
                Yii::$app->db->createCommand()->update('site_menu',[
                    'url_link'=>$this->getUrl($v['url'])
                ],[
                    'id'=>$v['id']
                ])->execute();
            }
        }
        //
        $l = (new Query())->from('articles')->where(['sid'=>__SID__])->all();
        if(!empty($l)){
            foreach ($l as $k=>$v){
                Yii::$app->db->createCommand()->update('articles',[
                    'url_link'=>$this->getUrl($v['url'])
                ],[
                    'id'=>$v['id']
                ])->execute();
            }
        }
    }
    
    public function getVehiclePrice($o = []){
        //
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        $supplier_id = isset($o['supplier_id']) ? $o['supplier_id'] : 0;
        //
    }
    
    private function count_all_child($table, $id = 0,$sid = 0,$c = 0){
        //$m = Yii::$app->db->createCommand("select a.id,a.parent_id from $table as a where a.parent_id=$id" )->queryAll();
        $m = (new Query())->select(['id','parent_id'])->from($table)->where(['parent_id'=>$id]+($sid>0 ? ['sid'=>$sid] : []))->all();
        $c += count($m);
        if(!empty($m)){
            foreach ($m as $k=>$v){
                $c = $this->count_all_child($table,$v['id'],$sid,$c);
            }
        }
        return $c;
    }
    public function update_table_lft($o = [] ){
        global $table_lft;
        @set_time_limit(0);
        @ini_set('mysql.connect_timeout','0');
        @ini_set('max_execution_time', '0');
        $table = $o['table'];
        $id = isset($o['id']) ? $o['id'] : 0;
        $lftx = isset($o['lftx']) ? $o['lftx'] : [];
        $sid = isset($o['sid']) ? $o['sid'] : 0;
        $level = isset($o['level']) && $o['level'] == true ? true : false;
        $orderBy = isset($o['orderBy']) ? $o['orderBy'] : ['title'=>SORT_ASC];
        foreach ((new Query())->from(['a'=>$table])->where(['parent_id'=>$id]+($sid>0 ? ['sid'=>$sid] : []))->orderBy($orderBy)->all() as $k=>$v){
            $lftx[] = $table_lft;
            $lft_c = $table_lft;
            $childs = $this->count_all_child($table,$v['id'],$sid);
            
            if($childs > 0){
                $rgt = ($childs* 2) + 1 + $table_lft;
                
            }else{
                $rgt = ++$table_lft;
                //	if($k == count($l)-1) ++$local_lft;
            }
            $lftx[] = $rgt;
            while(in_array($table_lft, $lftx)){
                $table_lft++;
            }
            if($level ){
                $level1 = $v['parent_id']>0 ? Yii::$app->db->createCommand("select a.level from $table as a where a.id=".$v['parent_id'])->queryScalar() : -1;
                Yii::$app->db->createCommand()->update($table,['level'=>$level1+1],['id'=>$v['id']])->execute();
            }
            $rgt_c = $rgt;
            Yii::$app->db->createCommand()->update($table,[
                'lft'=>$lft_c,
                'rgt'=>$rgt_c
            ],array('id'=>$v['id']))->execute();
            
            $o['id'] = $v['id'];
            $o['lftx'] = $lftx;
            $this->update_table_lft($o);
        }
    }
    
    public function parseCountry($id = 0, $default = 0){
    	
    	if($id < 1) $id = $default;
    	
        $query= (new Query())->select(['id','lft','rgt','title','lang_code','international_title','level','type_id'])
        ->from(\app\modules\admin\models\Local::tableName());
        if($id>0){
            $query->where(['id'=>$id]);
        }else {
            return false;
            $query->where(['is_default'=>1,'parent_id'=>0]);
        }
        $item = $query->one();

        if(!empty($item)){
            $r = (new Query())->select(['id','lft','rgt','title','lang_code','international_title','level','type_id'])
            ->from(\app\modules\admin\models\Local::tableName())->where([
                'and',
                ['<','lft',$item['lft']],
                ['>','rgt',$item['rgt']]
            ])->orderBy(['lft'=>SORT_ASC])->all();
            if(!empty($r)){
                $r[] = $item;
            }else{
                $r[0] = $item;
            }
            return [
                'country'=>$r[0],
                'province'=>isset($r[1]) ? $r['1'] : ['id'=>'-1','title'=>'-','type_id'=>0,'lang_code'=>''],
                'district'=>isset($r[2]) ? $r['2'] : ['id'=>'-1','title'=>'-','type_id'=>0,'lang_code'=>''],
                'ward'=>isset($r[3]) ? $r['3'] : ['id'=>'-1','title'=>'-','type_id'=>0,'lang_code'=>''],
            ];
        }
        return false;
    }
    
    public function getLocalForm($id = 0, $showID = true){
    	$html = ''; 
    	$local = $this->parseCountry($id);
    	//view($local);
    	// get all country
    	if(isset($local['country']['id'])){ 
    		$country = \app\modules\admin\models\Local::getAllCountry();
    		$province = \app\modules\admin\models\Local::getAllCountry(['parent_id'=>$local['country']['id']]);
    		$district = \app\modules\admin\models\Local::getAllCountry(['parent_id'=>$local['province']['id']]);
    		$ward = \app\modules\admin\models\Local::getAllCountry(['parent_id'=>$local['district']['id']]);
    		
    		$html .= '<div class="form-group group-sm30">';
    		
    		// Country
    		$html .= '<div class="col-sm-3 col-xs-3"><div class="row">
			<label class="col-sm-12 control-label">Quốc gia</label>
			<div class="col-sm-12"><select data-selected="'.(isset($local['country']['id']) ? $local['country']['id'] : 0).'" data-target_input=".input-local_id" data-target=".select-input-provinces" onchange="loadChildsProvinces(this)" class="form-control ajax-chosen-select-ajax select-input-country" data-role="chosen-load-country" name="l[country]">';
    		foreach ($country as $c){
    			$html .= '<option '.($c['id'] == $local['country']['id'] ? 'selected' : '').' value="'.$c['id'].'">'.uh($c['title']).'</option>';
    		}    					 		 
			$html .= '</select></div>
			</div></div>';
    		
			// Province
    		$html .= '<div class="col-sm-3 col-xs-3"><div class="row">
			<label class="col-sm-12 control-label">Tỉnh / Thành phố</label>
			<div class="col-sm-12"><select data-selected="'.(isset($local['province']['id']) ? $local['province']['id'] : 0).'" data-level="1" data-target_input=".input-local_id" data-parent_id="'.(isset($local['country']['id']) ? $local['country']['id'] : 0).'" data-target=".select-input-districts" onchange="loadChildsProvinces(this)" data-placeholder="Chọn tỉnh / thành phố" class="select-input-provinces form-control chosen-select" data-role="chosen-load-country" name="l[province]">';
    		if(!empty($province)){
    		foreach ($province as $c){
    			$html .= '<option '.($c['id'] == $local['province']['id'] ? 'selected' : '').' value="'.$c['id'].'">'. showLocalName(uh($c['title']),$c['type_id']).'</option>';
    		}} 		 
			$html .= '</select></div>
			</div></div>';
			
			
			// District
			$html .= '<div class="col-sm-3 col-xs-3"><div class="row">
			<label class="col-sm-12 control-label">Quận / Huyện</label>
		<div class="col-sm-12"><select data-selected="'.(isset($local['district']['id']) ? $local['district']['id'] : 0).'" data-level="2" data-target_input=".input-local_id" data-parent_id="'.(isset($local['province']['id']) ? $local['province']['id'] : 0).'" data-target=".select-input-wards" onchange="loadChildsProvinces(this)" data-placeholder="Chọn quận / huyện" class="select-input-districts form-control chosen-select" data-role="chosen-load-country" name="l[district]">';
			if(!empty($district)){
				foreach ($district as $c){
					$html .= '<option '.($c['id'] == $local['district']['id'] ? 'selected' : '').' value="'.$c['id'].'">'. showLocalName(uh($c['title']),$c['type_id']).'</option>';
				}}
				$html .= '</select></div>
			</div></div>';
				
			// Ward
			$html .= '<div class="col-sm-3 col-xs-3"><div class="row">
			<label class="col-sm-12 control-label">Phường / Xã</label>
			<div class="col-sm-12"><select data-selected="'.(isset($local['ward']['id']) ? $local['ward']['id'] : 0).'" data-level="3" data-target_input=".input-local_id" data-parent_id="'.(isset($local['district']['id']) ? $local['district']['id'] : 0).'" onchange="loadChildsProvinces(this)" data-placeholder="Chọn phường / xã" class="select-input-wards form-control chosen-select" data-role="chosen-load-country" name="l[ward]">';
			if(!empty($ward)){
				foreach ($ward as $c){
					$html .= '<option '.($c['id'] == $local['ward']['id'] ? 'selected' : '').' value="'.$c['id'].'">'. showLocalName(uh($c['title']),$c['type_id']).'</option>';
				}
			}
			$html .= '</select></div>
			</div></div>';
			
    		if($showID){
    			$html .= '<input type="hidden" name="f[local_id]" value="'.$id.'" class="input-local_id"/>';
    		}
    		$html .= '</div>';
    	}    	 
    	return $html;
    }
    
    
    
    public function getMember($id = 0){
        $query = (new Query())
        ->from(['a'=>\app\modules\admin\models\Customers::tableName()])
        ->where(['a.sid'=>__SID__,'a.id'=>$id])
        ->andWhere(['>','a.state',-2]);
        return $query->one();
    }
    
    public function getMembers($o = []){
        //$type_id = isset($o['type_id']) ? $o['type_id'] : TYPE_ID_CUS;
        $limit = isset($o['limit']) && is_numeric($o['limit']) ? $o['limit'] : 30;
        $filter_text = isset($o['filter_text']) ? $o['filter_text'] : '';
        $p = isset($o['p']) && is_numeric($o['p']) ? $o['p'] : 1;
        $count  = isset($o['count']) && $o['count'] == false ? false   : true;
        $offset = ($p-1) * $limit;
        $order_by = isset($o['order_by']) ? $o['order_by'] : ['a.position'=>SORT_ASC, 'a.fname'=>SORT_ASC];
        $parent_id = isset($o['parent_id']) ? $o['parent_id'] : -1;
        $is_active = isset($o['is_active']) ?  $o['is_active'] : 1;
        
        $not_in = isset($o['not_in']) ? $o['not_in'] : [];
        $in = isset($o['in']) ? $o['in'] : [];
        if(!is_array($in) && $in != "") $in = explode(',', $in);
        if(!is_array($not_in) && $not_in != "") $not_in = explode(',', $not_in);
        //view($o,true);
        $type_id = isset($o['type_id']) ? $o['type_id'] : TYPE_ID_CUS ;
        $place_id = isset($o['place_id']) ? $o['place_id'] : 0;
        $local_id = isset($o['local_id']) ? $o['local_id'] : 0;
        $query = (new Query())
        ->from(['a'=>\app\modules\admin\models\Customers::tableName()])
        //->leftJoin(['b'=>self::tableLocal()])
        ->where(['a.sid'=>__SID__])
        ->andWhere(['>','a.state',-2]);
        //->andWhere(['in','a.type_code',3]);
        if(strlen($filter_text) > 0){
            $query->andFilterWhere(['or',
                ['like', 'a.name', $filter_text],
                ['like', 'a.short_name', $filter_text],
            ]);
        }
        if(is_numeric($type_id) && $type_id > -1){
            $query->andWhere(['in','a.type_id',$type_id]);
        }
        if(is_numeric($is_active) && $is_active > -1){
            $query->andWhere(['a.is_active'=>$is_active]);
        }
        if(!empty($in)){
            $query->andWhere(['a.id'=>$in]);
        }
        if(!empty($not_in)){
            $query->andWhere(['not in','a.id',$not_in]);
        }
        if($place_id > 0){
            $query->andWhere(['in','a.id',(new Query())
                ->select('customer_id')
                ->from('customers_to_places')
                ->where(['place_id'=>$place_id])]);
        }
        if($local_id > 0){
            $query->andWhere(['a.local_id'=>$local_id]);
        }
        
        $c = 0;
        if($count){
            $c = $query->count(1);
        }
        $query->select(['a.*'])
        ->orderBy($order_by)
        ->offset($offset)
        ->limit($limit);
        //view($query->createCommand()->getSql());
        $l = $query->all();
        return [
            'listItem'=>$l,
            'total_records'=>$c,
            'total_pages'=>ceil($c/$limit),
            'limit'=>$limit,
            'p'=>$p,
        ];
    }
    
    
    
    
    public function getProgramInfoFromDate($date = '0000-00-00', $supplier_id = 0){
        if(!check_date_string($date)){
            $date = date('Y-m-d');
        }else{
            $date = ctime(['string'=>$date,'format'=>'Y-m-d']);
        }
        // get quotation
        $quotation = \app\modules\admin\models\Suppliers::getQuotation($date,$supplier_id);
        $quotation_id = !empty($quotation) ? $quotation['id'] : 0;
        
        
    }
    
    public function getSupplierIDFromService($id = 0,$type = 0){
        $supplier_id = 0;
        switch ($type){
            case TYPE_ID_SCEN: // Thang canh
            case TYPE_ID_TRAIN: // Ve tau hoa
                $supplier_id = (new Query())->from('tickets_to_suppliers')->where([
                'ticket_id'=>$id
                ])->select('supplier_id')->scalar();
                break;
            case TYPE_ID_GUIDES: //HDV
                
                $supplier_id = (new Query())->from('guides_to_suppliers')->where([
                'guide_id'=>$id
                ])->select('supplier_id')->scalar();
                break;
            case TYPE_ID_SHIP:
                $supplier_id = (new Query())->from('distances_to_suppliers')->where([
                'item_id'=>$id
                ])->select('supplier_id')->scalar();
                break;
            case TYPE_ID_TEXT: break;
            default: $supplier_id = $id; break;
        }
        return $supplier_id;
    }
    
    public function getSupplierServiceDetail($id = 0,$type = 0){
        $item = [];
        //view($type);
        switch ($type){
            case TYPE_ID_SCEN: // Thang canh
            case TYPE_ID_TRAIN: // Vé tàu hỏa
                $item = \app\modules\admin\models\Tickets::getItem($id);
                break;
            case TYPE_ID_GUIDES: //HDV
                $item = \app\modules\admin\models\Guides::getGuide($id);
                break;
            case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
                $item = \app\modules\admin\models\RoomsCategorys::getItem($id);
                break;
            case TYPE_ID_SHIP:
                $item = \app\modules\admin\models\Distances::getItem($id);
                break;
            case TYPE_ID_REST:
                $item = \app\modules\admin\models\Menus::getItem($id);
                break;
        }
        return $item;
    }
    
    /*
     * Lấy dịch vụ mặc định của nhà cung cấp
     * Áp dụng cho Khách sạn - Nhà hàng - Tàu ngủ - Thuyền bè
     */
    public function getSupplierDefaultServiceItem($supplier_id = 0){
        
    }
    
    public function getSupportWithCategory(){
        $r = $this->getConfigs('SUPPORTS');
        $cates = isset($r['support_categorys']) ? $r['support_categorys'] : [];
        $supports = isset($r['supports']) ? $r['supports'] : [];
        if(!empty($cates)){
            foreach ($cates as $k=>$v){
                $cates[$k]['listItem'] = [];
                if(!empty($supports)){
                    foreach ($supports as $v1){
                        if(isset($v1['category_id']) && $v1['category_id'] == $v['id']){
                            $cates[$k]['listItem'][] = $v1;
                        }
                    }
                }
            }
        }
        return $cates;
    }
    
    public function getSupports($o = []){
        $r = $this->getConfigs('SUPPORTS');
        return isset($r['supports']) ? $r['supports'] : [];
    }
    
    public function zipData($source, $destination) {
        if (extension_loaded('zip')) {
            if (file_exists($source)) {
                $zip = new ZipArchive();
                if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                    $source = realpath($source);
                    if (is_dir($source)) {
                        $iterator = new RecursiveDirectoryIterator($source);
                        // skip dot files while iterating
                        $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
                        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $file) {
                            $file = realpath($file);
                            if (is_dir($file)) {
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                            } else if (is_file($file)) {
                                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                            }
                        }
                    } else if (is_file($source)) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }
                return $zip->close();
            }
        }
        return false;
    }
    
    public function setNotificationDateExpired($time_left = SHOP_TIME_LEFT){
        $session = Yii::$app->session;
        $state = false;
        if (!$session->has('time_cookie_sexpired')) {
            switch (true){
                case ($time_left < 60 && $time_left > 30): // Thông báo lần 1
                    $state = 1;
                    break;
                case ($time_left > 15 && $time_left < 31): // Thông báo lần 2
                    $state = 2;
                    break;
                case ($time_left > 5 && $time_left < 16): // Thông báo lần 3
                    $state = 3;
                    break;
                case ($time_left > 2 && $time_left < 6): // Thông báo lần 4
                    $state = 4;
                    break;
                case ($time_left > -1 && $time_left < 3): // Thông báo lần 5
                    $state = 5;
                    break;
                case ($time_left > -5 && $time_left < -1): // Thông báo tạm ngưng dịch vụ
                    $state = 6;
                    break;
                case ($time_left > -16 && $time_left < -14): // Thông báo ngừng toàn bộ dịch vụ
                    $state = 7;
                    break;
            }
            if((new \yii\db\Query())->from(['a'=>'cronjobs'])->where([
                'type_code'=>SHOP_EXPIRED,'sid'=>__SID__,'item_id'=>$state
            ])->count(1) == 0){
                Yii::$app->db->createCommand()->insert('cronjobs',[
                    'type_code'=>SHOP_EXPIRED,'sid'=>__SID__,'item_id'=>$state
                ])->execute();
            }
            
            $session->set('time_cookie_sexpired', $state);
            
        }
        
    }
    public function showTourTime($day = 0, $night = 0){
        $text = $day > 0 ? $day .' '.getTextTranslate(19).' ' : '';
        $text .= $night > 0 ? $night .' '.getTextTranslate(20) : '';
        return $text;
    }
    
    public function getFilters($o = []){
        return Filters::getFilters($o);
    }
    
    public function getDeparturePlace(){
        return [];
    }
    
    public function getCategory(){
        return [];
    }
    public function get_departure_local(){
        return [];
    }
    public function getTourGroupsSplit(){
        return [];
    }
    
    public function showFullLocal($id, $address = '',$o=[]){
    	$showCountry = isset($o['display_country']) && !$o['display_country'] ? false : true;
    	
    	$language = isset($o['language']) ? $o['language'] : ROOT_LANG;
    	
    	$igrones = isset($o['igrones']) ? $o['igrones'] : [Yii::$app->local->location];
    	
        $local = $this->parseCountry($id);
        if(!!empty($local)) return '-';
        if($address != ""){
            //$address .= ', ';
        }
        //////////
        if(isset($local['ward']) && !empty($local['ward']) && trim($local['ward']['title']) != "-"){
            $address .= (trim($address) != '' ? ', ' : "") . showLocalName(uh($local['ward']['title']),$local['ward']['type_id']);
            
        }
        //////////
        if(isset($local['district']) && !empty($local['district'])  && trim($local['district']['title']) != "-"){
        	$address .= (trim($address) != '' ? ', ' : "") .showLocalName(uh($local['district']['title']),$local['district']['type_id']);
        }
        //////////
        if(isset($local['province']) && !empty($local['province'])  && trim($local['province']['title']) != "-"){
        	$address .= (trim($address) != '' ? ', ' : "") .showLocalName(uh($local['province']['title']),$local['province']['type_id']);
        }
        //////////-
       // view($showCountry);
        if($showCountry && isset($local['country']) && !empty($local['country']) && trim($local['country']['title']) != "-" && !in_array($local['country']['id'], $igrones)){
        	$address .= (trim($address) != '' ? ', ' : "") .showLocalName(uh($local['country']['title']),$local['country']['type_id']);
        }
        //////////
        return $address;
    }
    
    
    public function showCommentLikeBlock($o = []){
        $time = isset($o['time']) ? $o['time'] :date('Y-m-d H:i:s');
        $liked = isset($o['liked']) ? $o['liked'] : 0;
        $member_id = isset($o['created_by']) ? $o['created_by'] : 0;
        $item_id = isset($o['item_id']) ? $o['item_id'] : 0;
        $parent_id = isset($o['parent_id']) ? $o['parent_id'] : 0;
        $reply_id = isset($o['reply_id']) ? $o['reply_id'] : 0;
        $comment_id = isset($o['comment_id']) ? $o['comment_id'] : 0;
        $type_id = isset($o['type_id']) ? $o['type_id'] : 0;
        if($type_id == 1){
            $member = \app\modules\admin\models\Users::getItem($member_id);
        }else{
            $member = \common\models\Member::getItem($member_id);
        }
        $role = 'like';
        if((new Query())->from('comments_liked')->where(['item_id'=>$comment_id,'customer_id'=>Yii::$app->member->id])->count(1)>0){
            $role = 'unlike';
        }
        
        $html = '<div class="user_status width_common fl100">
				'.(isset($member['icon']) ? '<a class="avata_coment" href="/members" target="_blank">'.getImage([
				    'src'=>$member['icon'],
				    'w'=>30,
				    'alt'=>''
				]).'</a>' : '<a class="avata_coment"><img class="img_avatar" src="https://s.vnecdn.net/myvne/i/v1/graphics/img_60x60.gif"></a>').'
				    
				    
        		<span class="left txt_666 txt_11">
        		<a class="nickname txt_666" href="javascript:;" title=""><b title="'.($type_id == 1 ? 'Quản trị viên' : '').'" class="'.($type_id == 1 ? 'red' : '').'">'.(isset($member['name']) ? $member['name'] : '').'</b></a> - '.count_time_post($time).'</span>
        		    
        		<p class="txt_666 txt_11 right block_like_web">
        		    
        		<a class="txt_blue txt_11 link_reply" onclick="showQuickReplyForm(this);" href="javascript:;" data-comment_id="'.$comment_id.'" data-item_id="'.$item_id.'" data-parent_id="'.$parent_id.'">
        		<i class="fa fa-reply">&nbsp;</i> <b>Trả lời</b></a> &nbsp;|&nbsp;
        		    
        		<a class="txt_666 txt_11 link_thich '.$role.'" href="javascript:;" onclick="likeComment(this);" data-role="'.$role.'" data-liked="'.$liked.'" data-comment_id="'.$comment_id.'" data-item_id="'.$item_id.'" data-parent_id="'.$parent_id.'">
        		'.($role == 'like' ? '<i class="fa fa-thumbs-o-up"></i> Thích' : '<i class="fa fa-thumbs-o-down"></i> Bỏ thích').'</a>&nbsp;
        		    
        				<a class="txt_666 txt_11 total_like '.$role.'" href="javascript:;"  data-item_id="'.$item_id.'" data-parent_id="'.$parent_id.'">('.number_format($liked).')</a>
        		<span class="hide">
        						&nbsp;|&nbsp;
        				    
        		<a class="txt_blue txt_11 report_bad" rel="22196932" href="#" title="Vi phạm" alt="Vi phạm">
        		<i class="fa fa-minus-circle"></i> Vi phạm</a>&nbsp;|&nbsp;
        				    
        		<a href="javascript:;" rel="22196932" class="share_cmt_fb txt_blue txt_11"><i class="fa fa-share-alt"></i> Chia sẻ</a>
        		</span>
        		</p>
        		</div>';
				return $html;
    }
    
    public function getReplyName($reply_id = 0){
        $comment = (new Query())->from(\app\models\Comments::tableName())->where(['id'=>$reply_id])->one();
        if($comment['type_id'] == 1){
            $member = \app\modules\admin\models\Users::getItem($comment['created_by']);
        }else{
            $member = \common\models\Member::getItem($comment['created_by']);
        }
        
        if(!empty($member)){
            return '<span class="reply_name">@'.uh($member['name']).': </span>';
        }
        return '';
    }
    
    public function showComments($item_id){
        $html = '<div class="width_common fl100 space_bottom_20 box-item-comments f12e">
    <div class="block_show_commen width_common fl100">';
        
        $html .= '<div class="title_show txt_666 fl100">
            <div class="ykien_vne" style="">
        		<div class="left fl"><strong>Ý kiến khách hàng</strong> (<label class="total_comment">'.number_format(\app\models\Comments::countAllComment($item_id)).'</label>) </div>
				<div class="filter_coment fr"><a href="#" rel="time">Mới nhất</a> | <a href="#" class="active" rel="like">Quan tâm nhất</a></div>
				<div class="filter_coment_mobile"><select><option value="time">Mới nhất</option>
        		<option value="like" selected="">Quan tâm nhất</option></select></div>
			</div>
        </div>';
        
        $html .= '<div class="main_show_comment width_common fl100"><div class="main_show_comment box_width_common">';
        
        $l1 = \app\models\Comments::getList([
            'item_id'=>$item_id,
            'parent_id'=>0,
            'limit'=>20
        ]);
        
        if(!empty($l1['listItem'])){
            foreach ($l1['listItem'] as $k1=>$v1){
                $v1['comment_id'] = $v1['id'];
                $v1['parent_id'] = $v1['id'];
                $html .= '<div class="comment_item '.($k1%2==1 ? 'hight_light' : '').'" data-time="'.strtotime($v1['time']).'">
        		<div class="right width_comment_item width_common fl100">
        		<div class="width_common fl100">
        		<p id="comment_'.$v1['id'].'" class="content_less" rel="content_more">'.uh($v1['text']).'</p>
        		    
        		    
        		    
        		'.$this->showCommentLikeBlock($v1).'
        		    
        		</div>
        		    
        		<div class="sub_comment"><div class="reply_form_comment reply_form_comment_'.$v1['id'].'"></div>';
                $l2 = \app\models\Comments::getList([
                    'parent_id'=>$v1['id'],
                    'limit'=>20,
                    'item_id'=>$item_id
                ]);
                if(!empty($l2['listItem'])){
                    foreach ($l2['listItem'] as $k2=>$v2){
                        
                        $html .= '<div class="subcomment_item width_common">
        				<p id="comment_'.$v2['id'].'" class="full_content">';
                        if($v2['reply_id']>0){
                            $html .= $this->getReplyName($v2['reply_id']);
                            //$html .= '<span class="reply_name">@Thu Hương: </span>';
                        }
                        $v2['comment_id'] = $v2['id'];
                        $html .= uh($v2['text']).'</p>
                            
        				'.$this->showCommentLikeBlock($v2).'
        				    
       		<div class="reply_form_comment_'.$v2['id'].'"></div>
        				</div>';
                    }
                }
                
                
                $html .= '<div class="txt_view_more hide width_common"><a href="#" class="txt_blue view_all_reply" rel="22195873" data-total=" 9" data-offset="1">Xem tất cả 9 trả lời</a></div>
                    
        	</div>
                    
                    
                    
       </div><div class="clear"></div></div>';
                
            }
        }
        
        
        
        
        $html .= '</div><div class="clear"></div>';
        if($l1['total_pages']>1){
            $html .= '
            <div class="cmt-paginator">
                <div class="pagination_news right">
        		<a href="javascript:;" class="cmt-pagination pagination_btn pa_prev" rel="1">
        		<i class="fa fa-angle-left "></i>
        		</a>
        		<a class="cmt-pagination" href="javascript:;" rel="1">1</a>
        		<a class="active cmt-pagination" href="javascript:;" rel="2">2</a>
        		<a class="cmt-pagination" href="javascript:;" rel="3">3</a>
        		<a class="cmt-pagination" href="javascript:;" rel="4">4</a>
        		<a class="cmt-pagination" href="javascript:;" rel="5">5</a>
        		<a href="javascript:;" class="cmt-pagination pagination_btn pa_next" rel="3"><i class="fa fa-angle-right"></i></a>
        		</div>
            </div>';
        }
        
        $html .= $this->showCommentForm([
            'item_id'=>$item_id
        ]);
        
        
        $html .= '</div></div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    public function showCommentForm($o = []){
        $a = ['item_id','parent_id','reply_id' ];
        foreach ($a as $b){
            $$b = isset($o[$b]) ? $o[$b] : 0;
        }
        
        
        
        $html = '<div class="block_input_comment width_common fl100">
                <div class="input_comment">
                    <form data-action="sajax" action="" method="post" onsubmit="return ajaxSubmitForm(this);">
                        <textarea name="f[text]" class="left required" required="required" placeholder="Ý kiến của bạn"></textarea>
                        <div class="width_common fl100 block_relative">
                            <div class="right block_btn_send">
                                <input type="submit" value="Gửi" class="btn_send_comment" >
                            </div>';
        if(!Yii::$app->member->isGuest){
            $member = \common\models\Member::getItem(Yii::$app->member->id);
            //view($member['id']);
            $html .= '<div class="like_google" style="display: block;">
        		<div class="google_name">
        		'.(isset($member['icon']) ? '<a class="avata_coment" href="/members" target="_blank">'.getImage([
        		    'src'=>$member['icon'],
        		    'w'=>30,
        		    'alt'=>''
        		]).'</a>' : '').'
        		    
         		<span>'.Yii::$app->member->getUserName().'</span>
        		</div></div>';
        }
        
        $html .= '</div><div class="clear">&nbsp;</div>
        		<input type="hidden" name="_csrf-frontend" value="'.Yii::$app->request->csrfToken.'"/>
        		<input type="hidden" name="action" value="post_item_comment"/>
        		<input type="hidden" name="item_id" value="'.$item_id.'"/>
        		<input type="hidden" name="parent_id" value="'.$parent_id.'"/>
        		<input type="hidden" name="reply_id" value="'.$reply_id.'"/>
                </form>
        		    
                </div>
            </div>';
        return $html;
        
    }
    
    public function showTags($tags = ''){
        if($tags != ''){
            if(!is_array($tags)){
                $tags = explode(',', $tags);
            }
            if(!empty($tags)){
                $html = '<div class="block_tag fl100 width_common space_bottom_20">
              	<div class="txt_tag"><i class="ic fa fa-tag"></i>&nbsp;Tags</div>';
                foreach ($tags as $tag){
                    $html .= '<h4><a href="/search?q='.$tag.'" title="'.uh($tag).'" class="tag_item">'.uh($tag).'</a></h4>';
                }
                $html .= '</div>';
            }
            
            return $html;
        }
    }
    
    public function showItemTags($item_id){
        $tags = \app\modules\admin\models\Tags::getItemTags($item_id);
        $html = '';
        if(!empty($tags)){
            $html = '<div class="block_tag fl100 width_common space_bottom_20">
           	<div class="txt_tag"><i class="ic fa fa-tag"></i>&nbsp;Tags</div>';
            foreach ($tags as $tag){
                $html .= '<h4><a href="/tag/'.Yii::$app->controller->action->id.'/tag-'.$tag['id'].'" title="'.uh($tag['title']).'" class="tag_item">'.uh($tag['title']).'</a></h4>';
            }
            $html .= '</div>';
        }
        
        return $html;
        
    }
    
    
    public function getAllDepartureCalendar(){
        $html = '<div class="f12e departure_scheduler_from_item_price_all">';         
        
        $tour_category = getParam('tour_category',0)>0 ? getParam('tour_category',0) : \app\models\Filters::getFilterIdFromValue(2, 'tour_category');
                
        
        $html .= '<ul class="nav nav-tabs desktop-only" role="tablist">
		<li role="presentation" class="'.(getParam('tab_open','tab01') == 'tab01' ? 'active' : '').'"><a href="#tab01" aria-controls="home" role="tab" data-toggle="tab">Lịch khởi hành</a></li>
		<li role="presentation" class="'.(getParam('tab_open') == 'tab02' ? 'active' : '').'"><a href="#tab02" aria-controls="profile" role="tab" data-toggle="tab">Tour khởi hành hàng ngày</a></li>
		<li role="presentation" class="'.(getParam('tab_open') == 'tab03' ? 'active' : '').'"><a href="#tab03" aria-controls="profile" role="tab" data-toggle="tab">Tour khởi hành hàng tuần</a></li>
		    
				</ul>
  		<div class="tab-content">';
        
        $html .= '<div role="tabpanel" class="tab-pane '.(getParam('tab_open','tab01') == 'tab01' ? 'active' : '').'" id="tab01"><div id="departure_scheduler_from_item_price_all">';
        
        $html .= '<div class="mobile-only tab-mobile-header upper bold">Lịch khởi hành</div>';
        
        $html .= '<div class="lich-khoi-hanh-search"><form method="get" action="" class="form-inline form-search-lich-kh1">
  <div class="form-group">
            
    <select onchange="submitFormTarget(this);" data-form-submit=".form-search-lich-kh1" data-action="change_item_tour_category" name="tour_category" class="form-control input-sm select2" data-search="hidden" style="width: 100%" >';
        
        foreach (\app\modules\admin\models\Filters::getFilters([
            'code'=>'tour_category','parent_id'=>0
        ]) as $v1){
            
            //$html .= '<option value="0">'.uh($v1['title']).'</option>';
            foreach (\app\modules\admin\models\Filters::getFilters([
                'parent_id'=>$v1['id']
            ]) as $v2){
                $html .= '<option '.($tour_category == $v2['id'] ? 'selected="selected"' : '').' value="'.$v2['id'].'">'. $v2['title'].'</option> ';
            }
            
        }
        
        
        $html .= '</select>
  </div>
            
        		<div class="form-group" title="Điểm khởi hành">
            
    <select onchange="submitFormTarget(this);" data-form-submit=".form-search-lich-kh1" placeholder="Điểm khởi hành" data-action="change_item_tour_category" name="tour_start" class="form-control input-sm select2" data-search="hidden" style="width: 100%" ><option value="0">.: Điểm khởi hành :.</option>';
        $l = \app\models\Articles::getItemFromFilterDate([
            'type'=>'tours',
            'filter_code'=>['tour_type'],
            'filter_value'=>[1],
            'tour_category'=>$tour_category,
            'limit'=>1000000
        ]);
        $t_in = [0];
        if(!empty($l['listItem'])){
            foreach ($l['listItem'] as $v1){
                foreach (\app\modules\admin\models\Filters::getFilters([
                    'parent_id'=>-1,'code'=>'tour_start','item_id'=>$v1['id']
                ]) as $v2){
                    if(!in_array($v2['id'], $t_in))
                        $t_in[] = $v2['id'];
                }
            }
        }
        //view($t_in);
        
        foreach (\app\modules\admin\models\Filters::getFilters([
            'code'=>'tour_start','parent_id'=>0,
            
        ]) as $v1){
            
            //$html .= '<option value="0">'.uh($v1['title']).'</option>';
            foreach (\app\modules\admin\models\Filters::getFilters([
                'parent_id'=>$v1['id'],
                'check_item_existed'=>true,
                'in'=>$t_in
            ]) as $v2){
                $html .= '<option '.(getParam('tour_start') == $v2['id'] ? 'selected="selected"' : '').' value="'.$v2['id'].'">'. $v2['title'].'</option> ';
            }
            
        }
        
        $from_date = getParam('from_date');
        if(ctime(['string'=>$from_date,'return_type'=>1]) < time()){
            $from_date = date('d/m/Y');
        }
        $html .= '</select>
  </div>
            
            
  <div class="form-group desktop-only">
    <input onblur="submitFormTarget(this);" data-form-submit=".form-search-lich-kh1" data-minDate="'.(date('Y-m-d',time()+86400)).'" data-month="2" type="text" name="from_date" value="'.(getParam('from_date') != '' ? $from_date : '').'" data-old="'.(getParam('from_date') != '' ? $from_date : '').'" class="form-control input-sm datepicker2 mw100p" data-format="d/m/Y" placeholder="Từ ngày" title="Ngày khởi hành">
  </div>
  <div class="form-group desktop-only">
    <label class="font-normal "> đến </label>
    <input onblur="submitFormTarget(this);" data-form-submit=".form-search-lich-kh1"  data-minDate="'.(date('Y-m-d',time()+86400)).'" data-month="2" type="text" name="to_date" value="'.getParam('to_date').'" name="to_date" data-old="'.getParam('to_date').'" class="form-control input-sm datepicker2 mw100p" data-format="d/m/Y" placeholder="Đến ngày" title="Ngày khởi hành">
  </div>
        
  <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-find fa-search"></i> Tìm tour</button>
</form></div>';
        
        $html .= '<div class="table-responsive"><table class="table_departure_scheduler table table-hover table-bordered vmiddle">';
        $html .= '<thead><tr class="bold">
    				<th class="center">Ngày K.H</th>
    				<th class="center">Điểm K.H</th>
    				<th class="center">Mã tour</th>
    				<th class="center">Tên tour</th>
    				<th class="center">Giá <i class="font-normal">(VNĐ)</i></th>
    				<th class="center">Tình trạng</th>
            
    				<th class="center">Đặt tour</th>'.(Yii::$app->user->id>0 ? '<th class="center">ĐVTC</th>' : '').'
    				    
    				</tr></thead><tbody>';
        $l = \app\models\Articles::getItemFromFilterDate([
            'type'=>'tours',
            'filter_code'=>['tour_type'],
            'filter_value'=>[1],
            'tour_category'=>$tour_category,
            
        ]+$_GET);
        $existed = [];
        if(!empty($l['listItem'])){
            foreach ($l['listItem'] as $k=>$v){
                if(!in_array($v['id'], $existed)){
                    $existed[] = $v['id'];
                    $df_tour_type = \app\models\Articles::getDefaultFilter($v['id'],'tour_type');
                    
                    $df_start = \app\models\Articles::getDefaultFilter($v['id'],'tour_start');
                    $df_hotel = \app\models\Articles::getDefaultFilter($v['id'],'tour_hotel');
                    $link = isset($v['url_link']) ? $v['url_link'] : cu(['/' . $v['url']]);
                    $filter = \app\models\Articles::getItemFilter($v['id'],$v['filter_id']);
                    
                    $tour_date_time = $v['filter_date'];
                    $code = 'tour_date_time_'.  $tour_date_time;
                    
                    $price_code_partner = 'partner_price_' .$code;
                    $price_partner = \common\models\Articles::getItemPrice($price_code_partner,$v['id']);
                    
                    
                    $p = \app\models\Articles::getItemPrice($v['id'], $code);
                    // view($p);
                    if(!empty($p)){
                        //if($currency == $p['currency']){
                        $v['price2'] = $p['price'];
                        //}
                        //$currency = $p['currency'];
                        
                    }
                    
                    $code = 'tour_start_'.$df_start['id'];
                    $p = \app\models\Articles::getItemPrice($v['id'], $code);
                    if(!empty($p)){
                        //if($currency == $p['currency']){
                        $v['price2'] += $p['price'];
                        //}
                        //$currency = $p['currency'];
                        
                    }
                    $date_list = \app\modules\admin\models\Filters::getFilters([
                        //'parent_id'=>post('id',0),
                        'min_date'=>$from_date != "" ? ctime(['string'=> $from_date,'format'=>'Y-m-d']) : date('Y-m-d'),
                        'item_id'=>$v['id'],
                        'code'=>'tour_date_time',
                        'orderBy'=>['a.date'=>SORT_ASC,'a.position'=>SORT_ASC,'a.title'=>SORT_ASC]
                    ]);
                    
                    $html .= '<tr>
    				<td class="center">';
                        /*
    				<select onchange="change_item_price_detail2(this)"
						data-parent="#departure_scheduler_from_item_price_all"
    					data-item_id="'.$v['id'].'"
    					data-tour_type="'.$df_tour_type['id'].'"
    					data-tour_date_time="'.$v['filter_date'].'"
    					data-placeholder="'.getTextTranslate(227).'"
    					data-target=".t_price_change" data-xtarget
    					data-id="'. $v['id'].'"
    					data-tour_start="'.$df_start['id'].'"
    					data-field="tour_date_time"
    					data-disable_search_threshold="10"
    					class="input-select-tour-s'.$v['id'].' input-tour-detail-select-tour-start w100 h26p border-none transparent" name="tour_date_time" >';
                   */
                    if(!empty($date_list)){
                    	
                    	$html .='<p>'.date('d/m/Y',strtotime($date_list[0]['date'])).'</p>';
                    	
                        foreach ($date_list  as $v2){
                            
                           // $html .= '<option '.(getParam('tour_date_time') == date('d/m/Y',strtotime($v2['date'])) ? 'selected' : '').' value="'.date('d/m/Y',strtotime($v2['date'])).'">'.date('d/m/Y',strtotime($v2['date'])).'</option>';
                            
                        }}
                        
                        $places = Yii::$app->frontend->tour->getTourPlaces($v['id'],2);
                        
                        $starts = [];
                        
                        if(!empty($places)){
                            foreach ($places as $p1){
                                $starts[] = $p1['title'];
                            }
                        }
                        
                        
                        // Chừng nào cá mập hết cắn cáp thì mấy ông server hosting VN sẽ ... chết đói
                        
                       // $html .= '</select>'; 
if(count($date_list)>1){
	$html .= '<p><a href="#"
data-tour_type="'.$df_tour_type['id'].'"
data-tour_category="'.$tour_category.'"
data-id="'.$v['id'].'" data-item_id="'.$v['id'].'"
data-tour_hotel="'.(isset($df_hotel['id']) ? $df_hotel['id'] : -1).'"
data-tour_date_time="'.$v['filter_date'].'"
data-tour_start="'.$df_start['id'].'"
data-ajax-action="/sajax"
data-action="tour_get_all_day"
onclick="call_ajax_function(this); return false;" type="button" class="fl mgl5 italic input-select-tour-s'.$v['id'].'">Ngày khác</a></p>';
}
$html .= '</td>
                            
    				<td class="center">'.(!empty($starts) ? implode(' | ', $starts) : '-').'</td> 
                            
        			<td class="center"><a target="_blank" href="'.$link.'" class="" title="'.uh($v['title']).'">'.$v['code'].'</a></td>
        			<td class="aleft"><a target="_blank" href="'.$link.'" class="" title="'.uh($v['title']).'">'.uh(isset($v['short_title'])  && $v['short_title'] != "" ? $v['short_title'] : $v['title']).'</a></td>
    				<td class="center"><b class="red tour_pricex_rp input-tour-detail-select-tour-price-'.$v['id'].'">'.Yii::$app->zii->showPrice($v['price2'],$v['currency']).'</b></td>
    				<td class="center">'.(isset($filter['status']) ? Yii::$app->t->translate($filter['status'],__LANG__,['default'=>$filter['status']]) : '').'</td>
    				    
    				<td class="center"><div class="center nowrap input-respon-tour-expired-'.$v['id'].'">';
                        $btn = true;
                        if(strtotime($filter['to_date']) + 86400 > time() && in_array( $status,['label_available_seat']) ){
                        	//$btn = true;
                        }else{
                        	$btn = false;
                        }
if($btn){
$html .= '<a href="'.$link.'" data-tour_type="'.$df_tour_type['id'].'"
    						data-id="'.$v['id'].'"
							data-item_id="'.$v['id'].'"
    						data-tour_hotel="'.(isset($df_hotel['id']) ? $df_hotel['id'] : -1).'"
    						data-tour_date_time="'.$v['filter_date'].'"
    						data-tour_start="'.$df_start['id'].'"
    						onclick="btn_book_tour(this);"
    						type="button" class="input-select-tour-s'.$v['id'].' btn-book-tour-rp btn-link btn-book-tour-'.$v['id'].' btn btn-xs btn-warning"><i class="glyphicon glyphicon-hand-right"></i> Chi tiết</a>';
}else{
	$b = Yii::$app->zii->getBox('hotline_number');
	if(!empty($b)){
		$html .= '<a href="tel:'. unMark($b['text'],'') .'" class="btn-link"><i class="fa fa-phone"> ' . uh($b['text']) .'</i></a>';
	}else{
		$html .= Yii::$app->t->translate('label_contact');
	}
}
$html .= '</div>';


$html .= '</td>
    				'.(Yii::$app->user->id>0 ? '<td class="center">'.(isset($v['partner']) ? $v['partner'] . (isset($price_partner['price']) && $price_partner['price'] > 0 ? '<p><b class="red input-partner-price-'.$v['id'].'">'.Yii::$app->zii->showPrice($price_partner['price'],$price_partner['currency']).'</b></p>' : '')  : '').'</td>' : '').'
    								

</tr>';
                }}}
                $html .= '</tbody></table></div>';
                
                $html .= '</div></div>';
                
                
                
                $html .= '<div role="tabpanel" class="tab-pane '.(getParam('tab_open') == 'tab02' ? 'active' : '').'" id="tab02"><div class="pdt15" id="departure_scheduler_from_item_price_allxa">';
                $html .= '<div class="mobile-only tab-mobile-header upper bold">Tour khởi hành hàng ngày</div>';
                $html .= '<div class="lich-khoi-hanh-search"><form method="get" action="" class="form-inline form-search-lich-kh2">
  <div class="form-group hide">
   <input type="hidden" value="tab02" name="tab_open" />
    <select  onchange="submitFormTarget(this);" data-form-submit=".form-search-lich-kh2" data-action="change_item_tour_category" name="tour_category" class="form-control input-sm select2" data-search="hidden" style="width: 100%" >';
                
                foreach (\app\modules\admin\models\Filters::getFilters([
                    'code'=>'tour_category','parent_id'=>0,
                    
                ]) as $v1){
                    
                    //$html .= '<option value="0">'.uh($v1['title']).'</option>';
                    foreach (\app\modules\admin\models\Filters::getFilters([
                        'parent_id'=>$v1['id'],
                        'filter_value'=>1
                    ]) as $v2){
                        $html .= '<option '.($tour_category == $v2['id'] ? 'selected="selected"' : '').' value="'.$v2['id'].'">'. $v2['title'].'</option> ';
                    }
                    
                }
                
                
                $html .= '</select>
  </div>
                    
        		<div class="form-group" title="Điểm khởi hành">
                    
    <select  onblur="submitFormTarget(this);" data-form-submit=".form-search-lich-kh2" placeholder="Điểm khởi hành" data-action="change_item_tour_category" name="tour_start" class="form-control input-sm select2" data-search="hidden" style="width: 100%" ><option value="0">.: Điểm khởi hành :.</option>';
                $get = $_GET;
                if(isset($get['tour_category'])){
                    unset($get['tour_category']);
                }
                $l = \app\models\Articles::getItemDailyTour([
                    'type'=>'tours',
                    'filter_code'=>['tour_type'],
                    'filter_value'=>[2],
                    //'tour_category'=>$tour_category,
                    'limit'=>1000000
                ]);
                $t_in = [0];
                if(!empty($l['listItem'])){
                    foreach ($l['listItem'] as $v1){
                        foreach (\app\modules\admin\models\Filters::getFilters([
                            'parent_id'=>-1,'code'=>'tour_start','item_id'=>$v1['id']
                        ]) as $v2){
                            if(!in_array($v2['id'], $t_in))
                                $t_in[] = $v2['id'];
                        }
                    }
                }
                //view($t_in);
                //view($l['listItem']);
                foreach (\app\modules\admin\models\Filters::getFilters([
                    'code'=>'tour_start','parent_id'=>0
                ]) as $v1){
                    
                    //$html .= '<option value="0">'.uh($v1['title']).'</option>';
                    foreach (\app\modules\admin\models\Filters::getFilters([
                        'parent_id'=>$v1['id'],'in'=>$t_in
                    ]) as $v2){
                        $html .= '<option '.(getParam('tour_start') == $v2['id'] ? 'selected="selected"' : '').' value="'.$v2['id'].'">'. $v2['title'].'</option> ';
                    }
                    
                }
                
                $from_date = getParam('from_date');
                if(ctime(['string'=>$from_date,'return_type'=>1]) < time()){
                    $from_date = date('d/m/Y');
                }
                $html .= '</select>
  </div>
                    
                    
                    
                    
  <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-find fa-search"></i> Tìm tour</button>
</form></div>';
                
                $html .= '<div class="table-responsive"><table class="table_departure_scheduler table table-hover table-bordered vmiddle">';
                $html .= '<thead><tr class="bold">
    				 
    				<th class="center">Điểm K.H</th>
    				<th class="center">Mã tour</th>
    				<th class="center">Tên tour</th>
    				<th class="center">Giá <i class="font-normal">(VNĐ)</i></th>
    				<th class="center">Tình trạng</th>
                    
    				<th class="center">Đặt tour</th>
				<th class="center">ĐVTC</th>
    				</tr></thead><tbody>';
                
                $l = \app\models\Articles::getItemDailyTour([
                    'type'=>'tours',
                    'filter_code'=>['tour_type'],
                    'filter_value'=>[2],
                    'tour_category_value'=>1,
                    
                ]+$get);
                $existed = [];
                $tour_date_time = getParam('tour_date_time') != "" ? getParam('tour_date_time') : date('d/m/Y',time()+86400);
                if(!empty($l['listItem'])){
                    $df_tour_type = \app\models\Articles::getFilterFromValue('tour_type',2);
                    foreach ($l['listItem'] as $k=>$v){
                        if(!in_array($v['id'], $existed)){
                            $existed[] = $v['id'];
                            $filter = \app\models\Articles::getItemFilter($v['id'],$df_tour_type['id']);
                            $link = isset($v['url_link']) ? $v['url_link'] : cu(['/' . $v['url']]);
                            $df_start = \app\models\Articles::getDefaultFilter($v['id'],'tour_start');
                            $df_hotel = \app\models\Articles::getDefaultFilter($v['id'],'tour_hotel');
                            $v['filter_date'] = '';
                            //$tour_date_time = $v['filter_date'];
                            $code = 'tour_type_2_'.  $df_tour_type['id'];
                            $p = \app\models\Articles::getItemPrice($v['id'], $code);
                            // view($p);
                            if(!empty($p)){
                                //if($currency == $p['currency']){
                                $v['price2'] = $p['price'];
                                //}
                                //$currency = $p['currency'];
                                
                            }
                            
                            $code = 'tour_start_'.$df_start['id'];
                            $p = \app\models\Articles::getItemPrice($v['id'], $code);
                            if(!empty($p)){
                                //if($currency == $p['currency']){
                                $v['price2'] += $p['price'];
                                //}
                                //$currency = $p['currency'];
                                
                            }
                            
                            
                            $places = Yii::$app->frontend->tour->getTourPlaces($v['id'],2);
                            
                            $starts = [];
                            
                            if(!empty($places)){
                                foreach ($places as $p1){
                                    $starts[] = $p1['title'];
                                }
                            }
                            
                            $html .= '<tr>
    				 <td class="hide"><input
						onblur="change_item_price_detail2(this)"
        				data-parent="#departure_scheduler_from_item_price_allxa"
    					data-item_id="'.$v['id'].'"
    					data-tour_type="'.$df_tour_type['id'].'"
    					data-tour_date_time="'.$tour_date_time.'"
    					data-placeholder="'.getTextTranslate(227).'"
    					data-target=".t_price_change" data-xtarget
    					data-id="'. $v['id'].'"
    					data-tour_start="'.$df_start['id'] .'"
						data-field="tour_date_time"
    					data-minDate="'.(date('Y-m-d',time())).'"
						data-month="2" type="text" name="from_date" value="" class="center datepicker2 w100" data-format="d/m/Y" placeholder="Chọn ngày K.H" title="Ngày khởi hành">
							</td>
		<td class="center">
    					  '.(!empty($starts) ? implode(' | ', $starts) : '-').'
    					     
    								</td>
    					     
        			<td class="center"><a target="_blank" href="'.$link.'" class="" title="'.uh($v['title']).'">'.$v['code'].'</a></td>
        			<td class="aleft"><a target="_blank" href="'.$link.'" class="" title="'.uh($v['title']).'">'.uh(isset($v['short_title']) && $v['short_title'] != "" ? $v['short_title'] : $v['title']).'</a></td>
    				<td class="center"><b class="red tour_pricex_rp ">'.Yii::$app->zii->showPrice($v['price2'],$v['currency']).'</b></td>
    				';
                            $html .= '<td class="center">'.(isset($filter['status']) ? $filter['status'] : '').'</td>';
    				$html .= '<td class="center"><a href="'.$link.'" data-tour_type="'.$df_tour_type['id'].'"
    						data-id="'.$v['id'].'"
    						data-tour_hotel="'.(isset($df_hotel['id']) ? $df_hotel['id'] : -1).'"
    						data-tour_date_time="'.$tour_date_time.'"
    						data-tour_start="'.$df_start['id'].'"
    						onclick="btn_book_tour(this);"
    						type="button" class="input-select-tour-s'.$v['id'].' btn-book-tour-rp btn-book-tour-'.$v['id'].' btn btn-sm btn-warning btn-link"><i class="glyphicon glyphicon-hand-right"></i> Chi tiết</a></td>
    				<td></td>
    								</tr>';
                        }}}
                        $html .= '</tbody></table></div>';
                        
                        $html .= '</div></div>';
                        
                        
                        
                        $html .= '<div role="tabpanel" class="tab-pane '.(getParam('tab_open','tab01') == 'tab03' ? 'active' : '').'" id="tab03"><div id="departure_scheduler_from_item_price_all3" class="departure_scheduler_from_item_price_all3 pdt15">';
                        
                        $html .= '<div class="mobile-only tab-mobile-header upper bold">Tour khởi hành hàng tuần</div>';
                        $html .= '<div class="lich-khoi-hanh-search"><form method="get" action="" class="form-inline form-search-lich-kh3">
  <div class="form-group">
  <input type="hidden" value="tab03" name="tab_open" />
    <select onchange="submitFormTarget(this);" data-form-submit=".form-search-lich-kh3" data-action="change_item_tour_category" name="tour_category" class="form-control input-sm select2" data-search="hidden" style="width: 100%" >';
                        
                        foreach (\app\modules\admin\models\Filters::getFilters([
                            'code'=>'tour_category','parent_id'=>0
                        ]) as $v1){
                            
                            //$html .= '<option value="0">'.uh($v1['title']).'</option>';
                            foreach (\app\modules\admin\models\Filters::getFilters([
                                'parent_id'=>$v1['id']
                            ]) as $v2){
                                $html .= '<option '.($tour_category == $v2['id'] ? 'selected="selected"' : '').' value="'.$v2['id'].'">'. $v2['title'].'</option> ';
                            }
                            
                        }
                        
                        
                        $html .= '</select>
  </div>
                            
        		<div class="form-group" title="Điểm khởi hành">
                            
    <select onchange="submitFormTarget(this);" data-form-submit=".form-search-lich-kh3" placeholder="Điểm khởi hành" data-action="change_item_tour_category" name="tour_start" class="form-control input-sm select2" data-search="hidden" style="width: 100%" ><option value="0">.: Điểm khởi hành :.</option>';
                        $l = \app\models\Articles::getItemFromFilterDate([
                            'type'=>'tours',
                            'filter_code'=>['tour_type'],
                            'filter_value'=>[5],
                            'tour_category'=>$tour_category,
                            'limit'=>1000000
                        ]);
                        $t_in = [0];
                        if(!empty($l['listItem'])){
                            foreach ($l['listItem'] as $v1){
                                foreach (\app\modules\admin\models\Filters::getFilters([
                                    'parent_id'=>-1,'code'=>'tour_start','item_id'=>$v1['id']
                                ]) as $v2){
                                    if(!in_array($v2['id'], $t_in))
                                        $t_in[] = $v2['id'];
                                }
                            }
                        }
                        //view($t_in);
                        
                        foreach (\app\modules\admin\models\Filters::getFilters([
                            'code'=>'tour_start','parent_id'=>0,
                            
                        ]) as $v1){
                            
                            //$html .= '<option value="0">'.uh($v1['title']).'</option>';
                            foreach (\app\modules\admin\models\Filters::getFilters([
                                'parent_id'=>$v1['id'],
                                'check_item_existed'=>true,
                                'in'=>$t_in
                            ]) as $v2){
                                $html .= '<option '.(getParam('tour_start') == $v2['id'] ? 'selected="selected"' : '').' value="'.$v2['id'].'">'. $v2['title'].'</option> ';
                            }
                            
                        }
                        
                        $from_date = getParam('from_date');
                        if(ctime(['string'=>$from_date,'return_type'=>1]) < time()){
                            $from_date = date('d/m/Y');
                        }
                        $html .= '</select>
  </div>
                            
                            
   <div class="form-group">
                            
    <select onchange="submitFormTarget(this);" data-form-submit=".form-search-lich-kh3" data-action="change_item_tour_category" name="tour_date_time" class="form-control input-sm select2" data-search="hidden" style="width: 100%" >
						<option value="-1">.: Ngày khởi hành :.</option>';
                        for($i = 0; $i<7; $i++){
                            $html .= '<option '.(getParam('tour_date_time',-1) == $i ? 'selected="selected"' : '').' value="'.$i.'">'.read_date($i).'</option> ';
                        }
                        
                        
                        
                        $html .= '</select>
  </div>
                            
  <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-find fa-search"></i> Tìm tour</button>
</form></div>';
                        
                        $html .= '<div class="table-responsive"><table class="table_departure_scheduler table table-hover table-bordered vmiddle">';
                        $html .= '<thead><tr class="bold">
    				<th class="center hide">Ngày K.H</th>
    				<th class="center">Điểm K.H</th>
    				<th class="center">Mã tour</th>
    				<th class="center">Tên tour</th>
    				<th class="center">Giá <i class="font-normal">(VNĐ)</i></th>
    				<th class="center">Tình trạng</th>
                            
    				<th class="center">Đặt tour</th>
				<th class="center">ĐVTC</th>
    				</tr></thead><tbody>';
                        $l = \app\models\Articles::getItemWeeklyTour([
                            'type'=>'tours',
                            'filter_code'=>['tour_type'],
                            'filter_value'=>[5],
                            'tour_category'=>$tour_category,
                            
                        ]+$_GET);
                        $existed = [];
                        if(!empty($l['listItem'])){
                            foreach ($l['listItem'] as $k=>$v){
                                if(!in_array($v['id'], $existed)){
                                    $existed[] = $v['id'];
                                    
                                    $places = Yii::$app->frontend->tour->getTourPlaces($v['id'],2);
                                    
                                    $starts = [];
                                    
                                    if(!empty($places)){
                                        foreach ($places as $p1){
                                            $starts[] = $p1['title'];
                                        }
                                    }
                                    
                                    
                                    $df_tour_type = \app\models\Articles::getDefaultFilter($v['id'],'tour_type');
                                    $link = isset($v['url_link']) ? $v['url_link'] : cu(['/' . $v['url']]);
                                    $df_start = \app\models\Articles::getDefaultFilter($v['id'],'tour_start');
                                    $df_hotel = \app\models\Articles::getDefaultFilter($v['id'],'tour_hotel');
                                    $tour_date_time = $v['filter_date'];
                                    $code = 'tour_type_5_'.  $df_tour_type['id'];
                                    $p = \app\models\Articles::getItemPrice($v['id'], $code);
                                    // view($p);
                                    
                                    $price_code_partner = 'partner_price_' .$code;
                                    $price_partner = \common\models\Articles::getItemPrice($price_code_partner,$v['id']);
                                    
                                    
                                    if(!empty($p)){
                                        //if($currency == $p['currency']){
                                        $v['price2'] = $p['price'];
                                        //}
                                        //$currency = $p['currency'];
                                        
                                    }
                                    $filter = \app\models\Articles::getItemFilter($v['id'],$df_tour_type['id']);
                                    $code = 'tour_start_'.$df_start['id'];
                                    $p = \app\models\Articles::getItemPrice($v['id'], $code);
                                    if(!empty($p)){
                                        //if($currency == $p['currency']){
                                        $v['price2'] += $p['price'];
                                        //}
                                        //$currency = $p['currency'];
                                        
                                    }
                                    $date_list = \app\models\Filters::getFilterFromItem($v['id'],'tour_day_of_week');
                                    $rdate = [];
                                    if(!empty($date_list)){
                                        foreach ($date_list as $date){
                                            $rdate[] = $date['value'];
                                            
                                        }
                                    }
                                    //view($date_list);
                                    $html .= '<tr>
    				<td class="center hide">
                                        
    				<input
						onblur="change_item_price_detail2(this)"
        				data-parent="#departure_scheduler_from_item_price_all3"
    					data-item_id="'.$v['id'].'"
    					data-tour_type="'.$df_tour_type['id'].'"
    					data-tour_date_time="'.$tour_date_time.'"
    					data-placeholder="'.getTextTranslate(227).'"
    					data-target=".t_price_change" data-xtarget
    					data-id="'. $v['id'].'"
    					data-tour_start="'.$df_start['id'] .'"
						data-field="tour_date_time"
    					data-day_of_week="'.implode(',', $rdate).'"
    					data-minDate="'.(date('Y-m-d',time())).'"
						data-month="2" type="text" name="from_date" value="" class="center datepicker2 w100" data-format="d/m/Y" placeholder="Chọn ngày K.H" title="Ngày khởi hành">
							</td>
		<td class="center">
    					 '.(!empty($starts) ? implode(' | ', $starts) : '-').'
    								</td>
    					     
    					     
    					     
        			<td class="center"><a target="_blank" href="'.$link.'" class="" title="'.uh($v['title']).'">'.$v['code'].'</a></td>
        			<td class="aleft"><a target="_blank" href="'.$link.'" class="" title="'.uh($v['title']).'">'.uh(isset($v['short_title'])  && $v['short_title'] != "" ? $v['short_title'] : $v['title']).'</a></td>
    				<td class="center"><b class="red tour_pricex_rp input-tour-detail-select-tour-price-'.$v['id'].'">'.Yii::$app->zii->showPrice($v['price2'],$v['currency']).'</b></td>
    				';
					$html .= '<td class="center">'.(isset($filter['status']) ? $filter['status'] : '').'</td>';
    				    
    				$html .= '<td class="center"> 

<a href="'.$link.'"  data-tour_type="'.$df_tour_type['id'].'"
    						data-id="'.$v['id'].'"
    						data-tour_hotel="'.(isset($df_hotel['id']) ? $df_hotel['id'] : -1).'"
    						data-tour_date_time="'.$tour_date_time.'"
    						data-tour_start="'.$df_start['id'].'"
    						onclick="btn_book_tour(this);"
    						
    						type="button" class="input-select-tour-s'.$v['id'].' btn-book-tour-rp btn-book-tour-'.$v['id'].' btn btn-sm btn-link btn-warning btn-disabled-remove-attr-'.$v['id'].'"><i class="glyphicon glyphicon-hand-right"></i> Chi tiết</a></td>
    				'.(Yii::$app->user->id>0 ? '<td class="center">'.(isset($v['partner']) ? $v['partner'] . (isset($price_partner['price']) && $price_partner['price'] > 0 ? '<p><b class="red input-partner-price-'.$v['id'].'">'.Yii::$app->zii->showPrice($price_partner['price'],$price_partner['currency']).'</b></p>' : '')  : '').'</td>' : '').'
    								</tr>';
                                }}}
                                $html .= '</tbody></table></div>';
                                
                                $html .= '</div></div>';
                                
                                
                                
                                $html .= '</div></div>';
                                return $html;
    }
    
    
    public function showItemInfo($o = []){
        $updated_at = isset($o['updated_at']) ? $o['updated_at'] : false;
        $time = isset($o['time']) ? $o['time'] : false;
        $viewed = isset($o['viewed']) ? $o['viewed'] : 0;
        $comment = isset($o['comment']) ? $o['comment'] : 0;
        $post_by = isset($o['post_by']) ? $o['post_by'] : false;
        $short_info = isset($o['short_info']) && $o['short_info'] == false ? false : true;
        $url = isset($o['url']) ? getAbsoluteUrl($o['url']) : getAbsoluteUrl($this->getUrl( __DETAIL_URL__));
        
        $html = '<div class="entry-meta sitem-infomation">';
        $html .= $updated_at !== false ? '<span class="entry-date fa fa-history f14px">
		<time itemprop="dateModified" content="'.date('c',strtotime($updated_at)).'">'.count_time_post($updated_at).'</time></span>' : '';
        $html .= $time !== false ? '<span class="entry-date fa fa-history f14px hide">
		<time itemprop="datePublished" content="'.date('c',strtotime($time)).'">'.date('c',strtotime($time)).'</time></span>' : '';
        
        $html .= $post_by !== false ? '<span itemprop="author" itemscope itemtype="http://schema.org/Person">
<span itemprop="name" class="entry-view fa fa-user f14px"> '.uh($post_by).'</span>


</span>' : '';
        $html .= $viewed > 0 ? '<span class="entry-view fa fa-eye f14px"> '.number_format($viewed).' '.($short_info ? 'lượt xem' : '').'</span>' : '';
        $html .= isset(Yii::$app->view->info['short_name']) ? '<span class="hide" itemprop="publisher" 
itemscope itemtype="http://schema.org/Organization">
<span itemprop="name">'.Yii::$app->view->info['short_name'] .'</span>
<span class="hide" itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
       
      <meta itemprop="url" content="'.getAbsoluteUrl(Yii::$app->config['logo']['logo']['image']).'">
      
    </span>
<meta itemprop="url" content="'.ABSOLUTE_DOMAIN.'">
</span>' : '';
        $html .= $comment>0 ? '<span class="entry-comment fa fa-comments-o f14px"><a href="#">'.number_format($comment).' '.($short_info ? 'bình luận' : '').'</a></span>' : '';
        $html .= '</div>';
        return $html;
    }
    
    public function countItemComment($item_id){
        return (new Query())->from('comments')->where(['item_id'=>$item_id])->count(1);
    }
    
    public function showItemShareSocial($o = []){
        $fb_like = isset($o['fb_like']) && $o['fb_like'] == true ? true : false;
        $g_plusone = isset($o['g_plusone']) && $o['g_plusone'] == true ? true : false;
        $twitter = isset($o['twitter']) && $o['twitter'] == true ? true : false;
        $pinterest = isset($o['pinterest']) && $o['pinterest'] == true ? true : false;
        
        $v = isset($o['v']) ? $o['v'] : [];
        
        $html = '<div class="box-tin-chi-tiet-share sitem-share-social">';
        $html .= $fb_like ? '<div class="item_social fb-like inline-block mgr3" data-href="" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true" style="vertical-align: top"></div>' : '';
        $html .= $g_plusone ? '<div class="item_social g-plusone inline-block" data-size="medium" data-href="" style="vertical-align: top"></div>' : '';
        
        if($twitter){
            $html .= '<a href="https://twitter.com/share" class="item_social inline-block twitter-share-button" style="vertical-align: top">Tweet</a>';
            Yii::$app->view->registerJs('!function (d, s, id) { var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? \'http\' : \'https\'; if (!d.getElementById(id)) { js = d.createElement(s); js.id = id; js.src = p + \'://platform.twitter.com/widgets.js\'; fjs.parentNode.insertBefore(js, fjs); } }(document, \'script\', \'twitter-wjs\');');
        }
        if($pinterest){
            $html .= '<span class="mgl10 inline-block" style="vertical-align: top">
					<a class="item_social inline-block" data-pin-color="white"
					data-pin-do="buttonPin"
					href="https://www.pinterest.com/pin/create/button/?url='.URL_NO_PARAM.'&media='.$v['icon'].'&description='.uh($v['title']).'" data-pin-config="beside" style="vertical-align: top"></a></span>';
            Yii::$app->view->registerJsFile('//assets.pinterest.com/js/pinit.js', ['depends' => [
                \yii\web\JqueryAsset::className(),
                
            ],'async'=>'async', 'defer'=>'defer']);
        }
        $html .='</div> ';
        return $html;
    }
    
    
    public function updateCountry(){
        $gi = new \GeoIP();
        
        $code2 = 'VA';
        /*
         *
         $name = $gi->GEOIP_COUNTRY_NAMES[$gi->GEOIP_COUNTRY_CODE_TO_NUMBER[$code2]];
         $code3 = $gi->GEOIP_COUNTRY_CODES3[$gi->GEOIP_COUNTRY_CODE_TO_NUMBER[$code2]];
         view($code2 . ' / ' . $code3 . ' / ' .$name);
         Yii::$app->db->createCommand()->update('local', ['name2'=>$name,'code'=>$code2],['code3'=>$code3])->execute();
         return null;
         */
        include_once __LIBS_PATH__ .'/simplehtmldom_1_5/simple_html_dom.php';
        //$html = file_get_html('https://countrycode.org');
        $html = str_get_html(\app\modules\admin\models\Local::htmlCountry());
        foreach($html->find('tr') as $ul) {
            foreach($ul->find('td') as $k => $li){
                switch ($k){
                    case 1:
                        $phone_code = trim($li->plaintext);
                        break;
                    case 2:
                        $code2 = explode('/', $li->plaintext);
                        $code3 = trim($code2[1]);
                        $code2 = trim($code2[0]);
                        break;
                }
                
            }
            //view($phone_code . ' / ' . $code2 . ' / ' . $code3);
            Yii::$app->db->createCommand()->update('local', ['phone_code'=>$phone_code],
                ['code3'=>$code3])->execute();
        }
    }
    
    
    public function getSenderEmail(){
        $emails = \app\models\SiteConfigs::getConfigs('EMAILS');
        $smtp = [];
        if(isset($emails['listItem']) && !empty($emails['listItem'])){
            foreach ($emails['listItem'] as $v){
                if( isset($v['is_default']) && $v['is_default'] == 1 && isset($v['is_active']) && $v['is_active'] == 1){
                    $smtp = $v;
                    break;
                }
            }
            if(empty($smtp)){
                $smtp = $emails['listItem'][0] ;
            }
        }
        
        if(empty($smtp)){
            $smtp = \app\models\SiteConfigs::getConfigs('CONTACTS');
            $smtp['from_name'] = $smtp['short_name'];
        }
        return $smtp;
    }
    
    
    public function showContactInfo(){
        $v = \app\models\SiteConfigs::getConfigs('CONTACTS');
        $html = '';
        if(!empty($v)){
            $html .= '<p class="f16px contact_name"><b>'.uh($v['name']).' '.($v['short_name'] != '' ? '<i class="contact_short_name">(' .uh($v['short_name']) .')</i>' : '').'</b></p>';
            $html .= '<p class="contact_address">Địa chỉ: '.uh($v['address']).'</p>';
            $html .= '<p class="contact_phone">Điện thoại: '.uh($v['phone']).'</p>';
            $html .= '<p class="contact_hotline">Hotline: '.uh($v['hotline']).'</p>';
            $html .= '<p class="contact_email">Email: <a href="mailto:'.($v['email']).'">'.($v['email']).'</a></p>';
        }
        return $html;
    }
    
    public function getAbsoluteUrl(){
    	
    }
    
    public function getBreadCrumbListJs(){
    	 
    }
    
    public function getBreadCrumbList($o = []){
    	$l = \app\models\SiteMenu::get_tree_menu();
    	$li_append = isset($o['li_append']) ? $o['li_append'] : [];
    	if(!empty($l)){
    		$l = array_merge($l,$li_append);
    	}else{
    		$l = $li_append;
    	}
    	 
    	    	
    	$html = '<div class="breadcrumblist"><ol class="breadcrumbs" >';
    	if(!empty($l)){
    		$i = 1;
    		if(isset($o['show_home_page']) && $o['show_home_page'] === true){
    			$html .= '<li>
<a href="/"><span>'.getTextTranslate(37).'</span></a>
    
  </li>';
    			$html .= '<i class="ol-spc">›</i>';
    			
    		}
    		
    		foreach ($l as $k=>$v){
    			$html .= '<li >
    <a href="'.(isset($v['url_link']) ? getAbsoluteUrl($v['url_link']) : getAbsoluteUrl($this->getUrl($v['url']))).'">
    <span >'.(uh($v['title'])).'</span>
    '.(isset($v['icon']) && $v['icon'] != "" ? '' : '').'</a>
    
  </li>';
    			if($k<count($l)-1){
    				$html .= '<i class="ol-spc">›</i>';
    			}
    		}
    	}
    	$html .= '</ol></div>';
    	return $html;
    }
    
    
    public function getGoogleJsonLD(){
    	 
    	 
    }
    
    public function getSchemeJsonLD(){
    	
    	$html = ''; $jsonLD = [];
    	$logoImage = isset(Yii::$app->config['logo']['logo']['image']) ? getAbsoluteUrl(Yii::$app->config['logo']['logo']['image'])  : '';
    	$social = isset(Yii::$app->config['other_setting']['social']) ? (Yii::$app->config['other_setting']['social']) : [];    	 
    	$sameAs = [];
    	if(is_array($social) && !empty($social)){
    		
    		foreach ($social as $k=>$v){
    			if($v != ""){
    				$sameAs[] = $v;
    			}
    		}
    	}
    	// Common json
    	// 1. Webpage
    	
    	$brd = \app\models\SiteMenu::get_tree_menu();
    	
    	$webpage = [
    			"@context" => "http://schema.org",
    			"@type" => "WebPage",
    			//"name" => "A name. I use same as title tag",
    			//"url" => "http://example.com",
    			//"description" => "Description. I just use the same description as meta data",
    			"name" => (get_site_value('seo/title',1,true)),
    			"url" => ABSOLUTE_DOMAIN,
    			"description" => get_site_value('seo/description',1,true),
    	];
    	$breadcrumb = [
    			"@type" => "BreadcrumbList",
    			'itemListElement' => []
    	];
    	if(!empty($brd)){
    		foreach ($brd as $k=>$v){
    			$breadcrumb['itemListElement'][] = [
    					"@type" => "ListItem",
    					"position" => $k+1,
    					"item" => [
    						"@type" => "WebSite",
    							//"@id" => getAbsoluteUrl($v['url_link']),
    							"name" => uh($v['title'])
    							 
    			]
    			];
    		}
    		$webpage['breadcrumb'] = $breadcrumb;
    	}
    	$publisher = [
    			"@type" => "Organization",
    			"name" => Yii::$app->view->info['short_name'],
    			'url' => ABSOLUTE_DOMAIN,
    			"logo" => [
    					"@type" => "imageObject", 
    					"url" => $logoImage
    			]
    	];
    	if(__IS_DETAIL__ && !empty(Yii::$app->view->item)){
    		
    		$img = getImageInfo(getAbsoluteUrl(Yii::$app->view->item['icon']));
    		
    		$detailImg = isset($img[1]) && $img[1]> 0 ? ([
    				"@type" => "imageObject",
    				"url" => getAbsoluteUrl(Yii::$app->view->item['icon']),
    				"height" => $img[1],
    				"width" => $img[0]
    		]) : getAbsoluteUrl(Yii::$app->view->item['icon']);
    		
    		$mainEntity = [
    				"@type" => "Article",
    				"@id" => getAbsoluteUrl(Yii::$app->view->item['url_link']),
    				"author" => (Yii::$app->view->item['post_by_name']),
    				"datePublished" =>date('c',strtotime(Yii::$app->view->item['time'])),
    				"dateModified" => date('c',strtotime(Yii::$app->view->item['updated_at'])),
    				"mainEntityOfPage" => getAbsoluteUrl(__CATEGORY_URL__),
    				"headline" => uh(Yii::$app->view->item['title']),
    				"alternativeHeadline" => uh(isset(Yii::$app->view->item['info']) ? strip_tags(Yii::$app->view->item['info']) : ''),
    				"name" => uh(Yii::$app->view->item['title']),
    				"image" => $detailImg,
    				"publisher" => $publisher
    		];
    		$webpage['mainEntity'] = $mainEntity;
    	}
    	// End webpage
    	//$html .= '<script type="application/ld+json">' .(json_encode($webpage)) .'</script>';
    	$jsonLD[] = $webpage;
    	// WEBSITE
    	$website = [
    			"@context" => "http://schema.org",
    			"@type" => "WebSite",
    			"name" => Yii::$app->view->info['short_name'],
    			"url" => ABSOLUTE_DOMAIN,
    			"sameAs" => $sameAs,
    			"potentialAction" => [
    			"@type" => "SearchAction",
    			"target" => ABSOLUTE_DOMAIN . '/search?q={search_term_string}',
    			"query-input" => "required name=search_term_string"
    	]
    	] ;  
    	// END WEBSITE
    	//$html .= '<script type="application/ld+json">' .(json_encode($website)) .'</script>';
    	$jsonLD[] = $website;
    	
    	$address = [
    			"@type" => "PostalAddress",
    			"addressCountry" => isset(Yii::$app->view->info['addressCountry']) ? Yii::$app->view->info['addressCountry'] : "Việt Nam",
    			"addressLocality"=>isset(Yii::$app->view->info['addressLocality']) ? Yii::$app->view->info['addressLocality'] : "Hà Nội",
    			"addressRegion" => isset(Yii::$app->view->info['addressRegion']) ? Yii::$app->view->info['addressRegion'] : "Thanh Xuân",
    			"postalCode" => isset(Yii::$app->view->info['postalCode']) ? Yii::$app->view->info['postalCode'] : 100000,
    			"streetAddress" => isset(Yii::$app->view->info['streetAddress']) ?
    			Yii::$app->view->info['streetAddress'] :
    			(isset(Yii::$app->view->info['address']) ? Yii::$app->view->info['address'] : '')
    	];
    	
    	$Organization = [
    			"@context" => "http://schema.org",
    			"@type" => "Organization",
    			"@id" => ABSOLUTE_DOMAIN,
    			"url" => ABSOLUTE_DOMAIN,
    			"name" => Yii::$app->view->info['short_name'],
    			"description" => isset(Yii::$app->view->info['description']) ? Yii::$app->view->info['description'] : '',
    			"sameAs" => $sameAs,
    			"logo" => ($logoImage),
    			"address" => $address 
    	];
    	if(isset(Yii::$app->view->info['hotline']) && Yii::$app->view->info['hotline'] != ''){
    		$Organization['contactPoint'] = [
    				"@type"=> "ContactPoint",
    				"telephone"=> parsePhoneNumber(Yii::$app->view->info['hotline']),
    				"contactType"=> "customer service"
    		];
    	}
    	// END $Organization
    	$html .= '<script type="application/ld+json">' .(json_encode($Organization)) .'</script>';
    	$jsonLD[] = $Organization;
    	
    	$breadcrumb["@context"] = "http://schema.org";
    	
    	//$html .= '<script type="application/ld+json">' .(json_encode($breadcrumb)) .'</script>';
    	$jsonLD[] = $breadcrumb; 
    	$geo = [];
    	if(isset(Yii::$app->view->info['latitude']) && Yii::$app->view->info['latitude'] != "" && Yii::$app->view->info['longitude'] != ""){
    		$geo = [
    				"@type" => "GeoCoordinates",
    				"latitude" => Yii::$app->view->info['latitude'],
    				"longitude" => Yii::$app->view->info['longitude']
    		];
    	}
    	// Json by page
    	if(__IS_DETAIL__ && !empty(Yii::$app->view->item)){
    		$text = '';
    		if(isset(Yii::$app->view->item['ctab'])){
    		foreach(Yii::$app->view->item['ctab'] as $d=>$t){
    			$text .= '<div class="box-details">'.uh($t['text'],2).'</div>';
    		}}
    		    		    		
    		$article = [
    				"@context" => "http://schema.org",
    				"@type" => "Article",
    				"headline" => uh(Yii::$app->view->item['title']),
    				"alternativeHeadline" => uh(isset(Yii::$app->view->item['info']) ? strip_tags(Yii::$app->view->item['info']) : ''),
    				"name" => uh(Yii::$app->view->item['title']),
    				"author" => [
    				"@type" => "Person",
    				"name" => (Yii::$app->view->item['post_by_name'])
    		],
    		"datePublished" => date('c',strtotime(Yii::$app->view->item['time'])),
    		"dateModified" => date('c',strtotime(Yii::$app->view->item['updated_at'])),
    		"image" => getAbsoluteUrl(Yii::$app->view->item['icon']),
    		"articleSection" => __CATEGORY_NAME__,
    				"description" => uh(isset(Yii::$app->view->item['info']) ? strip_tags(Yii::$app->view->item['info']) : ''),
    		"articleBody" => \yii\helpers\Html::encode(strip_tags($text)),
    		"url" => getAbsoluteUrl(Yii::$app->view->item['url_link']),
    		"publisher" => $publisher,
    		"mainEntityOfPage" => [
    		"@type" => "WebPage",
    		"@id" => getAbsoluteUrl(__CATEGORY_URL__)
    		],
    		//"aggregateRating" => $aggregateRating
    		];
    		
    		$vote = \app\models\Ratings::getRating(Yii::$app->view->item['id']);
    		if($vote['avg']>0){
    			$aggregateRating = [
    				"@type" => "AggregateRating",
    				"ratingValue" => '"'. $vote['avg'] .'/' . $vote['max'] . '"',
    				"ratingCount" => $vote['total'],
    					"bestRating"=>$vote['max'],
    					'worstRating'=>$vote['min']
    			];    			
    			$article['aggregateRating'] = $aggregateRating;
    		}
    		
    		if($text == ""){
    			unset($article['articleBody']);
    		}
    		//$html .= '<script type="application/ld+json">' .(json_encode($article)) .'</script>';
    		$jsonLD[] = $article;
    	switch (Yii::$app->controller->action->id){
    		case 'index':
    			
    			break;
    		case 'news':
    			$newsarticle = [
    			"@context" => "http://schema.org",
    			"@type" => "NewsArticle",
    			"headline" => uh(Yii::$app->view->item['title']),
    			"alternativeHeadline" => uh(strip_tags(Yii::$app->view->item['info'])),
    			"dateline" => Yii::$app->view->info['short_name'],
    			"image" => [
    				getAbsoluteUrl(Yii::$app->view->item['icon'])
    			],
    			"datePublished" => date('c',strtotime(Yii::$app->view->item['time'])),
    			"dateModified" => date('c',strtotime(Yii::$app->view->item['updated_at'])),
    			"description" => uh(strip_tags(Yii::$app->view->item['info'])),
    			"articleBody" => \yii\helpers\Html::encode(strip_tags($text)),
    			"url" => getAbsoluteUrl(Yii::$app->view->item['url_link']),
    			"author" => [
    			"@type" => "Person",
    			"name" => (Yii::$app->view->item['post_by_name'])
    			],
    			"publisher" => $publisher,
    			"mainEntityOfPage" => [
    			"@type" => "WebPage",
    			"@id" => getAbsoluteUrl(__CATEGORY_URL__) 
    			]
    			];
    			//$html .= '<script type="application/ld+json">' .(json_encode($newsarticle)) .'</script>';
    			$jsonLD[] = $newsarticle;
    			break;
    	}
    	
    	}
    	
    	
    	
    	// LocalBusiness
    	$LocalBusiness = [
    		"@context" => "http://schema.org",
    		"@type" => "LocalBusiness",
    		"address" => $address,
    		"description" => isset(Yii::$app->view->info['description']) ? Yii::$app->view->info['description'] : '',    		
    		"name" => isset(Yii::$app->view->info['short_name']) && Yii::$app->view->info['short_name'] != "" ? 
    			Yii::$app->view->info['short_name'] : (isset(Yii::$app->view->info['name']) ? Yii::$app->view->info['name'] : ''),
    			"telephone"=> isset(Yii::$app->view->info['hotline']) ? parsePhoneNumber(Yii::$app->view->info['hotline']) : '',
    		"url" => ABSOLUTE_DOMAIN,
    		"image" => $logoImage,
    		"logo" => $logoImage,	
    		//	"priceRange"=>isset(Yii::$app->view->item['seo']['priceRange']) ? Yii::$app->view->item['seo']['priceRange'] : '0',
    		"sameAs"=>$sameAs,
    		"openingHours" => "Mo-Su",
    		"aggregateRating" =>	[
    					"@type" => "AggregateRating",
    					"ratingValue" => '5/5' ,
    					"ratingCount" => date('z') + (date('Y') - 2017),
    					 
    			]	
    	];
    	if(isset(Yii::$app->view->item['seo']['priceRange']) && Yii::$app->view->item['seo']['priceRange'] != ""){
    		$LocalBusiness['priceRange'] = Yii::$app->view->item['seo']['priceRange'];
    	}
    	if(!empty($geo)){
    		$LocalBusiness['geo'] = $geo;
    	}
    	
    	$jsonLD [] = $LocalBusiness;
    	
    	return '<script type="application/ld+json">' .(json_encode($jsonLD)) .'</script>';    	
    }
    
    
    public function getArticleCategoryName($id){
		    	
    }
    
    public function showOtherItems($o = []){
    	
    	$id = isset($o['id']) ? $o['id'] : 0;
    	$view_more = isset($o['view_more']) ? $o['view_more'] : '';
    	$l = Yii::$app->zii->getArticles([
    			'category'=>__CATEGORY_ID__,
    			'type'=>'news',
    			'key'=>'limit-other-news',
    			'count'=>true,
    			'other'=>$id,
    		//	'p'=>getParam('p',1),
    		//	'tag'=> Yii::$app->controller->action->id == 'tag' ? getParam('url') : false,
    	]);
    	$label = isset($o['label']) ? $o['label'] : '';
    	$html = '';
    	if(!empty($l['listItem'])){
    		$html .= '<div class="other-items-news">';
if(isset($o['label']) && $o['label'] != ''){
	$html .= '<label class="f16px other-item-heading">'.uh($label).'</label>';
}
$html .= '<ul class="other-item-list f14px">';
    		foreach ($l['listItem'] as $k=>$v){
    			$html .= '<li><a href="'.$v['url_link'].'">'.uh($v['title']).'</a></li>';
    		}
    		if(isset($o['external_link']) && $o['external_link']){
    			foreach ($this->getExternalLink(__DETAIL_URL__) as $k=>$v){
    				$html .= '<li class="ex-link"><a href="'.$v['url_link'].'">'.uh($v['title']).'</a></li>';
    			}
    		}
    		$html .= '</ul>'.$view_more.'

</div>';
    	}
    	return $html;
    }
    
    public function getExternalLink($url, $design_mode = 0){
    	return (new Query())->from('external_links')->where([
    			'sid'=>__SID__,
    			'url'=>$url
    	]+($design_mode == 0 ? ['is_active'=>1] : []))->all();
    	//return [];
    }
    
    public function getItemDetail($url){
    	$slug = \common\models\Slugs::getItem($url);
    	$item = [];
    	if(!empty($slug)){
    		switch ($slug['item_type']){
    			case 1: // Bài viết
    				$item = \app\modules\admin\models\Content::getItem($slug['item_id']);
    				
    				break;
    		}
    	}
    	return $item;
    }
    
    public function renderModal($o = []){
    	$modal = '';
    	$ajax_action = isset($o['ajax_action']) ? $o['ajax_action'] : 'ajax';
    	$action = isset($o['action']) ? $o['action'] : '';
    	$class = isset($o['class']) ? $o['class'] : '';
    	$title = isset($o['title']) ? $o['title'] : '';
    	$name = isset($o['name']) ? $o['name'] : 'mymodal';
    	$body= '<div class="modal-body inline-block w100">' . (isset($o['body']) ? $o['body'] : '') .'</div>' ;
    	
    	$footer = isset($o['footer']) ? $o['footer'] : '';
    	
    	if(isset($_POST) && !empty($_POST)){
    		if($action == ""){
    			$action = 'quick-submit-' . post('action');
    		}
    		$_POST['action'] = $action;
    		foreach ($_POST as $key=>$value){
    			if(!is_array($value) ){
    				$footer .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
    			}
    			
    		}
    		
    		$footer .= '<textarea name="request_post" class="hide">'.json_encode($_POST).'</textarea>';
    		$footer .= '<input type="hidden" name="modal" value=".'.($r = randString(12)).'"/>';
    	}
    	
    	$footer .= '</div></div></form></div>';
    	$header = isset($o['header']) ? $o['header'] : '
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title f14px upper bold">'.$title.'</h4>
</div>

';
    	$form_class = isset($o['form_class']) ? $o['form_class'] : 
    	(isset($o['formClass']) ? $o['formClass'] : 'form-horizontal');
    	
    	
    	$header = '<div class="modal fade '.$name . " $r " .'" id="'.$name.'" tabindex="-1" role="dialog" aria-labelledby="'.$name.'Label">
<form data-action="'.$ajax_action.'" name="ajaxForm" action="/'.$ajax_action.'" class="ajaxForm '.$form_class.' f12px" method="post" onsubmit="return ajaxSubmitForm(this);">
<input type="hidden" name="_csrf-frontend" value="'.Yii::$app->request->csrfToken.'" />
<div class="modal-dialog '.$class.'" role="document"><div class="modal-content">

' . $header;
    	
    	return $header .'<div class="clear"></div>' . $body  .'<div class="clear"></div>' . $footer;
    	/*
    	 * $html = '<form data-action="'+$ajax_action+'" name="sajaxForm" action="/'+$ajax_action+'" class="ajaxForm form-horizontal f12e" method="post" onsubmit="return ajaxSubmitForm(this);">';
	    	  $html += '<div class="modal-dialog '+$this.attr('data-class')+'" role="document">';
	    	  $html += '<div class="modal-content">';
	    	  $html += '<div class="modal-header">';
	    	  $html += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
	    	  $html += '<h4 class="modal-title f12e upper bold" style="font-size:1.5em">'+$data.title+'</h4>';
	    	  $html += '</div>';
	    	  $html += '<div class="modal-body ajax-modal-body">';
	    	  $html += '<p class="ajax-loading-data">Đang tải dữ liệu.</p>';
	    	  $html += '</div></div></div></form>';
    	 */
    }
}

 




















