<?php
/**
 * 
 * @link http://iziweb.vn
 * @copyright Copyright (c) 2016 iziWeb
 * @email zinzinx8@gmail.com
 *
 */
namespace izi\web;
use Yii;
class Airport extends \yii\base\Component
{
	
	 private $_model;
	 
	 public function getModel(){
	     if($this->_model == null){
	         $this->_model = Yii::createObject('izi\models\Airport');
	     }
	     
	     return $this->_model;
	 }
	
	 public function getItems($params = []) 
	 {
	     return $this->getModel()->getItems($params);
	 }
	 
	 public function getItem($id){
	     
        return $this->getModel()->getItem($id);
	     
	 }
	 
	 public function validateData($data, $existed_id = 0)
	 {
	     if($data['name'] == "") return false;
	     
	     $con = [
	         'lang_code' => $data['lang_code']
	     ];
	     
	     if(isset($data['IATA']) && $data['IATA'] != ""){
	         $con['IATA'] = $data['IATA'];
	     }
	     
	     if(isset($data['ICAO']) && $data['ICAO'] != ""){
	         $con['ICAO'] = $data['ICAO'];
	     }
	     
	     $query = (new \yii\db\Query())->from($this->getModel()->tableName())
	     ->where(['or', 
	           $con
	         ]
	     );
	     if($existed_id > 0){
	         $query->andWhere(['not in', 'id', $existed_id]);
	     }
	     
	     $r = $query->one();
	     
	     if(!empty($r)){
	         return false;
	     }
	     return true;
	 }
	 
	 public function renderData($data)
	 {
	     $data['name'] = trim_space($data['name']);
	     
	     $data['title'] = $data['name'];
	     
// 	     if(isset($data['is_airport']) && $data['is_airport'] == 1){
// 	         $data['lang_code'] = "port_" . unMark($data['name'], '_');
// 	     }else{
         $data['lang_code'] = "port_" . unMark($data['name'], '_');
// 	     }
	     
	     if(isset($data['IATA']) && $data['IATA'] != ""){
	         $data['IATA'] = strtoupper(preg_replace('/\s/', '', $data['IATA']));
	     }elseif(isset($data['IATA'])){
	         unset($data['IATA']);
	     }
	     if(isset($data['ICAO']) && $data['ICAO'] != ""){
	         $data['ICAO'] = strtoupper(preg_replace('/\s/', '', $data['ICAO']));
	     }elseif(isset($data['ICAO'])){
	         unset($data['ICAO']);
	     }
	     
	     return $data;
	 }
	 
	 public function insertData($data)
	 {
	     if($this->validateData($data = $this->renderData($data))){
	         Yii::$app->db->createCommand()->insert($this->getModel()->tableName(), $data)->execute();
	         Yii::$app->t->dbUpdateTextTranslate($data['lang_code'], 'vi-VN', $data['name'],1);	         
	         Yii::$app->t->dbUpdateTextTranslate($data['lang_code'], 'en-US', unMark($data['name'], ' ',false),1);
	         return Yii::$app->db->lastInsertID;
	     }
	     
	 }
	 
	 public function updateData($id, $data)
	 {
	     if($this->validateData($data = $this->renderData($data), $id)){
	         Yii::$app->db->createCommand()->update($this->getModel()->tableName(), $data, ['id' => $id])->execute();
	         Yii::$app->t->dbUpdateTextTranslate($data['lang_code'], 'vi-VN', $data['name'],1);
	         Yii::$app->t->dbUpdateTextTranslate($data['lang_code'], 'en-US', unMark($data['name'], ' ',false),1);
	         return $id;
	     }
	     
	 }
	 
	 public function getLevel()
	 {
	     return [
	         ['id' => 1, 'name' => 'Quốc tế'],
	         ['id' => 2, 'name' => 'Quốc gia'],
	         ['id' => 3, 'name' => 'Tỉnh'],
	     ];
	 }
	 
	 public function showLevelName($level){
	     $level2 = $this->getLevel();
	     foreach ($level2 as $v){
	         if($v['id'] == $level) return $v['name'];
	     }
	 }
	 
	 public function getType()
	 {
	     return [
	         ['id' => 1, 'name' => 'Hàng không'],
	         ['id' => 2, 'name' => 'Đường bộ'],
	         ['id' => 3, 'name' => 'Đường sắt'],
	         ['id' => 4, 'name' => 'Đường biển'],
	     ];
	 }
	 
