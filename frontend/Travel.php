<?php 

namespace izi\frontend;
use Yii;

use izi\frontend\models\Articles;

class Travel extends \yii\base\Component
{
    public $frontend;
    
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject([
                'class' =>  'izi\\frontend\\models\\Travel',
                'frontend' => $this,
                'box'  =>  Yii::$app->box
            ]);
        }
        
        return $this->_model;
    }
     
    
    private $_service;
    
    public function getService(){
        if($this->_service == null){
            $this->_service = Yii::createObject([
                'class' =>  'izi\\frontend\\models\\Service',
//                 'frontend' => $this,
//                 'box'  =>  Yii::$app->box
            ]);
        }
        
        return $this->_service;
    }
    
    
    public function getDefaultFilter($item_id, $filter_code){
        $l = (new \yii\db\Query())->from(['a'=>'filters'])
        ->innerJoin(['b'=>'articles_to_filters'],'a.id=b.filter_id')
        ->where([
            'a.code'=>$filter_code,
            'a.sid'=>__SID__,
            'a.lang'=>__LANG__,
            'b.state'=>1,
            'b.item_id'=>$item_id
        ])->one();
        
        
        if(empty($l)){
            $l = (new \yii\db\Query())->from(['a'=>'filters'])
            ->innerJoin(['b'=>'articles_to_filters'],'a.id=b.filter_id')
            ->where([
                'a.code'=>$filter_code,
                'a.sid'=>__SID__,
                'a.lang'=>__LANG__,
                //'b.state'=>1,
                'b.item_id'=>$item_id
            ])->one();
        }
        return $l;
    }
    
    
    
    public function getFilterTourType1NextDay($item_id){
        //$ex = new \yii\db\Expression(" REPLACE(`code`, \"tour_date_time_\", \"\")");
        $query = (new \yii\db\Query())->from(['a'=>'filters'])
        ->innerJoin(['b'=>'articles_to_filters'],'a.id=b.filter_id')
        ->where(['b.item_id'=>$item_id,'a.code'=>'tour_date_time'])
        ->andWhere(['>','b.to_date',date('Y-m-d H:i:s')])
        ->orderBy(['b.to_date'=>SORT_ASC])->limit(1)
        ;
        return $query->one();
    }
    
    
    public function getTourPrice($item_id, $price_code = ''){
        return (new \yii\db\Query())->from('articles_prices')->where(['item_id'=>$item_id,'code'=>$price_code])->one();
    }
    
    
    
    public function getPriceList($item_id, $params = [])
    {
        $item = (new \app\modules\admin\models\Content())->getItem($item_id);
        $currency = isset($item['currency'])  ? $item['currency'] : Yii::$app->currencies->default['id'];
        
        $html = '';
        
        $html .= '<fieldset class="f14px"><legend class="bold">Cài đặt chung: </legend>
  
    <p class="italic help-block mgl15 underline">Lưu ý: tất cả các thông tin thay đổi ở tab "Bảng giá" sẽ được cập nhật tự động vào cơ sở dữ liệu mà không cần thông qua sự kiện bấm nút "Lưu...".</p>
    
    <div class="col-sm-6 col-xs-12">
    
    <div class="form-horizontal">
  <div class="form-group">
    <label for="'.($r2 = randString(12)).'" class="col-sm-2 control-label">Tiền tệ</label>
    <div class="col-sm-10">
    <div class="w150px">
<select data-loading="left-small"
data-target_input=".'.($input_currency = randString(12)).'"
onchange="call_ajax_function(this);" 
data-action="change_article_attribute"
data-item_id="'.$item_id.'"
data-field="currency"
data-search="hidden" class="select2" name="f[currency]" id="'.($r2).'">';

foreach (Yii::$app->currencies->getUserCurrency() as $c){
    $html .= '<option '.($c['id'] == $currency ? 'selected' : '').' value="'.$c['id'].'">'.($c['title'] . ' (' . $c['code'] . ')').'</option>';
}

$html .= '</select></div>
      <p class="italic help-block">*** Lưu ý: thay đổi tiền tệ nhiều lần có thể sẽ làm sai lệch giá tour (các giá trị ở phần thập phân sẽ bị làm tròn).</p>
    </div>
  </div>
  <div class="form-group">
    <label for="'.($r2 = randString(12)).'" class="col-sm-2 control-label">Đặt trước</label>
    
    <div class="col-sm-3">

<div class="input-group">
  
  <input type="number" 
  
onblur="call_ajax_function(this);"
data-loading="left-small"
data-action="change_article_attribute"
data-field="before_booking"
data-biz="1"
data-item_id="'.$item_id.'"
class="form-control bold center" placeholder="Số ngày tối thiểu tính từ thời điểm đặt tour tới ngày khởi hành gần nhất" 
aria-describedby="'.($r2 = randString(12)).'" 
value="'.(isset($item['before_booking'])  ? $item['before_booking'] : 1).'"
data-old="'.(isset($item['before_booking'])  ? $item['before_booking'] : 1).'"
>
  
  <span class="input-group-addon" id="'.$r2.'">ngày</span>
  
</div>      


    </div>
    
     
  </div>
   
   
    <div class="form-group">
    <label for="'.($r2 = randString(12)).'" class="col-sm-2 control-label">Giới hạn độ tuổi</label>
    <div class="col-sm-3">

<div class="input-group">
  <span class="input-group-addon" id="'.($r2 = randString(12)).'">từ</span>
  <input
onblur="call_ajax_function(this);"
data-loading="left-small"
data-action="change_article_attribute"
data-field="min_age"
data-biz="1"
data-item_id="'.$item_id.'"
value="'.(isset($item['min_age'])  ? $item['min_age'] : 0).'"
data-old="'.(isset($item['min_age'])  ? $item['min_age'] : 0).'"
type="text" class="form-control bold center" placeholder="Tối thiểu" aria-describedby="'.$r2.'">
</div>      


    </div>
    
    <div class="col-sm-3">

<div class="input-group">
  <span class="input-group-addon" id="'.($r2 = randString(12)).'">đến</span>
  <input
  onblur="call_ajax_function(this);"
data-loading="left-small"
data-action="change_article_attribute"
data-field="max_age"
data-biz="1"
data-item_id="'.$item_id.'"
value="'.(isset($item['max_age'])  ? $item['max_age'] : 0).'"
data-old="'.(isset($item['max_age'])  ? $item['max_age'] : 0).'"
  type="text" class="form-control bold center" placeholder="Tối đa" aria-describedby="'.$r2.'">
</div>      
   </div>
    
 
  </div>
   
 
</div>
     
     
     </div> 
     
     
     
</fieldset>';


$html .= '<fieldset class="f14px mgt30">
    <legend class="bold">Lịch khởi hành: </legend>';
 

$depart_type = isset($item['departure_type']) ? $item['departure_type'] : 0;
$departTypeTarget = randString(12);
foreach(($departTypes = Yii::$app->frontend->travel->model->getDepartureType()) as $departType)
{
    $html .= '<label for="'.($r = randString(12)).'">'.$departType['title'].'</label>
    <input
onchange="call_ajax_function(this);"
data-loading="left-small"
data-action="change_article_attribute"
data-field="departure_type"
data-biz="1"
data-item_id="'.$item_id.'"
data-target=".'.($departTypeTarget).'"
'.($departType['value']==$depart_type? 'checked' : '').' class="checkboxradio skipUnloadValidate" type="radio" name="biz[departure_type]" id="'.$r.'" value="'.$departType['value'].'" >';
}

//
$pax_groups = [
    [
       'id'=>0, 'title'=>'Không phân nhóm'
    ]
];


$age_groups = Yii::$app->frontend->travel->model->getGuestGroups([
    'in'    =>  isset($item['age_groups']) ? $item['age_groups'] : [],
    'item_id'=>$item_id,
    'first_init'=>isset($item['age_groups']) && !empty($item['age_groups']) ? false : true,
]);

$childs_groups = [];

foreach ($age_groups as $k => $group){
    if($group['id']>0 && !(isset($group['is_default']) && $group['is_default'] == 1)){
        $childs_groups[] = $group;
        unset($age_groups[$k]);
    }
}


$age_groups[] = [
    'id'=>0, 
    'title'=>'PT ngủ đơn',
    'price_type_id'=>1,
];
 

  $html .= '</fieldset>';
  
  
  $html .= '<table class="table table-triped table-hover vmiddle table-responsive mgt15 table-bordered">
<caption><span class="f14px bold">Bảng giá (áp dụng cho người lớn - cài đặt nhóm tuổi click <a
data-action="travel_item_change_age_group" data-loading="fb2" data-item_id="'.$item_id.'" 
onclick="return call_ajax_function(this);" class="underline pointer">vào đây</a>)</span></caption>
<thead>';

 

$colspan1 = max(1, count($age_groups));

$colspan2 = max(1, $colspan1 * count($pax_groups) );

$cls = in_array($depart_type, [3,5]) ? true : false;

$html .= '<tr>
<th rowspan="3" class="w150p center">Ngày khởi hành<span class="hdkjahsj" '.($cls ? '' : 'style="display:none"').'>
<button data-loading="fb2"
onclick="call_ajax_function(this);"
data-action="change_travel_item_departure_day_selected"
data-item_id="'.$item_id.'"
data-departure_type_id="'.$depart_type.'"
 type="button" class="btn btn-sm btn-success mgt5"><i class="fa fa-check-square"> chọn ngày</i></button>
</span></th>
<th class="center pr" colspan="'.($colspan2).'">Ghép đoàn <i title="Thiết lập thuộc tính cho tour ghép đoàn" class="fa fa-cogs ps r10 t10 pointer hover-red"></i></th>
<th class="center pr" colspan="'.($colspan2).'">Đoàn riêng <i title="Thiết lập thuộc tính cho tour đoàn riêng" class="fa fa-cogs ps r10 t10 pointer hover-red"></i></th>
<th rowspan="3" class="w100p center">Thao tác</th>
</tr>';

if(!empty($pax_groups)){
    $html .= '<tr class="'.(count($pax_groups) > 1 ? '' : 'hide').'">';
    foreach ($pax_groups as $group){
        $html .= '<th class="center" colspan="'.$colspan1.'">'.$group['title'].'</th>';
    }
    $html .= '</tr>';
}

 
if(!empty($age_groups)){
    $html .= '<tr>';
    foreach ($age_groups as $group){
        
        $a = $group['id'] > 0 ? '<a 
onclick="call_ajax_function(this);"
data-action="travel_item_change_age_group" data-loading="fb2"
data-item_id="'.$item_id.'"
data-item_id="'.$item_id.'"
href="javascript:void(0);" title="Chỉnh sửa cài đặt nhóm.">'.$group['title'].'</a>' : $group['title'];
        
        $html .= '<th class="center">'.$a.'</th>';
    }
    
    foreach ($age_groups as $group){
        $a = $group['id'] > 0 ? '<a
onclick="call_ajax_function(this);" data-loading="fb2"
data-action="travel_item_change_age_group"
data-item_id="'.$item_id.'"
data-item_id="'.$item_id.'"
href="javascript:void(0);" title="Chỉnh sửa cài đặt nhóm.">'.$group['title'].'</a>' : $group['title'];
        
        $html .= '<th class="center">'.$a.'</th>';
    }
    
    
    $html .= '</tr>';
}
 

$html .= '</thead>';

 

foreach($departTypes as $departType)
{
    
    $html .= Yii::$app->view->renderPhpFile(Yii::$app->controller->module->viewPath. '/content/pages/tours/v2/partials/prices-'.$departType['id'].'.php', [
        'item_id'=>$item_id, 
        'item'=>$item,
        'departType'=>$departType,
        'depart_type'=>$depart_type,
        'departTypeTarget'=>$departTypeTarget,
//         'age_groups'=>$age_groups,
        'age_groups'=>$age_groups,
        'currency'=>$currency,
        'input_currency'=>$input_currency
    ]);
    
    

    
}
 


$html .= '<tfoot></tfoot>
</table>    ';



$html .= '<table class="table table-triped table-hover vmiddle table-responsive mgt15 table-bordered">
<caption><span class="f14px bold">Chính sách trẻ em (tính theo tỉ lệ % so với giá người lớn)</span></caption>
<thead>';

 
 


if(!empty($childs_groups)){
    $html .= '<tr>
<th class="w150p">Nhóm</th>
';
    foreach ($childs_groups as $group){
 
        
        if($group['id']>0 && !(isset($group['is_default']) && $group['is_default'] == 1)){
        
        $a = $group['id'] > 0 ? '<a
onclick="call_ajax_function(this);"
data-action="travel_item_change_age_group" data-loading="fb2"
data-item_id="'.$item_id.'"
data-item_id="'.$item_id.'"
href="javascript:void(0);" title="Chỉnh sửa cài đặt nhóm.">'.$group['title'].'</a>' : $group['title'];
        
        $html .= '<th class="center w150p">'.$a.'</th>';
        }
    }
   
    
    
    $html .= '
<th></th>
</tr>';
}


$html .= '</thead>';


$html .= '<tr><td class="aleft bold">Giá</td>';
foreach ($childs_groups as $group){
    
    
    if($group['id']>0 && !(isset($group['is_default']) && $group['is_default'] == 1)){
        $html .= '<td class="center">
<input placeholder="0.00%"
onblur="call_ajax_function(this);"
data-action="change_article_travel_price"
data-loading="left-small"
data-item_id="'.$item_id.'"
data-old="'.($value = Yii::$app->frontend->travel->model->getPrice([
    'item_id' => $item_id,
    'age_group_id' => $group['id'],
    'departure_type_id' =>  $departType['id'],
    'type_id'   =>  0,
    'price_type_id'=>(isset($group['price_type_id']) ? $group['price_type_id'] : 0),
    
])).'"

