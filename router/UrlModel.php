<?php
namespace izi\router;

use Yii;
use izi\models\DomainPointer;
use izi\models\Shops;
use izi\models\SiteMenu;

class UrlModel extends \izi\models\Slugs
{

    /**
     * DB init
     */
    public static function getDomainInfo($domain = __DOMAIN__){

        /**
        * Set cache param
        */
        $params = [
            __CLASS__,
            __FUNCTION__,
            $domain,
            date('H')
        ];

        $config = Yii::$app->icache->getCache($params);

        if(!YII_DEBUG && !empty($config)){
            return $config;
        }else{
            $config = static::find()
            ->select([
                'a.sid',
                'a.is_invisible',
                'b.status',
                'b.code',
                'a.is_admin',
                'a.module',
                'a.layout',
                'a.temp_id',
                'a.lang',
                'a.domain',
            ])
            ->from(['a'=>DomainPointer::tableName()])
            ->innerJoin(['b'=>Shops::tableName()],'a.sid=b.id')
            ->where(['a.domain'=>__DOMAIN__])->asArray()->one();
            Yii::$app->icache->store($config, $params);

            return $config;
        }
    }


    public static function findUrl($url = ''){

        $v = static::find()->where(['url'=>$url,'sid'=>__SID__])->asArray()->one();

        return self::populateData($v);


        //         if(isset($v['bizrule']) && ($content = json_decode($v['bizrule'], 1)) != false){
        //             $v += $content;
        //             unset($v['bizrule']);
        //         }

        //         return $v;
    }


    public function findUrls()
    {
        $query = static::find()->where(['sid'=>__SID__])->asArray()->all();
        return UrlModel::populateData($query);
    }


    public function getCategoryDetail($item_id){

        $item = static::find()
        ->from(SiteMenu::tableName())
        ->where([
            "id" => $item_id ,
            'is_active'=>1 ,
            'sid'=>__SID__

        ])->asArray()->one();

        return $this->populateData($item);

        //         if(!empty($item)) {
        //             if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
        //                 $item += $content;
        //                 unset($item['bizrule']);
        //             }
        //             return $item;
        //         }
    }

    public function getRootCategoryDetail($item = []){
        if(is_numeric($item)){
            $item = $this->getCategoryDetail($item);
        }

        if(!empty($item)){

            if(isset($item['parent_id']) && $item['parent_id'] == 0){
                return $item;
            }else{

                $item = static::find()
                ->from(SiteMenu::tableName())
                ->where(['and',[
                    "parent_id" => 0,
                    'is_active'=>1 ,
                    'sid'=>__SID__
                ],
                    ['<', 'lft', $item['lft']],
                    ['>', 'rgt', $item['rgt']],
                ])->asArray()->one();

                return $this->populateData($item);

                //                 if(!empty($item)) {
                //                     if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
                //                         $item += $content;
                //                         unset($item['bizrule']);
                //                     }
                //                     return $item;
                //                 }
            }

        }
    }



    public function getItemDetail($item_id){

        $item = static::find()
        ->from('{{%articles}}')
        ->where([
            "id" => $item_id ,
            'is_active'=>1 ,
            'sid'=>__SID__

        ])->asArray()->one();


        $item = $this->populateData($item);

        if(!empty($item)){

            // //
            // if(!isset($item['list_images']) && isset($item['listImages'])){
            //     $item['list_images'] = $item['listImages'];
            //     //                 unset($item['listImages']);
            // }

            // switch ($item['type']) {
            //     case 'text':
            //         break;

            //     default:
            //         $item['tabs'] = $this->populateData(static::find()
            //         ->from('{{%tab_details}}')
            //         ->where([
            //         "item_id" => $item_id ,
            //         //                     'is_active'=>1 ,
            //         //                     'sid'=>__SID__

            //         ])->asArray()->all());
            //         break;
            // }

            // switch ($item['type']) {
            //     case 'tours':
            //         if(!empty($tours_attrs = static::find()
            //         ->from('{{%tours_attrs}}')
            //         ->where([
            //         "item_id" => $item_id ,
            //         //                     'is_active'=>1 ,
            //         //                     'sid'=>__SID__

            //         ])->asArray()->one())){


            //             $item += $tours_attrs;

            //         }
            //         break;
            // }
        }

        return $item;

        //         if(!empty($item)) {

        //             if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
        //                 $item += $content;
        //                 unset($item['bizrule']);
        //             }
        //             return $item;
        //         }
    }


    public function getItemCategory($item_id){


        $item = static::find()
        ->from(['a'=>'{{%site_menu}}'])
        ->innerJoin(['b'=>'{{%items_to_category}}'],'a.id=b.category_id' )
        ->where([
            "b.item_id" => $item_id
        ])->asArray()->one();

        return $this->populateData($item);

        //         if(!empty($item)){
        //             if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
        //                 $item += $content;
        //                 unset($item['bizrule']);
        //             }

        //             return $item;
        //         }

    }