	 public function showTypeName($type_id){
	     
	     foreach ($this->getType() as $v){
	         if($v['id'] == $type_id) return $v['name'];
	     }
	 }
	 
	 
	 public function quickImportData()
	 {
	     return;
	     $data = $this->extractData();
	     foreach($data as $d){
	         $d = $this->renderData($d);
	         
	         $item = (new \yii\db\Query())->from($this->getModel()->tableName())->where(['lang_code' => $d['lang_code']])->one();
	         if(!empty($item)){
	             //$this->updateData($item['id'],$d);
	             if($item['level'] == 1){
	                 view($this->validateData($d,$item['id']));
	                 view($d);
	             }
	         }else{
	             //$this->insertData($d);
	         }
	     }
	 }
	 
	 
	 public function extractData()
	 {
	     $text = '<table class="wikitable">

<tbody><tr>
<th width="1%">Stt
</th>
<th width="16%">Tên cửa khẩu
</th>
<th width="10%">Cấp
</th>
<th width="12%">Loại
</th>
<th width="14%">Huyện, Quận, Thị xã
</th>
<th width="12%">Tỉnh, Thành
</th>
<th width="14%">Tới cửa khẩu
</th>
<th width="12%">Quốc gia
</th>
<th>Khu kinh tế
</th></tr>
<tr>
<td><center> 01
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_N%E1%BB%99i_B%C3%A0i" title="Sân bay quốc tế Nội Bài">Nội Bài</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/S%C3%B3c_S%C6%A1n" title="Sóc Sơn">Sóc Sơn</a>
</center></td>
<td><center> <a href="/wiki/H%C3%A0_N%E1%BB%99i" title="Hà Nội">Hà Nội</a>
</center></td>
<td><center> Nhiều Quốc gia
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 02
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_C%C3%A1t_Bi" class="mw-redirect" title="Sân bay quốc tế Cát Bi">Cát Bi</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/H%E1%BA%A3i_An,_H%E1%BA%A3i_Ph%C3%B2ng" class="mw-redirect" title="Hải An, Hải Phòng">Hải An</a>
</center></td>
<td><center> <a href="/wiki/H%E1%BA%A3i_Ph%C3%B2ng" title="Hải Phòng">Hải Phòng</a>
</center></td>
<td><center> nt
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 03
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_V%C3%A2n_%C4%90%E1%BB%93n" title="Sân bay quốc tế Vân Đồn">Vân Đồn</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/V%C3%A2n_%C4%90%E1%BB%93n" title="Vân Đồn">Vân Đồn</a>
</center></td>
<td><center> <a href="/wiki/Qu%E1%BA%A3ng_Ninh" title="Quảng Ninh">Quảng Ninh</a>
</center></td>
<td><center> nt
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 04
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_M%C3%B3ng_C%C3%A1i" title="Cửa khẩu Móng Cái">Móng Cái</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/wiki/M%C3%B3ng_C%C3%A1i" title="Móng Cái">Móng Cái</a>
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/%C4%90%C3%B4ng_H%C6%B0ng_(Qu%E1%BA%A3ng_T%C3%A2y)" class="mw-redirect" title="Đông Hưng (Quảng Tây)">Đông Hưng</a>
</center></td>
<td><center> Trung Quốc
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 05
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Ho%C3%A0nh_M%C3%B4" title="Cửa khẩu Hoành Mô">Hoành Mô</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/B%C3%ACnh_Li%C3%AAu" title="Bình Liêu">Bình Liêu</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Động Trung
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 06
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_B%E1%BA%AFc_Phong_Sinh" title="Cửa khẩu Bắc Phong Sinh">Bắc Phong Sinh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%E1%BA%A3i_H%C3%A0" title="Hải Hà">Hải Hà</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Lý Hỏa
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 07
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Chi_Ma" title="Cửa khẩu Chi Ma">Chi Ma</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/L%E1%BB%99c_B%C3%ACnh" title="Lộc Bình">Lộc Bình</a>
</center></td>
<td><center> <a href="/wiki/L%E1%BA%A1ng_S%C6%A1n" title="Lạng Sơn">Lạng Sơn</a>
</center></td>
<td><center> Ái Điểm
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 08
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Co_S%C3%A2u" title="Cửa khẩu Co Sâu">Co Sâu</a>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Cao_L%E1%BB%99c" title="Cao Lộc">Cao Lộc</a>
</center></td>
<td><center> nt
</center></td>
<td><center>
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 09
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_P%C3%B2_Nh%C3%B9ng" title="Cửa khẩu Pò Nhùng">Pò Nhùng</a><sup id="cite_ref-6" class="reference"><a href="#cite_note-6">[5]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 10
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Qu%E1%BB%91c_t%E1%BA%BF_H%E1%BB%AFu_Ngh%E1%BB%8B" title="Cửa khẩu Quốc tế Hữu Nghị">Hữu Nghị</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/B%E1%BA%B1ng_T%C6%B0%E1%BB%9Dng" title="Bằng Tường">Bằng Tường</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 11
</center></td>
<td><center> <a href="/wiki/Ga_%C4%90%E1%BB%93ng_%C4%90%C4%83ng" title="Ga Đồng Đăng">Đồng Đăng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Đường sắt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 12
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_C%E1%BB%91c_Nam" title="Cửa khẩu Cốc Nam">Cốc Nam</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/wiki/V%C4%83n_L%C3%A3ng" title="Văn Lãng">Văn Lãng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Lộng Hoài
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 13
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%C3%A2n_Thanh" title="Cửa khẩu Tân Thanh">Tân Thanh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Pu Zhai<br>(Pò Chài)
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 14
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_B%C3%ACnh_Nghi" title="Cửa khẩu Bình Nghi">Bình Nghi</a>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Tr%C3%A0ng_%C4%90%E1%BB%8Bnh" title="Tràng Định">Tràng Định</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Bình Nhi Quan
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 15
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_N%C3%A0_N%C6%B0a&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Nà Nưa (trang chưa được viết)">Nà Nưa</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 16
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_L%C3%BD_V%E1%BA%A1n" title="Cửa khẩu Lý Vạn">Lý Vạn</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%E1%BA%A1_Lang" title="Hạ Lang">Hạ Lang</a>
</center></td>
<td><center> <a href="/wiki/Cao_B%E1%BA%B1ng" title="Cao Bằng">Cao Bằng</a>
</center></td>
<td><center> Thạc Long
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 17
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_H%E1%BA%A1_Lang" title="Cửa khẩu Hạ Lang">Hạ Lang</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Khoa Giáp
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 18
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%C3%A0_L%C3%B9ng" title="Cửa khẩu Tà Lùng">Tà Lùng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Ph%E1%BB%A5c_H%C3%B2a" title="Phục Hòa">Phục Hòa</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Thủy Khẩu
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 19
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_P%C3%B2_Peo" title="Cửa khẩu Pò Peo">Pò Peo</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Tr%C3%B9ng_Kh%C3%A1nh_(huy%E1%BB%87n)" title="Trùng Khánh (huyện)">Trùng Khánh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Nhạc Vu
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 20
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Tr%C3%A0_L%C4%A9nh" title="Cửa khẩu Trà Lĩnh">Trà Lĩnh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Tr%C3%A0_L%C4%A9nh" title="Trà Lĩnh">Trà Lĩnh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Long Bang
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 21
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_S%C3%B3c_Giang" title="Cửa khẩu Sóc Giang">Sóc Giang</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%C3%A0_Qu%E1%BA%A3ng" title="Hà Quảng">Hà Quảng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Bình Mãng
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 22
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_S%C4%83m_Pun" title="Cửa khẩu Săm Pun">Săm Pun</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/M%C3%A8o_V%E1%BA%A1c" title="Mèo Vạc">Mèo Vạc</a>
</center></td>
<td><center> <a href="/wiki/H%C3%A0_Giang" title="Hà Giang">Hà Giang</a>
</center></td>
<td><center> Thanh Long Vàng
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 23
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Ph%C3%B3_B%E1%BA%A3ng" title="Cửa khẩu Phó Bảng">Phó Bảng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/%C4%90%E1%BB%93ng_V%C4%83n" title="Đồng Văn">Đồng Văn</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Cheng Sung Song
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 24
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Thanh_Th%E1%BB%A7y" class="mw-redirect" title="Cửa khẩu Thanh Thủy">Thanh Thủy</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/V%E1%BB%8B_Xuy%C3%AAn" title="Vị Xuyên">Vị Xuyên</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Thiên Bảo
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 25
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_X%C3%ADn_M%E1%BA%A7n" title="Cửa khẩu Xín Mần">Xín Mần</a><br>(Long Tuyền)
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/X%C3%ADn_M%E1%BA%A7n" title="Xín Mần">Xín Mần</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Đô Long
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 26
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_M%C6%B0%E1%BB%9Dng_Kh%C6%B0%C6%A1ng" title="Cửa khẩu Mường Khương">Mường Khương</a><br>(Tung Chung Phố)
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/M%C6%B0%E1%BB%9Dng_Kh%C6%B0%C6%A1ng" title="Mường Khương">Mường Khương</a>
</center></td>
<td><center> <a href="/wiki/L%C3%A0o_Cai" title="Lào Cai">Lào Cai</a>
</center></td>
<td><center> Kiều Đầu
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 27
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_L%C3%A0o_Cai" title="Cửa khẩu Lào Cai">Lào Cai</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/L%C3%A0o_Cai_(th%C3%A0nh_ph%E1%BB%91)" title="Lào Cai (thành phố)">Tp Lào Cai</a>
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%C3%A0_Kh%E1%BA%A9u" class="mw-redirect mw-disambig" title="Hà Khẩu">Hà Khẩu</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 28
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_B%E1%BA%A3n_V%C6%B0%E1%BB%A3c" title="Cửa khẩu Bản Vược">Bản Vược</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/B%C3%A1t_X%C3%A1t" title="Bát Xát">Bát Xát</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Ba Sa
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 29
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Ma_L%C3%B9_Th%C3%A0ng" title="Cửa khẩu Ma Lù Thàng">Ma Lù Thàng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Phong_Th%E1%BB%95" title="Phong Thổ">Phong Thổ</a>
</center></td>
<td><center> <a href="/wiki/Lai_Ch%C3%A2u" title="Lai Châu">Lai Châu</a>
</center></td>
<td><center> Kim Thủy Hà
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 30
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_A_Pa_Ch%E1%BA%A3i" title="Cửa khẩu A Pa Chải">A Pa Chải</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/M%C6%B0%E1%BB%9Dng_Nh%C3%A9" title="Mường Nhé">Mường Nhé</a>
</center></td>
<td><center> <a href="/wiki/%C4%90i%E1%BB%87n_Bi%C3%AAn" title="Điện Biên">Điện Biên</a>
</center></td>
<td><center> Long Phú
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 31
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Si_Pa_Ph%C3%ACn" title="Cửa khẩu Si Pa Phìn">Si Pa Phìn</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Houay La<br>(Huổi Lả)
</center></td>
<td><center> Lào
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 32
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%C3%A2y_Trang" title="Cửa khẩu Tây Trang">Tây Trang</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center><a href="/wiki/%C4%90i%E1%BB%87n_Bi%C3%AAn_(huy%E1%BB%87n)" title="Điện Biên (huyện)">Điện Biên</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Sop Hun<br>(Sôp Hùn)
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 33
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Chi%E1%BB%81ng_Kh%C6%B0%C6%A1ng" title="Cửa khẩu Chiềng Khương">Chiềng Khương</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/S%C3%B4ng_M%C3%A3" title="Sông Mã">Sông Mã</a>
</center></td>
<td><center> <a href="/wiki/S%C6%A1n_La" title="Sơn La">Sơn La</a>
</center></td>
<td><center> Ban Dan<br>(Bản Đán)
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 34
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_N%C3%A0_C%C3%A0i" title="Cửa khẩu Nà Cài">Nà Cài</a>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Y%C3%AAn_Ch%C3%A2u" title="Yên Châu">Yên Châu</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Sop Dung<br>(Sốp Đung)
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 35
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_L%C3%B3ng_S%E1%BA%ADp" title="Cửa khẩu Lóng Sập">Lóng Sập</a><br>(Pa Háng)
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/M%E1%BB%99c_Ch%C3%A2u" title="Mộc Châu">Mộc Châu</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Pa Hang
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 36
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%C3%A9n_T%E1%BA%B1n" title="Cửa khẩu Tén Tằn">Tén Tằn</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/M%C6%B0%E1%BB%9Dng_L%C3%A1t" title="Mường Lát">Mường Lát</a>
</center></td>
<td><center> <a href="/wiki/Thanh_H%C3%B3a" title="Thanh Hóa">Thanh Hóa</a>
</center></td>
<td><center> Somvang<br>(Xôm Vẳng)
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 37
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Na_M%C3%A8o" title="Cửa khẩu Na Mèo">Na Mèo</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Quan_S%C6%A1n" title="Quan Sơn">Quan Sơn</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Namsoi<br>(Nậm Xôi)
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 38
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Kh%E1%BA%B9o" title="Cửa khẩu Khẹo">Khẹo</a><sup id="cite_ref-7" class="reference"><a href="#cite_note-7">[6]</a></sup>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Th%C6%B0%E1%BB%9Dng_Xu%C3%A2n" title="Thường Xuân">Thường Xuân</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Thalao<br>(Tha Lấu)
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 39
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_Vinh" title="Sân bay quốc tế Vinh">Vinh</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/Vinh" title="Vinh">Tp Vinh</a>
</center></td>
<td><center> <a href="/wiki/Ngh%E1%BB%87_An" title="Nghệ An">Nghệ An</a>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 40
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_N%E1%BA%ADm_C%E1%BA%AFn" title="Cửa khẩu Nậm Cắn">Nậm Cắn</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/wiki/K%E1%BB%B3_S%C6%A1n" class="mw-redirect mw-disambig" title="Kỳ Sơn">Kỳ Sơn</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Namkan<br>(Nậm Cắn)
</center></td>
<td><center> Lào
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 41
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Thanh_Th%E1%BB%A7y,_Ngh%E1%BB%87_An" title="Cửa khẩu Thanh Thủy, Nghệ An">Thanh Thủy</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Thanh_Ch%C6%B0%C6%A1ng" title="Thanh Chương">Thanh Chương</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Namon<br>(Nậm On)
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 42
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_qu%E1%BB%91c_t%E1%BA%BF_C%E1%BA%A7u_Treo" title="Cửa khẩu quốc tế Cầu Treo">Cầu Treo</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%C6%B0%C6%A1ng_S%C6%A1n" title="Hương Sơn">Hương Sơn</a>
</center></td>
<td><center> <a href="/wiki/H%C3%A0_T%C4%A9nh" title="Hà Tĩnh">Hà Tĩnh</a>
</center></td>
<td><center> Namphao<br>(Nậm Phao)
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 43
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Cha_Lo" title="Cửa khẩu Cha Lo">Cha Lo</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Minh_H%C3%B3a" title="Minh Hóa">Minh Hóa</a>
</center></td>
<td><center> <a href="/wiki/Qu%E1%BA%A3ng_B%C3%ACnh" title="Quảng Bình">Quảng Bình</a>
</center></td>
<td><center> Naphao<br>(Nà Phao)
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 44
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_C%C3%A0_Ro%C3%B2ng" title="Cửa khẩu Cà Roòng">Cà Roòng</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/B%E1%BB%91_Tr%E1%BA%A1ch" title="Bố Trạch">Bố Trạch</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Nong Ma
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 45
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Lao_B%E1%BA%A3o" class="mw-redirect" title="Cửa khẩu Lao Bảo">Lao Bảo</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%C6%B0%E1%BB%9Bng_H%C3%B3a" title="Hướng Hóa">Hướng Hóa</a>
</center></td>
<td><center> <a href="/wiki/Qu%E1%BA%A3ng_Tr%E1%BB%8B" title="Quảng Trị">Quảng Trị</a>
</center></td>
<td><center> Den Savanh
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 46
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_La_Lay" title="Cửa khẩu La Lay">La Lay</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/%C4%90akr%C3%B4ng" title="Đakrông">Đa Krông</a>
</center></td>
<td><center>
</center></td>
<td><center> La Lay
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 47
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_H%E1%BB%93ng_V%C3%A2n" title="Cửa khẩu Hồng Vân">Hồng Vân</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/A_L%C6%B0%E1%BB%9Bi" title="A Lưới">A Lưới</a>
</center></td>
<td><center> <a href="/wiki/Th%E1%BB%ABa_Thi%C3%AAn_-_Hu%E1%BA%BF" title="Thừa Thiên - Huế">Thừa Thiên-Huế</a>
</center></td>
<td><center> Kutai
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 48
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_A_%C4%90%E1%BB%9Bt" title="Cửa khẩu A Đớt">A Đớt</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Tavang<br>(Tà Vàng)
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 49
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_Ph%C3%BA_B%C3%A0i" title="Sân bay quốc tế Phú Bài">Phú Bài</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/H%C6%B0%C6%A1ng_Th%E1%BB%A7y" title="Hương Thủy">Hương Thủy</a>
</center></td>
<td><center> nt
</center></td>
<td><center>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 50
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_%C4%90%C3%A0_N%E1%BA%B5ng" title="Sân bay quốc tế Đà Nẵng">Đà Nẵng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%E1%BA%A3i_Ch%C3%A2u,_%C4%90%C3%A0_N%E1%BA%B5ng" class="mw-redirect" title="Hải Châu, Đà Nẵng">Q.Hải Châu</a>
</center></td>
<td><center> <a href="/wiki/%C4%90%C3%A0_N%E1%BA%B5ng" title="Đà Nẵng">Đà Nẵng</a>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 51
</center></td>
<td><center> <a href="/wiki/Ch%27%C6%A0m" title="Ch\'Ơm">Ch\'Ơm</a>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/wiki/T%C3%A2y_Giang" title="Tây Giang">Tây Giang</a>
</center></td>
<td><center> <a href="/wiki/Qu%E1%BA%A3ng_Nam" title="Quảng Nam">Quảng Nam</a>
</center></td>
<td><center> Kaleum<br>(Kà Lừm)
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 52
</center></td>
<td><center> <a href="/wiki/La_D%C3%AA%C3%AA" title="La Dêê">La Dêê</a><br>(Đăk Ôc)
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Nam_Giang" title="Nam Giang">Nam Giang</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Dak Ta Ook
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 53
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_B%E1%BB%9D_Y" title="Cửa khẩu Bờ Y">Bờ Y</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Ng%E1%BB%8Dc_H%E1%BB%93i" title="Ngọc Hồi">Ngọc Hồi</a>
</center></td>
<td><center> <a href="/wiki/Kon_Tum" title="Kon Tum">Kon Tum</a>
</center></td>
<td><center> Phou Keua<br>(Phù Kưa)
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 54
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_%C4%90%C4%83k_K%C3%B4i" title="Cửa khẩu Đăk Kôi">Đăk Kôi</a><sup id="cite_ref-qd1490@2013_8-0" class="reference"><a href="#cite_note-qd1490@2013-8">[7]</a></sup>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Kon Tuy Neak
</center></td>
<td><center> Campuchia
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 55
</center></td>
<td><center> <a href="/wiki/%C4%90%C4%83k_Ru%C3%AA" title="Đăk Ruê">Đăk Ruê</a><sup id="cite_ref-qd1490@2013_8-1" class="reference"><a href="#cite_note-qd1490@2013-8">[7]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Ea_S%C3%BAp" title="Ea Súp">Ea Súp</a>
</center></td>
<td><center> <a href="/wiki/%C4%90%C4%83k_L%C4%83k" class="mw-redirect" title="Đăk Lăk">Đăk Lăk</a>
</center></td>
<td><center> Chi Mian
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 56
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_L%E1%BB%87_Thanh" title="Cửa khẩu Lệ Thanh">Lệ Thanh</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/%C4%90%E1%BB%A9c_C%C6%A1" title="Đức Cơ">Đức Cơ</a>
</center></td>
<td><center> <a href="/wiki/Gia_Lai" title="Gia Lai">Gia Lai</a>
</center></td>
<td><center> O\'Yadaw <sup id="cite_ref-cambodia-tourism_9-0" class="reference"><a href="#cite_note-cambodia-tourism-9">[8]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 57
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_Cam_Ranh" title="Sân bay quốc tế Cam Ranh">Cảng HK Cam Ranh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/Cam_Ranh" title="Cam Ranh">Tp Cam Ranh</a>
</center></td>
<td><center> <a href="/wiki/Kh%C3%A1nh_H%C3%B2a" title="Khánh Hòa">Khánh Hòa</a>
</center></td>
<td>
</td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 58
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_%C4%90%C4%83k_Peur" title="Cửa khẩu Đăk Peur">Đăk Peur</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/wiki/%C4%90%C4%83k_Mil" class="mw-redirect" title="Đăk Mil">Đăk Mil</a>
</center></td>
<td><center> <a href="/wiki/%C4%90%C4%83k_N%C3%B4ng" class="mw-redirect" title="Đăk Nông">Đăk Nông</a>
</center></td>
<td><center> Nam Lieou
</center></td>
<td><center> Campuchia
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 59
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Bu_Pr%C4%83ng" title="Cửa khẩu Bu Prăng">Bu Prăng</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Tuy_%C4%90%E1%BB%A9c" title="Tuy Đức">Tuy Đức</a>
</center></td>
<td><center> nt
</center></td>
<td><center> O Raing
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 60
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Hoa_L%C6%B0" class="mw-redirect" title="Cửa khẩu Hoa Lư">Hoa Lư</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/L%E1%BB%99c_Ninh" title="Lộc Ninh">Lộc Ninh</a>
</center></td>
<td><center> <a href="/wiki/B%C3%ACnh_Ph%C6%B0%E1%BB%9Bc" title="Bình Phước">Bình Phước</a>
</center></td>
<td><center> Trapeang Srer
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 61
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Ho%C3%A0ng_Di%E1%BB%87u" title="Cửa khẩu Hoàng Diệu">Hoàng Diệu</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/B%C3%B9_%C4%90%E1%BB%91p" title="Bù Đốp">Bù Đốp</a>
</center></td>
<td><center>
</center></td>
<td><center> Lapakhe
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 62
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_T%C3%A2n_Ti%E1%BA%BFn&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Tân Tiến (trang chưa được viết)">Tân Tiến</a><sup id="cite_ref-10" class="reference"><a href="#cite_note-10">[9]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Chay Kh’Leng
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 63
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_L%E1%BB%99c_Th%E1%BB%8Bnh&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Lộc Thịnh (trang chưa được viết)">Lộc Thịnh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/L%E1%BB%99c_Ninh" title="Lộc Ninh">Lộc Ninh</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Tonle Chàm
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 64
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Xa_M%C3%A1t" class="mw-redirect" title="Cửa khẩu Xa Mát">Xa Mát</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/T%C3%A2n_Bi%C3%AAn" title="Tân Biên">Tân Biên</a>
</center></td>
<td><center> <a href="/wiki/T%C3%A2y_Ninh" title="Tây Ninh">Tây Ninh</a>
</center></td>
<td><center> Trapeang Phlong
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 65
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Qu%E1%BB%91c_t%E1%BA%BF_M%E1%BB%99c_B%C3%A0i" title="Cửa khẩu Quốc tế Mộc Bài">Mộc Bài</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/B%E1%BA%BFn_C%E1%BA%A7u" title="Bến Cầu">Bến Cầu</a>
</center></td>
<td><center> <a href="/wiki/T%C3%A2y_Ninh" title="Tây Ninh">Tây Ninh</a>
</center></td>
<td><center> Bavet<sup id="cite_ref-cambodia-tourism_9-1" class="reference"><a href="#cite_note-cambodia-tourism-9">[8]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 66
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_K%C3%A0_Tum" title="Cửa khẩu Kà Tum">Kà Tum</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center>
</center></td>
<td><center> <a href="/wiki/T%C3%A2n_Ch%C3%A2u_(huy%E1%BB%87n)" title="Tân Châu (huyện)">Tân Châu</a>
</center></td>
<td><center>
</center></td>
<td><center> Chan Moul
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 67
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%E1%BB%91ng_L%C3%AA_Ch%C3%A2n" title="Cửa khẩu Tống Lê Chân">Tống Lê Chân</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Sa Tum
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 68
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_V%E1%BA%A1c_Sa&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Vạc Sa (trang chưa được viết)">Vạc Sa</a><sup id="cite_ref-tn-hq14_11-0" class="reference"><a href="#cite_note-tn-hq14-11">[10]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td>
</td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 69
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%C3%A2n_Nam" title="Cửa khẩu Tân Nam">Tân Nam</a><sup id="cite_ref-Cktn-Baotayninh_12-0" class="reference"><a href="#cite_note-Cktn-Baotayninh-12">[11]</a></sup>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/T%C3%A2n_Bi%C3%AAn" title="Tân Biên">Tân Biên</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Meanchey
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 70
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_Ch%C3%A0ng_Ri%E1%BB%87c&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Chàng Riệc (trang chưa được viết)">Chàng Riệc</a><sup id="cite_ref-13" class="reference"><a href="#cite_note-13">[12]</a></sup>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Da<br><a href="/wiki/Tbong_Khmum" title="Tbong Khmum">Tbong Khmum</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 71
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Ph%C6%B0%E1%BB%9Bc_T%C3%A2n" title="Cửa khẩu Phước Tân">Phước Tân</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><a href="/wiki/Ch%C3%A2u_Th%C3%A0nh,_T%C3%A2y_Ninh" title="Châu Thành, Tây Ninh">Châu Thành</a>
</td>
<td><center> nt
</center></td>
<td><center> Bos Mon
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 72
</center></td>
<td><center> <a href="/wiki/S%C3%A2n_bay_qu%E1%BB%91c_t%E1%BA%BF_T%C3%A2n_S%C6%A1n_Nh%E1%BA%A5t" title="Sân bay quốc tế Tân Sơn Nhất">Tân Sơn Nhất</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Hàng không
</center></td>
<td><center> <a href="/wiki/T%C3%A2n_B%C3%ACnh" title="Tân Bình">Tân Bình</a>
</center></td>
<td><center> <a href="/wiki/Th%C3%A0nh_ph%E1%BB%91_H%E1%BB%93_Ch%C3%AD_Minh" title="Thành phố Hồ Chí Minh">Tp Hồ Chí Minh</a>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td>
<td><center>
</center></td></tr>
<tr>
<td><center> 73
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_B%C3%ACnh_Hi%E1%BB%87p" title="Cửa khẩu Bình Hiệp">Bình Hiệp</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/wiki/Ki%E1%BA%BFn_T%C6%B0%E1%BB%9Dng" title="Kiến Tường">Kiến Tường</a>
</center></td>
<td><center> <a href="/wiki/Long_An" title="Long An">Long An</a>
</center></td>
<td><center> Prey Voir
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 74
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_M%E1%BB%B9_Qu%C3%BD_T%C3%A2y" title="Cửa khẩu Mỹ Quý Tây">Mỹ Quý Tây</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/%C4%90%E1%BB%A9c_Hu%E1%BB%87" title="Đức Huệ">Đức Huệ</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Sam Reong
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 75
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_H%C6%B0ng_%C4%90i%E1%BB%81n&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Hưng Điền (trang chưa được viết)">Hưng Điền</a>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/V%C4%A9nh_H%C6%B0ng" title="Vĩnh Hưng">Vĩnh Hưng</a>
</center></td>
<td><center> nt
</center></td>
<td>
</td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 76
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_Th%C3%B4ng_B%C3%ACnh&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Thông Bình (trang chưa được viết)">Thông Bình</a><sup id="cite_ref-qd1580ttg_14-0" class="reference"><a href="#cite_note-qd1580ttg-14">[13]</a></sup>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center>
</center></td>
<td><center> <a href="/wiki/T%C3%A2n_H%E1%BB%93ng" title="Tân Hồng">Tân Hồng</a>
</center></td>
<td><center> <a href="/wiki/%C4%90%E1%BB%93ng_Th%C3%A1p" title="Đồng Tháp">Đồng Tháp</a>
</center></td>
<td>
</td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 77
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Dinh_B%C3%A0" title="Cửa khẩu Dinh Bà">Dinh Bà</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Banteay Chakrey
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 78
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_S%E1%BB%9F_Th%C6%B0%E1%BB%A3ng&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Sở Thượng (trang chưa được viết)">Sở Thượng</a><sup id="cite_ref-qd1580ttg_14-1" class="reference"><a href="#cite_note-qd1580ttg-14">[13]</a></sup>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%E1%BB%93ng_Ng%E1%BB%B1_(huy%E1%BB%87n)" title="Hồng Ngự (huyện)">Hồng Ngự</a>
</center></td>
<td><center> nt
</center></td>
<td>
</td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 79
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Th%C6%B0%E1%BB%9Dng_Ph%C6%B0%E1%BB%9Bc" title="Cửa khẩu Thường Phước">Thường Phước</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> Đường sông
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Kaoh Roka<sup id="cite_ref-ckvncpc-dlpn_15-0" class="reference"><a href="#cite_note-ckvncpc-dlpn-15">[14]</a></sup>
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 80
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_V%C4%A9nh_X%C6%B0%C6%A1ng" title="Cửa khẩu Vĩnh Xương">Vĩnh Xương</a>
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/T%C3%A2n_Ch%C3%A2u_(th%E1%BB%8B_x%C3%A3)" title="Tân Châu (thị xã)">Tân Châu</a>
</center></td>
<td><center> <a href="/wiki/An_Giang" title="An Giang">An Giang</a>
</center></td>
<td><center> Kaam Samnor
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 81
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_Kh%C3%A1nh_B%C3%ACnh&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Khánh Bình (trang chưa được viết)">Khánh Bình</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> Đường bộ
</center></td>
<td><center> <a href="/w/index.php?title=An_Ph%C3%BA_(huy%E1%BB%87n)&amp;action=edit&amp;redlink=1" class="new" title="An Phú (huyện) (trang chưa được viết)">An Phú</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Chrey Thom
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 82
</center></td>
<td><center> <a href="/w/index.php?title=C%E1%BB%ADa_kh%E1%BA%A9u_B%E1%BA%AFc_%C4%90%E1%BA%A1i&amp;action=edit&amp;redlink=1" class="new" title="Cửa khẩu Bắc Đại (trang chưa được viết)">Bắc Đại</a>
</center></td>
<td><center> Tỉnh
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td>
</td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 83
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_V%C4%A9nh_H%E1%BB%99i_%C4%90%C3%B4ng" title="Cửa khẩu Vĩnh Hội Đông">Vĩnh Hội Đông</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td>
<td><center> Kompong Krosang
</center></td>
<td><center> nt
</center></td>
<td><center> nt
</center></td></tr>
<tr>
<td><center> 84
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_T%E1%BB%8Bnh_Bi%C3%AAn" title="Cửa khẩu Tịnh Biên">Tịnh Biên</a>
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/T%E1%BB%8Bnh_Bi%C3%AAn" title="Tịnh Biên">Tịnh Biên</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Phnom Den
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
<tr>
<td><center> 85
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_Giang_Th%C3%A0nh" title="Cửa khẩu Giang Thành">Giang Thành</a>
</center></td>
<td><center> Quốc gia
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/Giang_Th%C3%A0nh" title="Giang Thành">Giang Thành</a>
</center></td>
<td><center> <a href="/wiki/Ki%C3%AAn_Giang" title="Kiên Giang">Kiên Giang</a>
</center></td>
<td><center> Ton Hon
</center></td>
<td><center> nt
</center></td>
<td><center> Không
</center></td></tr>
<tr>
<td><center> 86
</center></td>
<td><center> <a href="/wiki/C%E1%BB%ADa_kh%E1%BA%A9u_H%C3%A0_Ti%C3%AAn" title="Cửa khẩu Hà Tiên">Hà Tiên</a><br>(Xà Xía)
</center></td>
<td><center> Quốc tế
</center></td>
<td><center> nt
</center></td>
<td><center> <a href="/wiki/H%C3%A0_Ti%C3%AAn" title="Hà Tiên">Hà Tiên</a>
</center></td>
<td><center> nt
</center></td>
<td><center> Prek Chak
</center></td>
<td><center> nt
</center></td>
<td><center> Có
</center></td></tr>
</tbody></table>';
	     
	     
	     
	     require_once Yii::getAlias('@app') . "/components/helpers/simple_html_dom.php";
	     
	     $html = @str_get_html($text);
	     
	     $data = [];
	     
	     foreach ($html->find('tr') as $k=>$tr){
	         if($k == 0) continue;
	         $p['name'] = trim_space(preg_replace('/(\[.*\])|\(.*\)/', '',  ($tr->find('td',1)->plaintext)));
	         
	         $level = trim_space($tr->find('td',2)->plaintext);
	         
	         switch ($level){
	             case 'Quốc tế':
	                 $level = 1;
	                 break;
	             case 'Quốc gia':
	                 $level = 2;
	                 break;
	                 
	             case 'Tỉnh':
	                 $level = 3;
	                 break;
	             default:
	                 $level = $last_lv;
	                 break;
	         }
	         
	         $last_lv = $level;
	         
	         $p['level'] = $level;
	         
	         // Loại
	         $type_id = trim_space($tr->find('td',3)->plaintext);
	         switch ($type_id){
	             case 'Hàng không':
	                 $type_id = 1;
	                 break;
	             case 'Đường bộ':
	                 $type_id = 2;
	                 break;
	                 
	             case 'Đường sắt':
	                 $type_id = 3;
	                 break;
	             default:
	                 $type_id = $last_type_id;
	                 break;
	         }
	         
	         $last_type_id = $type_id;
	         
	         $p['type_id'] = $type_id;
	         
	         // To port
	         $to_port = trim_space($tr->find('td',6)->plaintext);
	         switch ($to_port){
	             
	             case 'nt':
	                 $to_port = $last_to_port;
	                 break;
	         }
	         
	         $last_to_port = $to_port;
	         
	         
	         $to_port2 = trim_space($tr->find('td',7)->plaintext);
	         switch ($to_port2){
	             
	             case 'nt':
	                 $to_port2 = $last_to_port2;
	                 break;
	         }
	         
	         $last_to_port2 = $to_port2;
	         
	         if($to_port2 != ""){
	             $to_port = $to_port != "" ? "$to_port, $to_port2" : $to_port2;
	         }
	         
	         //
	         
	         $p['to_port'] = $to_port;
	         
	         
	         
	         
	         $data[] = $p;
	     }
	     
	     
	     
	     
	     
	     return $data;
	 }
	 
}