data-decimal="2"
data-quotation_id="0"
data-package_id="0"
data-nationality_id="0"
data-group_id="0"
data-age_group_id="'.$group['id'].'"
data-departure_type_id="'.$departType['id'].'"
data-type_id="0"
data-price_type="1"
data-price_type_id="'.(isset($group['price_type_id']) ? $group['price_type_id'] : 0).'"
data-departure_date=""
value="'.$value.'"
class="form-control input-sm bold red number-format aright skipUnloadValidate" />
</td>';
    }
}
$html .= '<td class=""><i>Giá trẻ em áp dụng chung cho cả tour ghép & tour riêng</i></td></tr>'; 



$html .= '<tfoot></tfoot>
</table>    ';
        
        
        return $html;
    }
    
    
    
    
    public function getTravelInfo($item_id)
    {
        return (new \yii\db\Query())->from('tours_attrs')->where(['item_id'=>$item_id])->one();
    }
    
    
    public function getTravelPrice($item_id, $params = [])
    {
        $item = isset($params['item']) ? $params['item'] : $this->getModel()->getItem($item_id);
        
        $price = 0;
        
        $type_id = isset($params['type_id']) ? $params['type_id'] : 1;
        
        $group = $this->getModel()->getDefaultGuestGroup();
        
        
        switch ($item['departure_type']) {
            
            case 2: // Hàng tuần
                
                break;
            case 3: // Hàng tháng
                
                break;
            case 4: // Hàng năm
                
                break;
            case 5: // Ngày cố định
                
                break;
            
            default:
                $price = Yii::$app->frontend->travel->model->getPrice([
                'item_id' => $item_id,
                'age_group_id' => $group['id'],
                'departure_type_id' =>  $item['departure_type'],
                'type_id'   =>  $type_id,
                'price_type_id'=>(isset($group['price_type_id']) ? $group['price_type_id'] : 0),
                
                ])
                ;
                break;
        }
        
        return $price;
    }
    
    
    public function showDepartureDate($item) 
    {
        $html ='';
        
        switch ($item['departure_type']){
            
            case 1:
                $html .= 'Hàng ngày';
                break;
                
            case 2:
                
                $departure_selected = isset($item['departure_selected'][$item['departure_type']]) ? $item['departure_selected'][$item['departure_type']] : [];
                
                $days = [];
                if(!empty($departure_selected)){
                    foreach ($departure_selected as $day){
                        $days[] = readFullTextDayOfWeek($day, __LANG__,'w',false);
                    }
                }
                
                $html .= implode(', ', $days);
                
                $html .= ' hàng tuần';
                break;
                
            case 3:
                $departure_selected = isset($item['departure_selected'][$item['departure_type']]) ? $item['departure_selected'][$item['departure_type']] : [];
                
                
                
                $html .= Yii::$app->t->translate('label_day' ) . ' ' . implode(', ', $departure_selected);
                
                $html .= ' hàng tháng';
                break;
                
            case 4:
                $html .= '';
                break;
                
            case 5:
                $departure_selected = isset($item['departure_selected'][$item['departure_type']]) ? $item['departure_selected'][$item['departure_type']] : [];
                
                $days = [];
                if(!empty($departure_selected)){
                    
                    sort($departure_selected);
                    
                    foreach ($departure_selected as $day){
                        
                        if(($time = strtotime($day) ) > time()){
                            $days[] = date('d/m/Y', $time);
                        } 
                    }
                }
                
                
                
                $html .=  implode(', ', $days);
                
                
                break;
            default:
                $html .= 'Tùy chọn';
                break;
        }
        
        echo $html;
    }
    
    
    public function quickImportData($data, $replace = false)
    {
        $url = unMark($data['title']);
        
        $item = Yii::$app->frontend->model->getItemByUrl($url);
        
//          view($item);
        
        if(empty($item)){
            
            $f['sid'] = __SID__;
            $f['lang'] = __LANG__;
            $f['created_by'] = Yii::$app->user->id;
            $f['updated_at'] = date('Y-m-d H:i:s');
            $f['is_active'] = 1;
            $f['title'] = $data['title'];
            $f['url']   = $url;
            $f['type']   = 'tours';
            
            
            if(Yii::$app->db->createCommand()->insert(Yii::$app->frontend->model->tableName(),$f)->execute()){
                $item_id = Yii::$app->db->getLastInsertID();
            }
            
        }else{
            $item_id = $item['id'];
            if(!$replace) return;
        }
        
        // Update
        
        $f['price2'] = $data['price2'];
        
        $f['updated_at'] = date('Y-m-d H:i:s');
        
        $biz = [
            'icon'  =>  $data['icon'],
            'list_images'   =>  $data['list_images'],
            'info' => '',
            'summary'=>'',
            'age_groups'    =>  [3,2,1],
            
        ];
        
        if(isset($data['full_text_vehicle'])){
            $biz['full_text_vehicle'] = $data['full_text_vehicle'];
        }
        
        if(isset($data['departure_type'])){
            $biz['departure_type'] = $data['departure_type'];
        }
        
        if(isset($data['departure_selected'])){
            $biz['departure_selected'] = $data['departure_selected'];
        }
        if(isset($data['short_itinerary'])){
            $biz['short_itinerary'] = $data['short_itinerary'];
        }
        
        
        
        $f['url_link'] = Yii::$app->izi->getUrl($url);
        
        $f['bizrule'] = json_encode($biz,JSON_UNESCAPED_UNICODE);
        
        $con = array('id'=> $item_id,'sid'=>__SID__);
        
        Yii::$app->db->createCommand()->update(Yii::$app->frontend->model->tableName(),$f,$con)->execute();
        
        $model = new \app\modules\admin\models\Content();
        
        $model->updateCategory($item_id, $data['category']);
        
         
        
        $model->updateAttrType($item_id, 'tours', [
            'day'=>isset($data['day']) ? $data['day'] : 0,
            'night'=>isset($data['night']) ? $data['night'] : 0
        ]);
        
        $tabs = [
            ['title'=>'Chi tiết', 'type'=>'program'],
           // ['title'=>'Chi tiết 2', 'type'=>'program']
        ];
        
//         view($tabs);
        
//tab_biz[0][program][before][0][title]
        
//                 view($data['tabs'] ,1,1);
        
        if(isset($data['tabs'])){
            $model->updateTabs($item_id, [
                'tab_biz'   =>  $data['tabs'],
                'tab_position'  =>  [0],
                'tab'   =>  $data['tabs_title'] ,
            ]);
        }else{
        
        
        if(isset($data['detail'])){
            
            $tab_biz = [
                ['program'=>[
                    'before'=>[
                        [
                    'title'=>'',
                    'is_active'=>'on',
                    'text'=>$data['detail']
                        ]
                ]]]
            ];
            
            $model->updateTabs($item_id, [
                'tab_biz'   =>  $tab_biz,
                'tab_position'  =>  [0],
                'tab'   =>  $tabs,
            ]);
        }
        }
        
        \app\modules\admin\models\Slugs::updateSlug($url,$item_id,'tours',1,[]);
        
        
        if(isset($data['departure_selected'][$data['departure_type']]) && !empty($data['departure_selected'][$data['departure_type']])){
            $days = $data['departure_selected'][$data['departure_type']];
        }else{
            $days = [0];
        }
        
        foreach ($days as $day_id){
        
        //
        $con = [
            'item_id'               =>  $item_id,
            'quotation_id'          =>  0,
            'package_id'            =>  0,
            'nationality_id'        =>  0,
            'group_id'              =>  0,
            'age_group_id'          =>  3,
            'departure_type_id'     =>  $data['departure_type'],
            'type_id'               =>  1,
            'price_type_id'         =>  0,
            'day_id'                =>  $day_id,
            'price_type'            =>  0,
            
        ];
//         if($departure_date != "" && $departure_date != '0000-00-00'){
//             $con['departure_date'] = $departure_date;
//         }
        
        $state = Yii::$app->frontend->travel->model->updatePrice($f['price2'], $con);
        
        //
        $con = [
            'item_id'               =>  $item_id,
            'quotation_id'          =>  0,
            'package_id'            =>  0,
            'nationality_id'        =>  0,
            'group_id'              =>  0,
            'age_group_id'          =>  3,
            'departure_type_id'     =>  $data['departure_type'],
            'type_id'               =>  2,
            'price_type_id'         =>  0,
            'day_id'                =>  $day_id,
            'price_type'            =>  0,
            
        ];
        //         if($departure_date != "" && $departure_date != '0000-00-00'){
        //             $con['departure_date'] = $departure_date;
        //         }
        
        $state = Yii::$app->frontend->travel->model->updatePrice($f['price2'], $con);
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}