    public function getBoxDetail($item_id){

        $item = static::find()
        ->from('{{%box}}')
        ->where([
            "id" => $item_id ,
            'is_active'=>1 ,
            'sid'=>__SID__

        ])->asArray()->one();

        return $this->populateData($item);
        //         if(!empty($item)) {

        //             if(isset($item['bizrule']) && ($content = json_decode($item['bizrule'],1)) != NULL){
        //                 $item += $content;
        //                 unset($item['bizrule']);
        //             }
        //             return $item;
        //         }
    }


    public function getTemplate()
    {


        if(in_array(__SID__, [-1,0])){
            return [
                'id'=>0,
                'name'=>'welcome',
                'code'=>'welcome',
                'parent_id'=>0,
                'is_mobile'=>0,
            ];
        }

        $item = [];
        /**
        Lấy template id theo cấp độ ưu tiên
        1. Cấp Chi tiết: sd trong trường hợp làm landing page cho 1 sản phẩm cụ thể
        - Phương án này có thể được thay thế bằng cách tạo file layout trong theme hiện tại

        2. Cấp Danh mục: sd trong trường hợp làm landing page cho danh mục sản phẩm cụ thể,
        hoặc muốn làm giao diện riêng cho tất cả sp thuộc dm đó

        3. Cấp domain: sd trong trường hợp site có nhiều domain trỏ về, muốn hiển thị khác nhau trên mỗi domain

        4. Mặc định hệ thống: hoạt động khi các điều kiên (1) (2) (3) không được thỏa mãn
         */

        if(defined('PRIVATE_TEMPLATE') && PRIVATE_TEMPLATE>0){
            $private_template = PRIVATE_TEMPLATE;
        }elseif(defined('CATEGORY_TEMPLATE') && CATEGORY_TEMPLATE>0){
            $private_template = CATEGORY_TEMPLATE;
        }elseif(defined('DOMAIN_TEMPLATE') && DOMAIN_TEMPLATE>0){
            $private_template = DOMAIN_TEMPLATE;
        }else{
            $private_template = 0;
        }


        $params = [
            __METHOD__,
            __FILE__,
            __DOMAIN__,
            __LANG__,
            defined('CATEGORY_TEMPLATE') ?: false ,
            $private_template
        ];


        $cached = Yii::$app->icache->getCache($params);

        if(!YII_DEBUG && !empty($cached)){
            return $cached;
        }

        $templateTable = \izi\models\Templates::tableName();

        if($private_template>0){
            $item = UrlModel::find()->from(['a' => $templateTable ])->where(["id" => $private_template])->asArray()->one();
        }

        /**
        Lấy template mặc định
         */
        if(empty($item)){

            $query = UrlModel::find()
            ->select(['a.*'])
            ->from(['a' => $templateTable ])
            ->innerJoin(['b' => '{{%temp_to_shop}}'], "a.id=b.temp_id");


            /**
            (1) Tìm template thỏa mãn điều kiện [state, lang]
            */
            $item = $query
            ->where(
                [
                    'b.state'=>__TEMPLATE_DOMAIN_STATUS__,
                    'b.sid'=>__SID__,
                    'b.lang'=>__LANG__,
                ])
                ->asArray()
                ->one();


                if(empty($item)){
                    /**
                    Tìm template thỏa mãn điều kiện [state] (đk 1 loại bỏ lang)
                    */
                    $item = $query
                    ->where(
                        [
                            'b.state'=>__TEMPLATE_DOMAIN_STATUS__,
                            'b.sid'=>__SID__
                        ])
                        ->asArray()
                        ->one();


                        /**
                        Nếu vẫn chưa tìm thấy temp phù hợp sẽ tìm đến temp mặc định
                        */

                        if(empty($item) && __TEMPLATE_DOMAIN_STATUS__ > 1){

                            $item = $query
                            ->where(
                                [
                                    'b.state'=>1,
                                    'b.sid'=>__SID__,
                                    'b.lang'=>__LANG__,
                                ])
                                ->asArray()
                                ->one();

                                if(empty($item)){

                                    $item = $query
                                    ->where(
                                        [
                                            'b.state'=>1,
                                            'b.sid'=>__SID__,
                                        ])
                                        ->asArray()
                                        ->one();

                                }
                        }
                }


        }

        if(!empty($item) && $item['parent_id'] > 0){
            $parent = UrlModel::find()
            ->select(['a.*'])
            ->from(['a' => \izi\models\TemplateCategory::tableName()])
            ->where(['id' => $item['parent_id']])->asArray()->one();
            $item['category'] = $parent;
        }

        Yii::$app->icache->store($item, $params);

        return $this->populateData($item);
    }


}
