<?php
namespace izi\currencies;
use Yii;
/**
 * Cookie represents information related with a cookie, such as [[name]], [[value]], [[domain]], etc.
 *
 * For more details and usage information on Cookie, see the [guide article on handling cookies](guide:runtime-sessions-cookies).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Currencies extends \yii\base\Component
{
	public $default ;
	
	private $_id = 1, $_code = 'VND';
	
	
	public function init(){
	    $this->default = $this->getDefault();	   
	}
	 
	
	private $_model;
	
	public function getModel(){
	    if($this->_model === null){
	        $this->_model = Yii::createObject(['class'=>Model::class]);
	    }
	    return $this->_model;
	}
	
	public function getItem($id){
	    return $this->getModel()->getItem($id);
	}
	
	
	/**
	 * Set default currencies
	 * @param unknown $id
	 * @param string $cache
	 * @return number|boolean|unknown
	 */
	public function setDefaultCurrency($id, $cache=true){
	    $currency = \izi\models\Currencies::getCurrencies();
		if($cache && isset($currency['list']) && !empty($currency['list'])){
		    foreach ($currency['list'] as $ki=>$v){
			    if($v['id'] == $id){
			        $currency['list'][$ki]['is_default'] = $v['is_default'] = 1;
					$currency['default'] = $v;
					$this->default = $v;
				}else{
					$currency['list'][$ki]['is_default'] = 0;
				}
			}
		}else{
		    $v = \izi\models\Currencies::getItem($id,['cache'=>false]);
			$v['is_default'] = 1;
			$currency['list'][0] = $v;
			$currency['default'] = $v;
			$this->default = $v;
		}
		
		if(__SID__ > 0){
    		Yii::$app->db->updateBizData(['currency' => $currency],[
    				'code'=>\izi\models\Currencies::$key, 'sid' => __SID__
    		]);
		}
		return $currency;
	}
	
	
	public function getDefault($cache=true){
	    $currency = \izi\models\Currencies::getCurrencies();
	    if($cache && isset($currency['list']) && !empty($currency['list'])){
	        foreach ($currency['list'] as $ki=>$v){
	            if(isset($v['is_default']) && $v['is_default'] == 1){
	                return $v;
	            }
	        }
	    }
	}
	
	public function getUserCurrency(){
	    return \izi\models\Currencies::getUserCurrency();
	}
	
	public function convertCurrency($params){
	    return $this->getModel()->convertCurrency($params);
	}
	
	public function getExchangeRate($o = []){
	    return $this->getModel()->getExchangeRate($o);
	}
	
	
	public function getCurrency($id){
	    $l = $this->getUserCurrency();
	    foreach ($l as $v){
	        if(is_numeric($id) && $v['id'] == $id){
	            return $v;
	        }elseif (!is_numeric($id)){
	            return $this->getCurrencyByCode($id);
	        }
	    }
	}
	
	public function getCurrencyByCode($code){
	    $l = $this->getUserCurrency();
	    foreach ($l as $v){
	        if(!is_numeric($code) && $v['code'] == $code){
	            return $v;
	        }elseif (is_numeric($code)){
	            return $this->getCurrency($code);
	        }
	    }
	}
	
	public function getId(){
	    if($this->_id === null){
	        $this->_id = $this->default['id'];
	    }
	    return $this->_id;
	}
	
	public function getCode($id = 1){
	    if($id != $this->getId()){
	        $item = $this->getCurrency($id);
	        return $item['code'];
	    }
	    if($this->_code === null){
	        $this->_code = $this->default['id'];
	    }
	    return $this->_code;
	}
	
	public function getCurrencyCode($id){
	    $item = $this->getCurrency($id);
	    return $item['code'];
	}
	
	public function getSymbol($id){
	    $item = $this->getCurrency($id);
	    return $item['symbol'];
	}
	
	public function getSymbol2($id){
	    $item = $this->getCurrency($id);
	    return $item['symbol2'];
	}
	
	public function getPrecision($id){
	    $item = $this->getCurrency($id);
	    return $item['decimal_number'];
	}
	
	public function getPrice($price, $params){
	    $from = isset($params['from']) ? $params['from'] : 1;
	    $to = isset($params['to']) ? $params['to'] : 1;
	    if($from == $to){
	        return ['price'=>$price, 'currency'=>$from, 'status'=>0,'decimal'=>$this->getPrecision($from)];
	    }
	    
	    return [
	        'price'=>$price * $params['exchange_rate'], 
	        'currency'=>$to, 
	        'status'=>1,
	        'decimal'=>$this->getPrecision($to),
	        'old_price'=>$price,
	        'old_currency'=>$from
	    ];
	}
	
	
	public function showPrice($params){
	    $html = '<span '.(
	        $params['status'] ? 'class="text-price red underline" title="'.$this->getCurrencyText($params['old_price'], $params['old_currency']).'"' : 'class="text-price"'
	        ).'>'.number_format($params['price'], $params['decimal']).'</span>';
	    return $html;
	}
	
	
	
	public function getCurrencyText($number, $currency,$o=[]){
	    if(is_numeric($currency)){
	        $currency = $this->getCurrency($currency);
	    }
	    //
	    $preText = $afterText = '';
	    
	    $priceText = number_format($number, $currency['decimal_number']);
	    
	    switch ($currency['display_type']){
	        case 1: $preText = ''; $afterText = $currency['symbol']; break;
	        case 2: $preText = ''; $afterText = $currency['code']; break;
	        case 3: $preText = $currency['symbol']; $afterText = ''; break;
	        case 4: $preText = $currency['code']; $afterText = ''; break;
	        case 5: $preText = ''; $afterText = $currency['symbol2']; break;
	        case 6: $preText = $currency['symbol2']; $afterText = ''; break;
	        case 7: $preText = ''; $afterText = ' ' . $currency['symbol2']; break;
	        case 8: $preText = ''; $afterText = ' ' . $currency['code']; break;
	    }
	    
	    if(isset($o['show_symbol']) && !$o['show_symbol']){
	        $preText = $afterText = '';
	    }
	    
	    return $preText . $priceText . $afterText;
	}
	
	
	private function getExchangeRates(){
	    //$url = 'http://www.vietcombank.com.vn/ExchangeRates/ExrateXML.aspx';
		$url = 'https://portal.vietcombank.com.vn/Usercontrols/TVPortal.TyGia/pXML.aspx';		
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);  
		$response = file_get_contents($url, false, stream_context_create($arrContextOptions));
	    return simplexml_load_string($response);
	}
	
	public function updateExchangeRate(){
	    $d = $this->getExchangeRates();
	    
	    //view($d,1,1);
	    
	    if(isset($d->Exrate)){
	        $g_currency = $this->getId();
	        
	        $time = strtotime($d->DateTime);
	        $time_update = date("Y-m-d H:i:s",$time);
	         
	        if((new \yii\db\Query())->from('exchange_rate')->where(array('updated_at'=>$time_update))->count(1) == 0){
	            foreach ($d->Exrate as $object){
	                
	                $v = xmlObject2Array($object); 
					
					$v['Buy'] = cprice($v['Buy']);
					$v['Sell'] = cprice($v['Sell']);
					$v['Transfer'] = cprice($v['Transfer']);
	                
	                if($v['Buy'] ==0 || $v['Sell'] == 0) continue;
	                
	                if((new \yii\db\Query())->from('currency')->where(['code'=>$v['CurrencyCode']])->count(1) == 0){
	                    Yii::$app->db->createCommand()->insert('currency',array(
	                        'code'=>$v['CurrencyCode'],
	                        'symbol'=>$v['CurrencyCode'],
	                        'name'=>trim($v['CurrencyName']),
	                        'title'=>trim($v['CurrencyName']),
	                    ))->execute();
	                    
	                }else{
	                    
	                }
	                //
	                // Cập nhật tỷ giá
	                //$item = \app\modules\admin\models\Currency::getItemByCode($v['CurrencyCode']);
	                $item = $this->getModel()->getItemByCode($v['CurrencyCode']);

	                if(!empty($item)){
	                    
	                  
	                    $ex  = $this->getExrate(array(
	                        'from' => $item['id'],
	                        'to'=>$g_currency,
	                        'return'=>'last'
	                    ));
	                    //view($ex,$v['Buy'],1);
	                    //
	                    if(!empty($ex) && $ex['value'] == $v['Buy']){
	                        // Khi tỷ giá không tăng
	                    }else{
	                        // Khi tỷ giá biến động
							try{
								Yii::$app->db->createCommand()->insert('exchange_rate',array(
									'from_currency'=>$item['id'],
									'value'=>$v['Buy'],
									'sell'=>$v['Sell'],
									'transfer'=>$v['Transfer'],
									'buy'=>$v['Buy'],
									'from_date'=>$time_update,
									'updated_at'=>time(),
									'increase' => $v['Buy'] - $ex['value'],
									'to_currency'=>$g_currency)
								)->execute();
							}catch(\Exception $e){
								
								echo 'Đã xảy ra lỗi đối với '.$v['CurrencyName'].': ',  $e->getMessage(), "\n";
							}
	                    }
	                }
	            }
	        }}
	         
	}
	
	
	/**
	 * Lấy tỷ giá hiện tại, mới nhất
	 */
	
	public function getNewestExrate($params)
	{
	    $params['return'] = 'last';
	    $ex = $this->getExrate($params);
	    if(!empty($ex)) return $ex['value'];
	    return 0;
	}
	
	
	public function getExrate($params){
	    $from = isset($params['from']) ? $params['from'] : 0;
	    $to = isset($params['to']) ? $params['to'] : 0;
	    
	    $time = isset($params['time']) ? $params['time'] : false;
	    	   
	    if(!is_numeric($from)){
	        $c = $this->getCurrencyByCode($from);
	        $from = $c['id'];
	    }
	    if(!is_numeric($to)){
	        $c = $this->getCurrencyByCode($to);
	        $to = $c['id'];
	    }
	    
	    /**
	     * Build query
	     */
	    $query = (new \yii\db\Query())
	    ->select(['to_currency', 'value', 'from_date'])
	    ->from('exchange_rate')->where(['from_currency' => $from]);
	    
	    if($to > 0){
	        $query -> andWhere(['to_currency' => $to]);
	    }
	    
	    if($time > 0){
	        $query->andWhere(new \yii\db\Expression("DAYOFYEAR(from_date)=".date('z',$time) . ""));
	        $query->andWhere(new \yii\db\Expression("YEAR(from_date)=".date('Y',$time) . ""));
	    }
	    
	    $query->orderBy(['updated_at' => SORT_DESC]);
	    
	    if(isset($params['return']) && $params['return'] == 'last'){
	        return $query->limit(1)->one();
	    }
	    return $query->all();
	    
	    /* $time = check_date_string($time) ? ctime(array('string'=>$time ,'return_type'=>1)) : false;
	    $sql = "select a.to_currency,a.value,a.from_date from exchange_rate as a where a.from_currency=$from";
	    $sql .= $to > 0 ? " and a.to_currency=$to" : "";
	    
	    $sql .= $time !== false ? " and DAYOFYEAR(a.from_date)=".date('z',$time) . " and YEAR(a.from_date)=" . date('Y',$time) : '';
	    $sql .= " order by a.from_date desc";
	    if(isset($params['return']) && $params['return'] == 'last'){
	        $sql .= " limit 1";
	        return Yii::$app->db->createCommand($sql)->queryOne();
	    }
	    return Yii::$app->db->createCommand($sql)->queryAll(); */
	}
	
	
	private $_mangso = [
	    'vi-VN' => ['không','một','hai','ba','bốn','năm','sáu','bảy','tám','chín'],
	    'en-US' => ['zero','one','two','three','four','five','six','seven','eight','nine']
	];
	
	public function getMangSo($lang = __LANG__)
	{
	    if(isset($this->_mangso[$lang])) return $this->_mangso[$lang];
	    return $this->_mangso[SYSTEM_LANG];
	}
	
	public function docHangChuc($so, $daydu, $lang = __LANG__)
	{
	   
	    $mangso = $this->getMangSo($lang);
	    
	    $chuoi = "";
	    $chuc = floor($so/10);
	    $donvi = $so%10;
	    if ($chuc>1) {
	        $chuoi = " " . $mangso[$chuc] . " mươi";
	        if ($donvi==1) {
	            $chuoi .= " mốt";
	        }
	    } else if ($chuc==1) {
	        $chuoi = " mười";
	        if ($donvi==1) {
	            $chuoi .= " một";
	        }
	    } else if ($daydu && $donvi>0) {
	        $chuoi = " lẻ";
	    }
	    if ($donvi==5 && $chuc>1) {
	        $chuoi .= " lăm";
	    } else if ($donvi>1||($donvi==1&&$chuc==0)) {
	        $chuoi .= " " . $mangso[$donvi];
	    }
	    return $chuoi;
	}
	
	public function docBlock($so,$daydu, $lang = __LANG__)
	{
	    $mangso = $this->getMangSo($lang);
	    $chuoi = "";
	    $tram = floor($so/100);
	    $so = $so%100;
	    if ($daydu || $tram>0) {
	        $chuoi = " " . $mangso[$tram] . " trăm";
	        $chuoi .= $this->docHangChuc($so,true,$lang);
	    } else {
	        $chuoi = $this->docHangChuc($so,false,$lang);
	    }
	    return $chuoi;
	}
	
	public function docHangTrieu($so, $daydu, $lang = __LANG__)
	{
	    $chuoi = "";
	    $trieu = floor($so/1000000);
	    $so = $so%1000000;
	    if ($trieu>0) {
	        $chuoi = $this->docBlock($trieu,$daydu, $lang) . " triệu";
	        $daydu = true;
	    }
	    $nghin = floor($so/1000);
	    $so = $so%1000;
	    if ($nghin>0) {
	        $chuoi .= $this->docBlock($nghin,$daydu, $lang) . " nghìn";
	        $daydu = true;
	    }
	    if ($so>0) {
	        $chuoi .= $this->docBlock($so,$daydu, $lang);
	    }
	    return $chuoi;
	}
	
	public function toText($so, $currency = 1, $lang = __LANG__){
	    $mangso = $this->getMangSo($lang);
	    if ($so==0) return $mangso[0];
	    $chuoi = "";
	    $hauto = "";
	    $chan = "";
	    $c = $currency == 1 ? 'đồng' : '';
	    if($so % 10000 == 0 && $currency == 1) $chan = ' chẵn';
	    do {
	        $ty = $so%1000000000;
	        $so = floor($so/1000000000);
	        if ($so>0) {
	            $chuoi = $this->docHangTrieu($ty,true, $lang) . $hauto . $chuoi;
	        } else {
	            $chuoi = $this->docHangTrieu($ty,false, $lang) . $hauto . $chuoi;
	        }
	        $hauto = " tỷ";
	    } while ($so>0);
	    return ucfirst(trim($chuoi)) .' ' . $c ;
	}
	
	
	public function getAllUserExchangeRate($default_currency = 1){
 
	    
	    $currencies = $this->getUserCurrency();
	    
	    $exrate = [
	        'time' => time(),
	    ];
	    
	    if(!empty($currencies))
	    {
	        foreach($currencies as $c){
	            if($c['id'] == $default_currency) continue;
	            $exrate[$c['id']][$default_currency] = $this->getNewestExrate(['from' => $c['id'], 'to' => $default_currency]);
	            
	        }
	    }
	     
	    return $exrate;
	}
	
	
	public function getLastestExchangeRate($params){
		return $this->getModel()->getLastestExchangeRate($params);
	}
	
	public function getAllExchangeRate($default_currency = 1){
		$currencies = $this->getUserCurrency();
		
// 		view($currencies);
		
		$exrate = [];
		foreach($currencies as $c){
			
			//foreach($currencies as $c2){
				if($c['id'] != $default_currency){
					
					$e = $this->getLastestExchangeRate(['to' => $default_currency, 'from' => $c['id'], 'select' => ['sell', 'buy', 'transfer', 'amount'] ]);
					
					$exrate[$default_currency][$c['id']] = $e;
				}
			//}
		}
		
		
		return $exrate;
	}
}

