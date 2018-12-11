# yii2-app
### Sơ lược về izisoft/yii2
Tùy chỉnh bộ định tuyến Yii2 theo mô hình url 1 (hoặc nhiều) cấp

	-> https://iziweb.vn/kho-giao-dien
	
	<=>

	[
	controller: site,
	action: news
	id: kho-giao-dien
	]

### Cài đặt
composer require --prefer-dist izisoft/yii2 "dev-master"

### Chức năng
-------------
* Chỉ định  controller / action thông qua url thân thiện (1 cấp hoặc nhiều cấp)
* Thiết lập ngôn ngữ từ url
* [Ext] Quản lý và cài đặt tiền tệ

* ... [còn nữa cơ mà lười viết]
### Hướng dẫn sử dụng
* Thêm đoạn code sau vào components
```php
	Đoạn này dài lắm, chưa viết được
  ```
* Tạo bảng slugs với thông tin cơ bản như sau:

	url: varchar -> Url trên thanh địa chỉ web

	route: varchar -> controller/action | action
	 
	(thêm các thông tin khác mà bạn cần khai thác)
* Tạo bảng currency, bảng language 

--- Updating --- 

	
Xem thêm các dự án viết bằng yii framework
-----

[Kho hàng US - Dịch vụ đặt hàng Mỹ số 1 Việt Nam](https://www.khohangus.com)

[Mỹ phẩm cao cấp Hàn Quốc Amaranth - Sorabee - Bello Vita](https://www.amaranth.com.vn)

[Chia sẻ kinh nghiệm lập trình php - vps - hosting](https://www.truongbui.com)

[Chia sẻ coupon khuyến mãi từ các trang thương mại điện tử hàng đầu tại Việt Nam và trên toàn thế giới](https://www.phutchot.com)

[EMZ - Mua gì cũng có](https://www.emz.vn)

[Thao Chip Shop, Chuyên bán buôn, bán lẻ đồ ngủ nữ](https://thaochip.com)

