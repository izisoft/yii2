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
use yii\db\Query;
class Izi extends \yii\base\Component
{

    /**
     * 
     * @param unknown $url
     * @return string|unknown
     */

    private $_airport;
    
    public function getAirport()
    {
        if($this->_airport == null){
            $this->_airport = Yii::createObject('izi\web\Airport');
        }
        return $this->_airport;
    }
    
    
    public function getUrl($url){
      if($url == ""){
        return ABSOLUTE_DOMAIN;
      }
        return \izi\models\Slug::getUrl($url);
    }

    public function getSchemeJsonLD(){

       /// view2(Yii::$app->cfg->contact,true);

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
            "name" => (get_site_value('seo/real_title',1,true)),
            "url" => ABSOLUTE_DOMAIN,
            "description" => get_site_value('seo/real_description',1,true),
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
                        "@id" => isset($v['url_link']) ? getAbsoluteUrl($v['url_link']) : ABSOLUTE_DOMAIN,
                        "name" => uh($v['title'])

                    ]
                ];
            }
            $webpage['breadcrumb'] = $breadcrumb;
        }
        $publisher = [
            "@type" => "Organization",
            "name" => Yii::$app->cfg->contact['short_name'],
            'url' => ABSOLUTE_DOMAIN,
            "logo" => [
                "@type" => "imageObject",
                "url" => $logoImage
            ]
        ];
        if(__IS_DETAIL__ && !empty(Yii::$app->item)){
            $detailImg = '';
            if(isset(Yii::$app->item['icon'])){
            $img = getImageInfo(getAbsoluteUrl(Yii::$app->item['icon']));


            $detailImg = isset($img[1]) && $img[1]> 0 ? ([
                "@type" => "imageObject",
                "url" => getAbsoluteUrl(Yii::$app->item['icon']),
                "height" => $img[1],
                "width" => $img[0]
            ]) : getAbsoluteUrl(Yii::$app->item['icon']);
            }

            $authName =  isset(Yii::$app->item['post_by_name']) && Yii::$app->item['post_by_name'] != "" ? Yii::$app->item['post_by_name'] : Yii::$app->user->getNameByUser(Yii::$app->item['created_by']);

            $mainEntity = [
                "@type" => "Article",
                "@id" => getAbsoluteUrl(Yii::$app->item['url_link']),
                "author" =>$authName != null ? $authName : Yii::$app->cfg->contact['short_name'],
                "datePublished" =>date('c',strtotime(Yii::$app->item['time'])),
                "dateModified" => date('c',strtotime(Yii::$app->item['updated_at'])),
                "mainEntityOfPage" => getAbsoluteUrl(__CATEGORY_URL__),
                "headline" => uh(Yii::$app->item['title']),
                "alternativeHeadline" => uh(isset(Yii::$app->item['info']) ? strip_tags(Yii::$app->item['info']) : ''),
                "name" => uh(Yii::$app->item['title']),
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
            "name" => Yii::$app->cfg->contact['short_name'],
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
            "addressCountry" => isset(Yii::$app->cfg->contact['addressCountry']) ? Yii::$app->cfg->contact['addressCountry'] : "Việt Nam",
            "addressLocality"=>isset(Yii::$app->cfg->contact['addressLocality']) ? Yii::$app->cfg->contact['addressLocality'] : "Hà Nội",
            "addressRegion" => isset(Yii::$app->cfg->contact['addressRegion']) ? Yii::$app->cfg->contact['addressRegion'] : "Thanh Xuân",
            "postalCode" => isset(Yii::$app->cfg->contact['postalCode']) ? Yii::$app->cfg->contact['postalCode'] : 100000,
            "streetAddress" => isset(Yii::$app->cfg->contact['streetAddress']) ?
            Yii::$app->cfg->contact['streetAddress'] :
            (isset(Yii::$app->cfg->contact['address']) ? Yii::$app->cfg->contact['address'] : '')
        ];

        $Organization = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            "@id" => ABSOLUTE_DOMAIN,
            "url" => ABSOLUTE_DOMAIN,
            "name" => Yii::$app->cfg->contact['short_name'],
            "description" => isset(Yii::$app->cfg->contact['description']) ? Yii::$app->cfg->contact['description'] : '',
            "sameAs" => $sameAs,
            "logo" => ($logoImage),
            "address" => $address
        ];
        if(isset(Yii::$app->cfg->contact['hotline']) && Yii::$app->cfg->contact['hotline'] != ''){
            $Organization['contactPoint'] = [
                "@type"=> "ContactPoint",
                "telephone"=> parsePhoneNumber(Yii::$app->cfg->contact['hotline']),
                "contactType"=> "customer service"
            ];
        }
        // END $Organization
        //$html .= '<script type="application/ld+json">' .(json_encode($Organization)) .'</script>';
        $jsonLD[] = $Organization;

        
        $breadcrumb["@context"] = "http://schema.org";

        if(!empty($breadcrumb['itemListElement'])){
            $jsonLD[] = $breadcrumb;
        }
        $geo = [];
        if(isset(Yii::$app->cfg->contact['latitude']) && Yii::$app->cfg->contact['latitude'] != "" && Yii::$app->cfg->contact['longitude'] != ""){
            $geo = [
                "@type" => "GeoCoordinates",
                "latitude" => Yii::$app->cfg->contact['latitude'],
                "longitude" => Yii::$app->cfg->contact['longitude']
            ];
        }
        // Json by page
        if(__IS_DETAIL__ && !empty(Yii::$app->item)){
            $text = '';
            if(isset(Yii::$app->item['ctab'])){
                foreach(Yii::$app->item['ctab'] as $d=>$t){
                    $text .= '<div class="box-details">'.uh($t['text'],2).'</div>';
                }}
                $authName =  isset(Yii::$app->item['post_by_name']) && Yii::$app->item['post_by_name'] != "" ? Yii::$app->item['post_by_name'] : Yii::$app->user->getNameByUser(Yii::$app->item['created_by']);

                $article = [
                    "@context" => "http://schema.org",
                    "@type" => "Article",
                    "headline" => uh(Yii::$app->item['title']),
                    "alternativeHeadline" => uh(isset(Yii::$app->item['info']) ? strip_tags(Yii::$app->item['info']) : ''),
                    "name" => uh(Yii::$app->item['title']),
                    "author" => [
                        "@type" => "Person",
                        "name" =>$authName != null ? $authName : Yii::$app->cfg->contact['short_name'],
                    ],
                    "datePublished" => date('c',strtotime(Yii::$app->item['time'])),
                    "dateModified" => date('c',strtotime(Yii::$app->item['updated_at'])),
                    "image" => isset(Yii::$app->item['icon']) ? getAbsoluteUrl(Yii::$app->item['icon']) : '',
                    "articleSection" => __CATEGORY_NAME__,
                    "description" => uh(isset(Yii::$app->item['info']) ? strip_tags(Yii::$app->item['info']) : ''),
                    "articleBody" => \yii\helpers\Html::encode(strip_tags($text)),
                    "url" => getAbsoluteUrl(Yii::$app->item['url_link']),
                    "publisher" => $publisher,
                    "mainEntityOfPage" => [
                        "@type" => "WebPage",
                        "@id" => getAbsoluteUrl(__CATEGORY_URL__)
                    ],
                    //"aggregateRating" => $aggregateRating
                ];

                $vote = \app\models\Ratings::getRating(Yii::$app->item['id']);
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
                        $authName =  isset(Yii::$app->item['post_by_name']) && Yii::$app->item['post_by_name'] != "" ? Yii::$app->item['post_by_name'] : Yii::$app->user->getNameByUser(Yii::$app->item['created_by']);

                        $newsarticle = [
                        "@context" => "http://schema.org",
                        "@type" => "NewsArticle",
                        "name" => uh(strip_tags(Yii::$app->item['title'])),
                        "headline" => uh(Yii::$app->item['title']),
                        "alternativeHeadline" => uh(strip_tags(Yii::$app->item['info'])),
                        "dateline" => Yii::$app->cfg->contact['short_name'],
                        "image" => [
                        getAbsoluteUrl(Yii::$app->item['icon'])
                        ],
                        "datePublished" => date('c',strtotime(Yii::$app->item['time'])),
                        "dateModified" => date('c',strtotime(Yii::$app->item['updated_at'])),
                        "description" => uh(strip_tags(Yii::$app->item['info'])),
                        "articleBody" => \yii\helpers\Html::encode(strip_tags($text)),
                        "url" => getAbsoluteUrl(Yii::$app->item['url_link']),
                        "author" => [
                        "@type" => "Person",
                        "name" =>$authName != null ? $authName : Yii::$app->cfg->contact['short_name'],
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
                    case 'products': case 'product':
                        $authName =  isset(Yii::$app->item['post_by_name']) && Yii::$app->item['post_by_name'] != "" ? Yii::$app->item['post_by_name'] : Yii::$app->user->getNameByUser(Yii::$app->item['created_by']);
                        
                        $product = [
                            "@context" => "http://schema.org",
                            "@type" => "Product",
                            "name" => uh(strip_tags(Yii::$app->item['title'])),
                            "image" => isset(Yii::$app->item['icon']) ? getAbsoluteUrl(Yii::$app->item['icon']) : '',
                            "description"=>isset(Yii::$app->item['info']) ? uh(strip_tags(Yii::$app->item['info'])) : '',
                            "sku"=>Yii::$app->item['code'],
                            "url"=>getAbsoluteUrl(Yii::$app->item['url_link']),
                            
                            "offers" => [
                                "@type"=>"Offer",
                                "availability"=>"http://schema.org/InStock",
                                "price"=>round(Yii::$app->item['price2'] , Yii::$app->currencies->getPrecision(Yii::$app->item['currency'])),
                                "priceCurrency"=>Yii::$app->currencies->getCode(Yii::$app->item['currency']),
                                "seller"=>["@type"=>"Organization","name"=>Yii::$app->cfg->contact['name']]
                            ]
                        ];
                        
                        $b = \app\modules\admin\models\Content::getItemProducer(Yii::$app->item['id']);
                        if(!empty($b)){
                            $product['brand'] = $b['title'];
                        }
                        if(Yii::$app->item['status']>0){
                            $product["itemCondition"] = readProductStatus(Yii::$app->item['status']);
                        }
                        
                        $mpn = true;
                        switch (strlen(Yii::$app->item['barcode'])){
                            case 8:
                                $product["gtin8"] = (Yii::$app->item['barcode']);
                                $mpn = false;
                                break;
                            case 12:
                                $product["gtin12"] = (Yii::$app->item['barcode']);
                                $mpn = false;
                                break;
                            case 13:
                                $product["gtin13"] = (Yii::$app->item['barcode']);
                                $mpn = false;
                                break;
                            case 14:
                                $product["gtin14"] = (Yii::$app->item['barcode']);
                                $mpn = false;
                                break;
                                
                        }
                        if($mpn && Yii::$app->item['mpn'] != ""){
                            $product["mpn"] = readProductStatus(Yii::$app->item['mpn']);
                        }
                        
                        
                        $jsonLD[] = $product;
                        break;
                }

        }



        // LocalBusiness
        $LocalBusiness = [
            "@context" => "http://schema.org",
            "@type" => "LocalBusiness",
            "address" => $address,
            "description" => isset(Yii::$app->cfg->contact['description']) ? Yii::$app->cfg->contact['description'] : '',
            "name" => isset(Yii::$app->cfg->contact['short_name']) && Yii::$app->cfg->contact['short_name'] != "" ?
            Yii::$app->cfg->contact['short_name'] : (isset(Yii::$app->cfg->contact['name']) ? Yii::$app->cfg->contact['name'] : ''),
            "telephone"=> isset(Yii::$app->cfg->contact['hotline']) ? parsePhoneNumber(Yii::$app->cfg->contact['hotline']) : '',
            "url" => ABSOLUTE_DOMAIN,
            "image" => $logoImage,
            "logo" => $logoImage,
            //	"priceRange"=>isset(Yii::$app->item['seo']['priceRange']) ? Yii::$app->item['seo']['priceRange'] : '0',
            "sameAs"=>$sameAs,
            "openingHours" => "Mo-Su",
            "aggregateRating" =>	[
                "@type" => "AggregateRating",
                "ratingValue" => '5/5' ,
                "ratingCount" => date('z') + (date('Y') - 2018) * 365,

            ]
        ];
        if(isset(Yii::$app->item['seo']['priceRange']) && Yii::$app->item['seo']['priceRange'] != ""){
            $LocalBusiness['priceRange'] = Yii::$app->item['seo']['priceRange'];
        }
        if(!empty($geo)){
            $LocalBusiness['geo'] = $geo;
        }

        $jsonLD [] = $LocalBusiness;

        return '<script type="application/ld+json">' .(json_encode($jsonLD)) .'</script>';
    }


    /**
     * Old function
     */

    public function getBox($code){
        return (new Query())->from(\app\models\Box::tableName())->where([
            'sid'	=>	__SID__,
            'code'	=>	$code,
            'lang'	=>	__LANG__
        ])->one();
    }

    public function showBoxText($code){
        $r = $this->getBox($code);
        if(!empty($r) && isset($r['text'])){
            return uh($r['text'],2);
        }
    }

    // Nhà sx
    public function getItemProducer($id){

    }

    // Thương hiệu
    public function getItemTrademark($id){
        return (new Query())->select(['a.*'])
        ->from(['a'=>'{{%text_attrs}}'])
        ->innerJoin(['b'=>'{{%item_to_text_attrs}}'],'a.id=b.text_id')
        ->where(['a.sid'=>__SID__, 'b.item_id'=>$id,'b.type_id'=>6])->one();
    }

    // Xuất xứ
    public function getItemMadein($id){
        return (new Query())->select(['a.*'])
        ->from(['a'=>'{{%text_attrs}}'])
        ->innerJoin(['b'=>'{{%item_to_text_attrs}}'],'a.id=b.text_id')
        ->where(['a.sid'=>__SID__, 'b.item_id'=>$id,'b.type_id'=>5])->one();
    }


    // Nhà cung cấp
    public function getItemSupplier($id){
        return (new Query())->select(['a.*'])->from(['a'=>'{{%customers}}'])
        ->innerJoin(['b'=>'{{%items_to_customers}}'],'a.id=b.customer_id')
        ->where(['b.item_id'=>$id,'b.type_id'=>TYPE_ID_VENDOR])->one();
    }

    // Số lượng trong kho

    public function getItemQuantity($id){
        //item_to_warehouse
        $c = (new Query())
        ->select((new \yii\db\Expression('sum(quantity)')))
        ->from('item_to_warehouse')->where(['item_id'=>$id])->scalar();

        return $c;
    }


    public function getItemTextAttrs($id,$type_id){
        return (new Query())->select(['a.*'])
        ->from(['a'=>'{{%text_attrs}}'])
        ->innerJoin(['b'=>'{{%item_to_text_attrs}}'],'a.id=b.text_id')
        ->where(['a.sid'=>__SID__, 'b.item_id'=>$id,'b.type_id'=>$type_id])->one();

    }


    public function updateCart($o = []){
        $action = isset($o['action']) ? $o['action'] : 'add';
        $quantity = isset($o['quantity']) ? $o['quantity'] : 1;
        $item = isset($o['item']) ? $o['item'] : [];
        $item_id = isset($o['item_id']) ? $o['item_id'] : (isset($item['id']) ? $item['id'] : 0);
        //

        //
        $session = Yii::$app->session;
        $s = $session->get(__SITE_NAME__);
        $c = isset($s['cart']) ? $s['cart'] : [];

        if(empty($item) && isset($c[$item_id]['item'])){
            $item = $c[$item_id]['item'];
        }

        if(!isset($c[$item_id]['quantity'])){
            $c[$item_id]['quantity'] = 0;
        }

        if($item_id>0){
            switch($action){
                case 'add':
                    $i = \app\modules\admin\models\Content::getItem($item_id);
                    if(!empty($i)){
                        $item['title'] = $i['title'];
                        $item['code'] = $i['code'];
                        $item['url'] = $i['url'];
                        $item['url_link'] = $i['url_link'];
                        $item['listImages'] = $i['listImages'];
                        $item['icon'] = $i['icon'];
                        $item['currency'] = $i['currency'];

                    }
                    $c[$item_id]['quantity'] += $quantity;
                    break;

                case 'inc':
                    //if($c[$item_id]['quantity']>1){
                    $c[$item_id]['quantity'] += $quantity;
                    //}
                    break;
                case 'desc':
                    if($c[$item_id]['quantity']>1){
                        $c[$item_id]['quantity'] -= $quantity;
                    }
                    break;
                case 'update':case 'set':
                    $c[$item_id]['quantity'] = $quantity;
                    break;
                case 'delete': case 'del':
                    $c[$item_id]['quantity'] = 0;
                    unset($c[$item_id]);
                    break;
            }
        }
        //
        $item['id'] = $item_id;
        //view($item);
        //
        $a = ['price1','price2','price'];
        foreach ($a as $b){
            if(!isset($item[$b])){
                $item[$b] = isset($o[$b]) ? $o[$b] : 0;
            }
        }

        $a = ['size','color'];
        foreach ($a as $b){
            if(isset($o[$b])){
                $item[$b] = $o[$b];
            }
        }

        if(!empty($c) && isset($c[$item_id])){
            if(empty($item)){
                $c[$item_id]['quantity'] = 0;
                unset($c[$item_id]);
                return false;
            }else{
                //view($item['price2']>0);
                $item['price2'] = isset($item['price2']) && $item['price2']>0 ? $item['price2'] : (isset($item['price']) ? $item['price'] : 0);
                $c[$item_id]['price']= $item['price2'];
                $c[$item_id]['amount'] = $item['amount'] = $c[$item_id]['total'] = $item['price2'] * $c[$item_id]['quantity'];
                $item['quantity'] = $c[$item_id]['quantity'];
                $c[$item_id]['item'] = $item;
            }
        }
        $s['cart'] = $c;
        $session->set(__SITE_NAME__, $s);
    }

    public function unsetCart(){
        $_SESSION[__SITE_NAME__]['cart'] = null;
        unset($_SESSION[__SITE_NAME__]['cart']);
        $session = Yii::$app->session;
        $s = $session->get(__SITE_NAME__);
        if(isset($s['cart'])){
            unset($s['cart']);
        }
        $session->set(__SITE_NAME__, $s);
    }

    public function getCart($item_id = 0, $order_id = 0){
        //
        if($order_id>0){
            $order = \app\modules\admin\models\Orders::getItem2($order_id);


            return array(
                'totalItem'	=>	$order['total_quantity'],
                'totalQuantity'	=>	$order['total_quantity'],
                'totalPrice'=>	$order['total_price'],
                'listItem'	=>	[],
                'seller'	=>	isset($order['seller']) ? $order['seller'] : [],
                'currency'	=>  $order['currency']
            );
        }else{
            $s = Yii::$app->session;

            $sn = $s->get(__SITE_NAME__);

            $c = isset($sn['cart']) ? $sn['cart'] : [0=>[
                'quantity'=>0,
                'amount'=>0,
                'total'=>0,
                'price'=>0
            ]];
        }
        $listItem = [];
        $totalItem = $totalQuantity = 0;
        $totalPrice = 0;
        $currency = 1;
        $seller = [];

        if(!empty($c)){

            foreach($c as $k=>$v){
                if($k>0){
                    if(!isset($v['seller_id'])){
                        $seller_id = $v['seller_id'] = 0;
                    }
                    $seller_id = $v['seller_id'];

                    $listItem[] = $v['item'];
                    $totalItem ++;
                    $totalQuantity += $v['quantity'];
                    $totalPrice += $v['total'];

                    if(!isset($seller[$seller_id]['listItem'])){
                        $seller[$seller_id]['listItem'] = [];
                        $seller[$seller_id]['totalItem'] = 0;
                        $seller[$seller_id]['totalPrice'] = 0;
                    }
                    if(!isset($seller[$seller_id]['totalQuantity'])){
                        $seller[$seller_id]['totalQuantity'] = 0;
                    }

                    $seller[$seller_id]['listItem'][] = $v['item'];
                    $seller[$seller_id]['totalItem']++;
                    $seller[$seller_id]['totalQuantity']++;

                    $seller[$seller_id]['totalPrice'] += $v['total'];
                    $currency = isset($v['item']['currency']) ? $v['item']['currency'] : 1;
                    //
                    if($item_id == $v['item']['id']){
                        return $v['item'];
                    }
                }
            }

        }

        $cart = array(
            'totalItem'	=>	$totalItem,
            'totalQuantity'	=>	$totalQuantity,
            'totalPrice'=>	$totalPrice,
            'listItem'	=>	$listItem,
            'seller'	=>	$seller,
            'currency'	=>  $currency
        );

        return $cart;
    }


    public function getPaging($o = []){
        //view(cu(['/tin-tuc/tin-tuc-moi-truong/tin-moi-truong']));
        $total_pages = isset($o['total_pages']) ? $o['total_pages'] : 1;
        $regex = isset($o['regex']) ? $o['regex'] : null;
        $p = isset($o['p']) ? $o['p'] : 1;
        $prev = $p>1 ? $p-1 : 1;
        $next = $p < $total_pages ? $p+1 : $total_pages;
        $html = '';
        if($total_pages>1){

            $limit_page = isset($o['limit_page']) && $o['limit_page']> 0 ? $o['limit_page'] : $total_pages;
            $part = intval($limit_page/2);
            $start_page = $p - $part;
            $start_page = $start_page > 0 ? $start_page : 1;
            $end_page = $p + $part;
            $end_page = $end_page < $total_pages ? $end_page: $total_pages;
            $end_page = $end_page < $limit_page ? $limit_page : $end_page;

            $html .= '<div class="clear"></div><div class="page_break page_break_cus1 ">
			<div class="btn-group" role="group" aria-label="First group">';
            $html .= '<a '.($p == 1 ? 'disabled' : '').' href="'.buildUrl(['p'=>$prev],['regex'=>$regex,'igrones'=>['view']]).'" class="btn btn-default"><i class="fa fa-long-arrow-left"></i></a>';
            for($i = $start_page;$i< $end_page+1; $i++){
                $html .= '<a '.($p == $i ? 'disabled' : '').' href="'.buildUrl(['p'=>$i],['regex'=>$regex,'igrones'=>['view']]).'" class="btn btn-default">'.$i.'</a>';
            }
            $html .= '<a '.($p == $total_pages ? 'disabled' : '').' href="'.buildUrl(['p'=>$next],['regex'=>$regex,'igrones'=>['view']]).'" class="btn btn-default"><i class="fa fa-long-arrow-right"></i></a>';
            $html .= '</div></div>';
        }
        return $html;
    }
    /*
     * Mẫu KHHD
     */

    public function Tour_KeHoachHuongDan($id, $o = []){
        
        $item_id = $id;
        
        $v = \app\modules\admin\models\ToursPrograms::getItem($id);
        $inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
        
        $table_id = isset($o['id']) ? $o['id'] : randString(12);
        
        
        $day = max($v['day'],$v['night']);
        $cols = 14; 
        
        $w = 100/$cols; 
        
        if(!is_numeric($w)){
            $w = str_replace(',', '.', $w);
        }
        
        $language = isset($o['language']) ? $o['language'] : __LANG__;
        $currency = isset($o['currency']) ? $o['currency'] : $v['currency'];
        
        $tdStyle = 'border: 2px solid #999;padding: 8px;vertical-align: middle;font-size: 12px;line-height: 1.42857143;';
        $pStyle = 'margin:0;font-size:12px;';
        

        $html = '<div class="clear"></div>
<table id="'.$table_id.'" cellpadding="0" cellspacing="0" data-name="cool-table" class="table table-bordered vtop table-striped" '.($inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').'>
<colgroup>';
        for($i=0; $i<$cols;$i++){
            $html .= '<col style="width:'.$w.'%">';
        }
        $html .= '</colgroup><thead>

</thead>
<tbody class="">
<tr>
<th class="center" '.($inline_css ? 
    'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;width:'.$w.'%"' : 
    'style="width:'.$w.'%"').'>Ngày</th>
<th colspan="3" class="center" '.($inline_css ? 
    'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:center;width:'.(3*$w).'%"' : 
    'style="width:'.(3*$w).'%"').'>Sáng - Trưa</th>
<th colspan="2" class="center" '.($inline_css ? 
    'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:center;min-width:120px;width:'.(2*$w).'%"' : 
    'style="width:'.(2*$w).'%"').'>Ăn trưa</th>
<th colspan="3" class="center" '.($inline_css ? 
    'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:center;width:'.(3*$w).'%"' : 
    'style="width:'.(3*$w).'%"').'>Chiều - Tối</th>
<th colspan="2" class="center" '.($inline_css ? 
    'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:center;min-width:120px;width:'.(2*$w).'%"' : 
    'style="width:'.(2*$w).'%"').'>Ăn tối</th>
<th colspan="3" class="center" '.($inline_css ? 
    'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;min-width:200px;width:'.(3*$w).'%"' : 
    'style="width:'.(3*$w).'%"').'>Ngủ</th>
</tr>
';

        $selected_meal = $selected_acc = [] ;
        
        for($i = 0; $i<$day;$i++){
            for($j=0;$j<4;$j++){
                
                $sx = \app\modules\admin\models\ToursPrograms::getProgramServices($id,$i,$j);
                
                
                
                $servicesx[$j] = $sx;
                
                //$_c += max(count($servicesx[$j]),1);
            }
            $date =  date('Y-m-d', mktime(0, 0, 0, date("m",strtotime($v['from_date']))  , date("d",strtotime($v['from_date']))+$i, date("Y",strtotime($v['from_date']))));
            $html .= '<tr>';
            $html .= '<td class="center" '.($inline_css ? 'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:center;"' : '').'>
<span>'.(check_date_string($v['from_date']) ?  readDate($date,['spc'=>' <br> ','format'=>'d/m/y']) : 'Ngày '.($i+1)).'</span>
</td>';
            // Sáng : $j = 0
            $j = 0;
            $html .= '<td colspan="3" class="" '.($inline_css ? 'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:left;"' : '').'>';
            $services = $servicesx[$j];
            
             
            
            //$services = Yii::$app->tour->program->getModel()->getAllServiceDayPrice($item_id);
            
            
            $note = [];
            if(!empty($services)){
                $tx = [];
                foreach ($services as $kv=>$sv){
                    
                    $title = uh($sv['title']);
                    $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                    if(isset($svx['note']) && $svx['note'] != ""){
                        $title .= '&nbsp;<i style="font-style:italic" class="pm0 italic text-muted f11px aleft">('.uh($svx['note']).')</i>';
                    }
                    
                    $tx[] = $title;
                    
                    
                }
                $html .= implode(' → ', $tx);
                if(!empty($note)){
                    $html .= implode('', $note);
                }
            }



            $html .= '</td>';

            // Trưa :
            $j = 1;
            $html .= '<td colspan="2" class="" '.($inline_css ? 'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:left;"' : '').'>';
            $services = $servicesx[$j];
            if(!empty($services)){
                foreach ($services as $kv=>$sv){
                    
                    
                    $svx = Yii::$app->tour->program->getModel()->getAllServiceDayDetail([
                        'item_id'=>$item_id,
                        'service_id'=>$sv['service_id'],
                        'type_id'=>$sv['type_id'],
                        'day_id'=>$sv['day_id'],
                        'time_id'=>$sv['time_id'],
                        'package_id'=>$sv['package_id'],
                    ]);
                    //$svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                    //view($svx);
                    
                    $xs = $sv['time_id'] == 3 ?  (isset($svx['selected_acc'][$sv['package_id']][$sv['type_id']][$sv['service_id']]) ? $svx['selected_acc'][$sv['package_id']][$sv['type_id']][$sv['service_id']] : []) : '';
                    
                    if($xs == 'on'){
                        $selected_acc[$sv['day_id']][$sv['time_id']]['status'] = true;
                        
                        $s = Yii::$app->tour->program->model->getServiceDetail($item_id, $sv['service_id'], $i, $sv['time_id'], $sv['type_id']);
                        
                        if(!empty($s)){
                            
                            $name = uh(isset($s['title']) ? $s['title'] : $s['name']);
                            
                            $name=
                            isset($s['lang_code']) ? uh(Yii::$app->t->translate($s['lang_code'], $language, ['default'=>$name]))  : $name ;
                            $selected_acc[$sv['day_id']][$sv['time_id']]['name'] = $name;
                        }else{
                            $selected_acc[$sv['day_id']][$sv['time_id']]['name'] = '';
                        }
                        
                        
                        continue;
                    }
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    switch ($sv['type_id']){
                        case TYPE_ID_REST:
                            
                            $html .= '<p class="pm0" style="padding-bottom:0"><b>'.uh($sv['title']).'</b></p>';
                            $html .= $sv['phone'] != '' ? '<p class="pm0" style="padding-bottom:0">ĐT: '.($sv['phone']).'</p>' : '';
                            $html .= '<p class="pm0" style="padding-bottom:0">'.($sv['address']).'</p>';
                            $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                            if(isset($svx['note']) && $svx['note'] != ""){
                                $html .= '<p class="pm0 italic text-muted f11px aleft">('.uh($svx['note']).')</p>';
                            }
                            
                            break;
                        default:
                            if(!in_array($sv['type_id'], [
                            TYPE_ID_HOTEL,
                            TYPE_ID_TRAIN,
                            TYPE_ID_SHIP_HOTEL,
                            TYPE_ID_REST
                            ])){                                                                 
                                
                                $title = uh($sv['title']);
                                $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                                if(isset($svx['note']) && $svx['note'] != ""){
                                    $title .= '&nbsp;<i style="font-style:italic" class="pm0 italic text-muted f11px aleft">('.uh($svx['note']).')</i>';
                                }
                                
                                $html .= "→ $title ";
                                
                            }
                            break;
                    }

                }
            }
            $html .= '</td>';

            // Chiều :
            $j = 2;
            $html .= '<td colspan="3" class="" '.($inline_css ? 'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:left;"' : '').'>';
            $services = $servicesx[$j];
            if(!empty($services)){
                $tx = [];
                foreach ($services as $kv=>$sv){
 
                    $title = uh($sv['title']);
                    $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                    if(isset($svx['note']) && $svx['note'] != ""){
                        $title .= '&nbsp;<i style="font-style:italic" class="pm0 italic text-muted f11px aleft">('.uh($svx['note']).')</i>';
                    }
                    
                    $tx[] = $title;
                    
                    
                }
                $html .= implode(' → ', $tx);
            }
            $html .= '</td>';

            // Tối :
            $j = 3;
            $html .= '<td colspan="2" class="" '.($inline_css ? 'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:left;"' : '').'>';
            $services = $servicesx[$j];
            
             
            
            if(!empty($services)){
                foreach ($services as $kv=>$sv){
                    switch ($sv['type_id']){
                        
                        case TYPE_ID_REST:
                        
                           
                            //$html .= json_encode($sv);
                            
                            $html .= '<p class="pm0" style="padding-bottom:0"><b>'.uh($sv['title']).'</b></p>';
                            
                            $html .= isset($sv['phone']) && $sv['phone'] != '' ? '<p class="pm0" style="padding-bottom:0;font-style:italic;">ĐT: '.($sv['phone']).'</p>' : '';
                            $html .= isset($sv['address']) && $sv['address'] != '' ?  '<p class="pm0" style="padding-bottom:0;font-style:italic;">'.($sv['address']).'</p>' : '';
                            $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                            if(isset($svx['note']) && $svx['note'] != ""){
                                $html .= '<p class="pm0 italic text-muted f11px aleft" style="font-style:italic;">('.uh($svx['note']).')</p>';
                            }
                            break;
                        default:
                            
                            $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                            
                            $xs = $sv['time_id'] == 3 ?  (isset($svx['selected_acc'][$sv['package_id']][$sv['type_id']][$sv['service_id']]) ? $svx['selected_acc'][$sv['package_id']][$sv['type_id']][$sv['service_id']] : []) : '';
                            
                            if($xs == 'on'){
                                $selected_acc[$sv['day_id']][$sv['time_id']]['status'] = true;
                                
                                $s = Yii::$app->tour->program->model->getServiceDetail($item_id, $sv['service_id'], $i, $sv['time_id'], $sv['type_id']);
                                
                               
                                
                                if(!empty($s)){
                                    
                                    $name = uh(isset($s['title']) ? $s['title'] : $s['name']);
                                    
                                    $name=
                                    isset($s['lang_code']) ? uh(Yii::$app->t->translate($s['lang_code'], $language, ['default'=>$name]))  : $name ;
                                    $selected_acc[$sv['day_id']][$sv['time_id']]['name'] = $name;
                                }else{
                                    $selected_acc[$sv['day_id']][$sv['time_id']]['name'] = '';
                                }
                                
                                
                                continue;
                            }
                            
                            
                            if(!in_array($sv['type_id'], [
                            TYPE_ID_HOTEL,
                            TYPE_ID_TRAIN,
                            TYPE_ID_SHIP_HOTEL
                            ])){
                                
                                
                                $title = uh($sv['title']);
                                $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                                if(isset($svx['note']) && $svx['note'] != ""){
                                    $title .= '&nbsp;<i style="font-style:italic" class="pm0 italic text-muted f11px aleft">('.uh($svx['note']).')</i>';
                                }
                                
                                $html .= "→ $title ";
                                
                            }
                            break;
                        
                    }

                }
            }
            $html .= '</td>';

            // Tối : $j = 3
            $html .= '<td colspan="3" class="" '.($inline_css ? 'style="border: 2px solid #999 !important;padding: 3px;line-height: 1.42857143;vertical-align: top;font-size: 12px;text-align:left;"' : '').'>';
            $services = $servicesx[$j];
            if(!empty($services)){
                foreach ($services as $kv=>$sv){
                    switch ($sv['type_id']){
                        case TYPE_ID_HOTEL:
                        case TYPE_ID_SHIP_HOTEL:
                            
                            $html .= '<p class="pm0" style="padding-bottom:0"><b>'.uh($sv['title']).'</b></p>';
                            $html .= $sv['phone'] != '' ? '<p class="pm0" style="padding-bottom:0;font-style:italic;">ĐT: '.($sv['phone']).'</p>' : '';
                            $html .= '<p class="pm0" style="padding-bottom:0;font-style:italic;">'.($sv['address']).'</p>';
                            $svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']);
                            if(isset($svx['note']) && $svx['note'] != ""){
                                $html .= '<p class="pm0 italic text-muted f11px aleft" style="font-style:italic;">('.uh($svx['note']).')</p>';
                            }
                            
                            break;
                            
                        case TYPE_ID_TRAIN:                            
                            
                            $supplier = Yii::$app->customer->getItem($sv['supplier_id']);
                            $room = Yii::$app->tour->hotel->model->getRoom($sv['sub_item_id']);
                            
                            $html .= '<p class="pm0" style="padding-bottom:0"><b>'.uh($supplier['name']).(!empty($room) ? ' - ' . $room['title'] : '').'</b></p>';
                            break;
                        default:
                            
                            break;
                    }

                }
            }
            
            if(isset($sv) && isset($selected_acc[$sv['day_id']][$j])  && $selected_acc[$sv['day_id']][$j]['status'] === true){
                $html .= '<p style="'.$pStyle.'"><b>' . $selected_acc[$sv['day_id']][$j]['name'] . '</b></p>';
            }
            
//             view($selected_acc);
            
            $html .= '</td>';

            $html .= '</tr>';
        }


        $html .= '</tbody></table>';



        return $html;
    }

    /*
     * Mẫu báo cáo chương trình tour
     */

    public function Tour_ThongTinChung($id, $o = []){
        $v = \app\modules\admin\models\ToursPrograms::getItem($id);
        $inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
        $html = '<div class="clear"></div>
<table cellpadding="0" cellspacing="0" class="table table-bordered vmiddle table-striped" '.($inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').'>
<colgroup><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"></colgroup><thead></thead>
<tbody class="">
<tr class="col-middle">
<td '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Loại tour</td>
<td colspan="3" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>';

        $v2 = \app\modules\admin\models\Filters::getItem($v['tour_category']);
        $html .= $v2['title'];

        $html .= '</td>
<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>SL khách</td>
<td colspan="7" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>'.$v['guest'].'</b></td></tr>';



        $html .= '<tr class="col-middle">
<td '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Thời gian</td>
<td colspan="3" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>';

        $html .= '<b>'.$v['day'].'</b> ' . Yii::$app->t->translate('label_day',ADMIN_LANG);
        $html .= ' - ';
        $html .= '<b>'.$v['night'].'</b> ' . Yii::$app->t->translate('label_night',ADMIN_LANG);

        $html .= '</td>
<td colspan="1" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'> Quốc tịch</td>
<td colspan="7" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>';
        $v1 = \app\modules\admin\models\Local::getItem($v['nationality']);
        if(!empty($v1)){
            $html .= '<b>'.$v1['title'].'</b>';
        }
        $html .= '</b></td></tr>';

        $html .= '<tr class="col-middle">
<td '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Tiền tệ</td>
<td colspan="1" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'> '.Yii::$app->zii->showCurrency($v['currency']).' </td>
<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Tỷ giá USD</td>
<td colspan="1" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>'.number_format(Yii::$app->zii->getItemExchangeRate(
    [
        'item_id'=>$id,
        'from'=>2,
        'to'=>$v['currency'],
        'time'=>$v['time']
    ]),0).'</b></td>
<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Đón</td>
<td colspan="3" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>';
    $v1 = \app\modules\admin\models\Places::getItem($v['in']);
    if(!empty($v1)){
        $html .= '<b>'.uh($v1['title']).'</b>';
    }
    $html .= '</b>
</td><td colspan="1" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Tiễn</td>
<td colspan="3" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>';
    $v1 = \app\modules\admin\models\Places::getItem($v['out']);
    if(!empty($v1)){
        $html .= '<b>'.uh($v1['title']).'</b>';
    }
    $html .= '</b></td></tr>';

    $html .= '<tr class="col-middle"><td '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Mã chương trình</td>
<td colspan="3" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>'.(isset($v['code']) ? $v['code']  : '').'</b></td>
<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Tiêu đề</td>
<td colspan="7" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>'.(isset($v['title']) ? $v['title']  : '').'</b></td></tr>';

    $html .= '<tr class="col-middle">
<td '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Ngày bắt đầu</td>
<td colspan="3" class="pr" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>'.date("d/m/Y",strtotime($v['from_date'])).'</b></td>
<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>Kết thúc</td>
<td colspan="7" class="pr" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'><b>'.date("d/m/Y",strtotime($v['to_date'])).'</b></td>
</tr></tbody></table>';



    return $html;
    }

    public function Tour_DichVuNgay($id, $o = []){
        $html = '<div class="clear"></div>';
        $html .= loadTourProgramDetail([
            'print' => true,
            'updateDatabase'=>false,
            //'day'=>(isset($v['day']) ? (max($v['day'],$v['night'])) : 0),
            'id'=>$id,
            'fields' => $o,
            'inline_css' => isset($o['inline_css']) ? $o['inline_css'] : false,
        ])['html'];

        $html .= ' ';

        return $html;

    }

    public function Tour_VanChuyen($id, $o = []){
        $html = '<div class="clear"></div>';

        $html .= getTourProgramSegments($id, ['print' => true,
            'updateDatabase'=>false,
            'fields'=>$o,
            'label'=>false,
            'sub_label' => 'Vận chuyển',
            'inline_css' => isset($o['inline_css']) ? $o['inline_css'] : false,

        ])['html'];

        return $html;
    }


    public function Tour_HuongDan($id, $o = []){
        $html = '<div class="clear"></div>';

        $html .= loadTourProgramGuides($id, ['print' => true,
            'updateDatabase'=>false,
            'fields'=>$o,
            'sub_label'=>false,
            'inline_css' => isset($o['inline_css']) ? $o['inline_css'] : false,

        ])['html'];
        $html .= '<div class="clear cd3zxxx"></div>';
        return $html;
    }
    public function Tour_BangTongHop($id, $o = []){
        $html = '<div class="clear"></div>';
        $guest = isset($o['guest']) ? $o['guest'] : false;
        $inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
        $item_id = $id;

        $print = true;

        $profit_price = $vat_price = 0;



        $item = \app\modules\admin\models\ToursPrograms::getItem($item_id);

        $profit_price = $item['net_price'] * $item['profit'] / 100;
        $vat_price = ($item['net_price']+$profit_price) * $item['vat_tax'] / 100;

        if($guest){
            $item['net_price'] += $profit_price;
        }

        $avrg_price = $item['total_price'] / $item['guest'];

        $html .= '<div class="clear"></div>
<p class="upper bold grid-sui-pheader aleft " '.($inline_css ? 'style="font-weight: bold;border: 1px solid #ddd;line-height: 30px;border-bottom: none;background-color: #dedede;padding-left: 15px;margin-bottom:0"' : '').'>Bảng tổng hợp chi phí</p>
<table class="table table-bordered vmiddle" cellpadding="0" cellspacing="0" '.($inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').'>';
        if(!$guest){
            $html .= '<tr class="bold">
<td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Chi phí dịch vụ ngày</b></td>
<td class="aright " colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
'.getCurrencyText($item['total_price1'],$item['currency'],['show_symbol'=>true]).'
</td></tr>
<tr class="bold">
<td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Chi phí vận chuyển</b></td>
<td class="aright" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
'.getCurrencyText($item['total_price2'],$item['currency'],['show_symbol'=>true]).'
</td></tr>
<tr class="bold"><td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Chi phí hướng dẫn viên</b></td>
<td class="aright" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
'.getCurrencyText($item['total_price3'],$item['currency'],['show_symbol'=>true]).'
</td></tr>';
            $html .= '<tr class="bold"><td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Chi phí khác</b></td>
<td class="aright" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
'.getCurrencyText($item['total_price4'],$item['currency'],['show_symbol'=>true]).'
</td></tr>';
        }
        $html .= '
<tr class="bold"><td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Tổng (Giá NET)</b></td>
<td class="aright green" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>'.getCurrencyText($item['net_price'],$item['currency'],['show_symbol'=>true]).'</b></td></tr>';
        if(!$guest){
            $html .= '<tr class="bold"><td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Lợi nhuận (%)</b></td>
<td class="center w100p" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>'.($print ? '<b>'.$item['profit'].'</b>': '<input type="number"
class="form-control bold aright input-sm number-format"
onblur="call_ajax_function(this);"
data-action="Tour_program_update_profit"
data-decimal="2"
data-item_id="'.$item_id.'"
data-old="'.$item['profit'].'"
value="'.$item['profit'].'"/>').'

</td>
<td class="aright w250p" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
<b>'.getCurrencyText($profit_price,$item['currency'],['show_symbol'=>true]).'</b>
</td>
</tr>';
        }
        $html .= '<tr class="bold">
<td class="aright "'.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>VAT (%)</b></td>
<td class="center w100p" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>'.($print ? '<b>'.$item['vat_tax'].'</b>' : '<input type="number"
class="aright bold form-control input-sm number-format" data-decimal="2"
onblur="call_ajax_function(this);"
data-action="Tour_program_update_vat"
data-decimal="2"
data-item_id="'.$item_id.'"
data-old="'.$item['vat_tax'].'"
value="'.$item['vat_tax'].'"/>').'

</td>
<td class="aright" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
<b>'.getCurrencyText($vat_price,$item['currency'],['show_symbol'=>true]).'</b>
</td></tr>
<tr class="bold"><td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Tổng cộng</b></td>
<td class="aright text-danger" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>'.getCurrencyText($item['total_price'],$item['currency'],['show_symbol'=>true]).'</b></td></tr>
<tr class="bold"><td class="aright " '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>Giá TB khách</b></td>
<td class="aright red" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><b>'.getCurrencyText($avrg_price,$item['currency'],['show_symbol'=>true]).'</b></td></tr>
					<tbody>';
        $html .= '</tbody></table>';

        return $html;
    }


    public function Tour_RenderEmailForGuest($id){
        $html = '';
        $text1 = Yii::$app->zii->getTextRespon(array('code'=>'RP_REPORT_FOR_GUEST', 'show'=>false));
        $regex = [
            '{{%TOUR_THONG_TIN_CHUNG}}'	=>	$this->Tour_ThongTinChung($id,[
                'inline_css'=>true
            ]),
            '{{%TOUR_CHI_TIET_DICH_VU_NGAY}}'	=>	$this->Tour_DichVuNgay($id,[
                'price'=>false,
                'amount'=>false,
                'inline_css'=>true
            ]),
            '{{%TOUR_VAN_CHUYEN}}'	=>	$this->Tour_VanChuyen($id,[
                'price'=>false,
                'amount'=>false,
                'distance'=>false,
                'inline_css'=>true
            ]),
            '{{%TOUR_HUONG_DAN_VIEN}}'	=>	$this->Tour_HuongDan($id,[
                'price'=>false,
                'amount'=>false,
                'inline_css'=>true,
                'sguide_type'=>false,
            ]),
            '{{%TOUR_BANG_TONG_HOP}}'	=>	$this->Tour_BangTongHop($id,[
                'price'=>false,
                'amount'=>false,
                'inline_css'=>true,
                'guest'=>true
            ]),

        ];
        $html .= replace_text_form($regex, uh($text1['value'],2));

        return $html;
    }

    public function Order_RenderEmailForCustomer($id){

        $order = \app\modules\admin\models\Orders::getItem2($id);


        $html = '';

        $text1 = $this->getTextRespon(array('code'=>'RP_ORDER_CUS', 'show'=>false));
        $regex = [

            '{{%LOGO}}'=>'<a target="_blank" href="'.ABSOLUTE_DOMAIN.'">
                    <img style="max-height:100px;max-width:300px" alt="logo" class="" src="'.(isset(Yii::$app->config['logo']['logo']['image']) ? getAbsoluteUrl(Yii::$app->config['logo']['logo']['image'])  : '').'" /></a>',
            '{{%MY_COMPANY_NAME}}'=>Yii::$app->cfg->contact['name'],
            '{{%MY_COMPANY_ADDRESS}}'=>Yii::$app->cfg->contact['address'],
            '{{%MY_COMPANY_PHONE}}'=>'Hotline: ' .Yii::$app->cfg->contact['hotline'],
            '{{%MY_COMPANY_EMAIL}}'=>'Email: ' .Yii::$app->cfg->contact['email'],
            '{{%MY_COMPANY_INFOMATION}}'=>'<b>' . Yii::$app->cfg->contact['name'] . '</b><br/>
Địa chỉ: '.Yii::$app->cfg->contact['address'].'<br/>
Hotline: '.Yii::$app->cfg->contact['hotline'].'<br/>
Email: '.Yii::$app->cfg->contact['email'].'<br/>
',
            '{{%DOMAIN}}'=>DOMAIN,
            '{{%DOMAIN_LINK}}'=>ABSOLUTE_DOMAIN,

            '{{%ORDER_NUMBER}}'	=>	$order['code'],
            '{{%ORDER_TIME}}'	=>	date('d/m/Y H:i',strtotime($order['time'])),
            '{{%ORDER_TAX_INFOMATION}}'	=>	'',
            '{{%ORDER_OTHER_REQUEST}}'	=> isset($order['other_request']) ?	uh($order['other_request']) : '',
            '{{%ORDER_PRODUCTS_LIST}}'	=>	$this->Order_RenderProductList($id,[]),
            '{{%ORDER_PAYMENT_METHOD}}'	=>	isset($order['payment_method']) ? \app\models\States::showState($order['payment_method']) : '',

            '{{%CUSTOMER_NAME}}'	=>	isset($order['customer']['name']) ? $order['customer']['name']
            : (isset($order['guest']['name']) ? $order['guest']['name'] : ''),
            '{{%CUSTOMER_PHONE}}'	=>	isset($order['customer']['phone']) ? $order['customer']['phone']
            : (isset($order['guest']['phone']) ? $order['guest']['phone'] : ''),
            '{{%CUSTOMER_EMAIL}}'	=>	isset($order['customer']['email']) ? $order['customer']['email']
            : (isset($order['guest']['email']) ? $order['guest']['email'] : ''),
            '{{%CUSTOMER_ADDRESS}}'	=>	isset($order['customer']['address']) ? $order['customer']['address']
            : (isset($order['guest']['address']) ? $order['guest']['address'] : ''),
            //'{{%CUSTOMER_NAME}}'	=>	'',








        ];
        $html .= replace_text_form($regex, uh($text1['value'],2));
        return $html;
    }
    public function Order_RenderEmailForAdmin($id){

        $order = \app\modules\admin\models\Orders::getItem2($id);


        $html = '';

        $text1 = $this->getTextRespon(array('code'=>'RP_ORDER_ADMIN', 'show'=>false));
        $regex = [

            '{{%LOGO}}'=>'<a target="_blank" href="'.ABSOLUTE_DOMAIN.'">
                    <img style="max-height:100px;max-width:300px" alt="logo" class="" src="'.(isset(Yii::$app->config['logo']['logo']['image']) ? getAbsoluteUrl(Yii::$app->config['logo']['logo']['image'])  : '').'" /></a>',
            '{{%MY_COMPANY_NAME}}'=>Yii::$app->cfg->contact['name'],
            '{{%MY_COMPANY_ADDRESS}}'=>Yii::$app->cfg->contact['address'],
            '{{%MY_COMPANY_PHONE}}'=>'Hotline: ' .Yii::$app->cfg->contact['hotline'],
            '{{%MY_COMPANY_EMAIL}}'=>'Email: ' .Yii::$app->cfg->contact['email'],
            '{{%MY_COMPANY_INFOMATION}}'=>'<b>' . Yii::$app->cfg->contact['name'] . '</b><br/>
Địa chỉ: '.Yii::$app->cfg->contact['address'].'<br/>
Hotline: '.Yii::$app->cfg->contact['hotline'].'<br/>
Email: '.Yii::$app->cfg->contact['email'].'<br/>
',
            '{{%DOMAIN}}'=>DOMAIN,
            '{{%ADMIN_LINK}}'=>getAbsoluteUrl(ADMIN_ADDRESS) . '/?_ref=order_email&order_id='. $order['code'],

            '{{%ORDER_NUMBER}}'	=>	$order['code'],
            '{{%ORDER_TIME}}'	=>	date('d/m/Y H:i',strtotime($order['time'])),
            '{{%ORDER_TAX_INFOMATION}}'	=>	'',
            '{{%ORDER_OTHER_REQUEST}}'	=> isset($order['other_request']) ?	uh($order['other_request']) : '',
            '{{%ORDER_PRODUCTS_LIST}}'	=>	$this->Order_RenderProductList($id,[]),
            '{{%ORDER_PAYMENT_METHOD}}'	=>	isset($order['payment_method']) ? \app\models\States::showState($order['payment_method']) : '',

            '{{%CUSTOMER_NAME}}'	=>	isset($order['customer']['name']) ? $order['customer']['name']
            : (isset($order['guest']['name']) ? $order['guest']['name'] : ''),
            '{{%CUSTOMER_PHONE}}'	=>	isset($order['customer']['phone']) ? $order['customer']['phone']
            : (isset($order['guest']['phone']) ? $order['guest']['phone'] : ''),
            '{{%CUSTOMER_EMAIL}}'	=>	isset($order['customer']['email']) ? $order['customer']['email']
            : (isset($order['guest']['email']) ? $order['guest']['email'] : ''),
            '{{%CUSTOMER_ADDRESS}}'	=>	isset($order['customer']['address']) ? $order['customer']['address']
            : (isset($order['guest']['address']) ? $order['guest']['address'] : ''),
            //'{{%CUSTOMER_NAME}}'	=>	'',








        ];
        $html .= replace_text_form($regex, uh($text1['value'],2));
        return $html;
    }

    public function Order_RenderProductList($id, $o=[]){
        $seller_id = isset($o['seller_id']) ? $o['seller_id'] : 0;
        $html = '<table cellpadding="0" cellspacing="0" class="table table-bordered vmiddle table-striped" style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;">';
        $html .= '<colgroup><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"><col style="width:8.333333%"></colgroup>';
        $html .= '<thead><tr>
<th colspan="6" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;background-color:#dedede;">Sản phẩm</th>
<th colspan="3" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">Phân loại</th>
<th colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">SL</th>
<th colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;background-color:#dedede;">Đơn giá</th>
</tr></thead><tbody>';
        if($id > 0){
            //$carts = \app\modules\admin\models\Orders::getItem($id);
            $carts = $this->getCart(0,$id);
        }else{
            $carts = $this->getCart();
        }
        //else{
        //view($carts);
        //
        if($seller_id>0 && isset($carts['seller'])){
            //
            foreach ($carts['seller'] as $seller=>$cart){
                if($seller_id==$seller){
                    if(!empty($cart['listItem'])){
                        foreach ($cart['listItem'] as $v){
                            $html .= '<tr>
<td colspan="6" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"><a target="_blank" href="'.getAbsoluteUrl($v['url_link']).'">'.uh($v['title']).'</a></td>
<td colspan="3" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"></td>
<td colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center"><b>'.$v['quantity'].'</b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red">'.$this->showPrice($v['price2'],$v['currency']).'</b></td>
</tr>';
                        }
                    }

                    $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng cộng </b> ('.$cart['totalQuantity'].' sản phẩm)</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.$this->showPrice($cart['totalPrice'],$cart['currency']).'</b></td>
</tr>';
                    if(isset($cart['note']) && $cart['note'] != ""){
                        $html .= '<tr>
<td colspan="12" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:left"><b>Ghi chú: </b> '.uh($cart['note']).'</td>

</tr>';
                    }
                }
            }
        }elseif( isset($carts['seller'])){
            //
            foreach ($carts['seller'] as $seller=>$cart){
                if(!empty($cart['listItem'])){
                    if($seller>0){
                        $html .= '<tr>
<td colspan="12" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:left "><b>EMZ SHOP</b></td>

</tr>';
                    }

                    foreach ($cart['listItem'] as $v){
                        $html .= '<tr>
<td colspan="6" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"><a target="_blank" href="'.getAbsoluteUrl($v['url_link']).'">'.uh($v['title']).'</a></td>
<td colspan="3" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"></td>
<td colspan="1" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center"><b>'.$v['quantity'].'</b></td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red">'.$this->showPrice($v['price2'],$v['currency']).'</b></td>
</tr>';
                    }
                }
                $html .= '<tr>
<td colspan="10" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b>Tổng cộng </b> ('.$carts['totalQuantity'].' sản phẩm)</td>
<td colspan="2" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right"><b style="color:red;text-decoration:underline">'.$this->showPrice($carts['totalPrice'],$carts['currency']).'</b></td>
</tr>';

                if(isset($cart['note']) && $cart['note'] != ""){
                    $html .= '<tr>
<td colspan="12" style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:left"><b>Ghi chú: </b> '.uh($cart['note']).'</td>

</tr>';
                }

            }
        }
        //}

        $html .= '</tbody></table>';
        return $html ;
    }


    public function Tour_RenderEmailForGuide($id, $params = []){
        $html = '';
        $v = $item = \app\modules\admin\models\ToursPrograms::getItem($id);
         
        
        $text1 = Yii::$app->izi->getTextRespon(array('code'=>'RP_REPORT_FOR_GUIDE', 'show'=>false));

        $other = \app\modules\admin\models\ToursPrograms::Tour_get_other_cost($id);
        $plan_note = $plan_note2 = '';
        if(!empty($other)){
            foreach ($other as $v1){
                if(isset($v1['plan_note']) && $v1['plan_note'] != ""){
                    
                    $plan_note  .= '<p style="float:left;width:100%;margin-bottom:0">
<span style="float:left;width:60%;text-align:right;padding-right:8px">' . uh($v1['title']) .'</span>
<span style="float:left;width:40%;text-align:left;padding-left:8px">' . uh($v1['plan_note']) .'</span>
</p>';
                    
                    $plan_note2 .= uh($v1['plan_note']) .'<br/>';
                }
            }
        }


        $tour_leader = '';
        if($v['tour_leader']>0){
            $user = \app\modules\admin\models\Users::getItem($v['tour_leader']);
            $tour_leader .= showGenderName($user['gender']) . $user['name'] . ' - ' . $user['phone'];
        }

        // HDV
        $guide_type = $root_guide_type = isset($item['guide_type']) ? $item['guide_type']  : 2;
        $guide_language = isset($item['guide_language']) ? $item['guide_language'] : DEFAULT_LANG;

//         $guides = \app\modules\admin\models\ToursPrograms::getProgramGuides2([
//             'item_id'=>$id,
//             //'segment_id'=>0,
//             'guide_type'=>$guide_type
//         ]);
        $g = '';
        
        $segments = Yii::$app->tour->transport->getModel()->getAllSegments($id);
        
        if(!empty($segments)){
            foreach ($segments as $segment) {
                $guides = Yii::$app->tour->guide->model->getSelectedGuideFromSegment([
                    'item_id'=>$id,
                    'guide_type'=>$guide_type,
                    'segment_id'=>$guide_type == 1 ? 0 : $segment['id']
                ]);
                $place = '';
                if(count($segments)>1){
                    $places = Yii::$app->tour->program->getModel()->getSegmentPlaces($segment['id']);
                    if(!empty($places)){
                        $px = [];
                        foreach ($places as $x1 => $place2){
                            $px[] = $place2['title'];
                            //$place .= '<i class="'.($x1>0?'':'fa fa-map-marker tnone').'"> '.$place2['title'].'</i> ';
                        }
                        $place .= '<i class=""> '.implode('+', $px).'</i>';
                    }
                }
                if(!empty($guides)){
                    $g .= $place != '' ? "$place: " . (count($guides)>1 ? '<br/>' : '') : '';
                    foreach ($guides as $guide){                        
                        $g .= Yii::$app->customer->showNameByLanguage($guide) . ($guide['phone'] != "" ? " - " . $guide['phone'] . "" : '').'<br/>';
                    }
                }
                
                if($guide_type == 1){
                    break;
                }
                
            }
        }else{
            
        }
        
        
        
        
        //view2($guides);
       
//         if(!empty($guides)){
//             foreach ($guides as $guide){
//                 if(isset($guide['note']))
//                     $g .= $guide['note'] .'<br/>';
//             }
//         }else{
//             $g = '';
//         }
        if($g == '')$g = '<br/>';
        
        $guest_note = '';
        
        if($v['tl'] + $v['foc'] > 0){
            $foc = [];
            $guest_note .= ' (';
            if($v['tl'] > 0){
                $foc[] = $v['tl'] .' TL';
            }
            
            if($v['foc'] > 0){
                $foc[] = number_format($v['foc']) .' FOC';
            }
            
            $guest_note .= implode(' + ', $foc);
            
            $guest_note .= ')';
        }
        
        $params['inline_css'] = true;
        
        $total_pax = $v['guest1'] + $v['guest2'] + $v['guest3'] + $v['tl']; 
        
        $guest_note = '';
        
        $note = false;
        
        if($v['guest2'] + $v['guest3'] > 0){
            $note = true;
        }
        if($v['tl'] + $v['foc'] > 0){
            $note = true;
        }
        
        if($note){
            
            $note = [];
            
            $guest_note .= ' (';
            $adults = $v['guest1'] ;
            
            $note[] = "$adults " . Yii::$app->t->translate('label_adults_short');
            
            if(($te =$v['guest2'] + $v['guest3']) > 0){
                $note[] = "$te " . Yii::$app->t->translate('label_child_short');
            }
            
            if($v['tl'] > 0){
                $note[] = "${v['tl']} " . Yii::$app->t->translate('label_team_leader_short');
            }
            
            $guest_note .= implode(' + ', $note);
            
            $guest_note .= ')';
        }
        
        if(isset($v['guest_note']) && $v['guest_note'] != ''){
            $guest_note .= '<br/><i style="font-weight:normal">('.$v['guest_note'].')</i>';
        }
        
        $note1 = $this->showBoxText('note_guide_plan');
        
        $plan = \izi\tour\program\models\Plan::find()->where(['item_id'=>$id])->asArray()->one();
        
        $notess = isset($plan['note1']) ? $plan['note1'] : null;
        
        $note1 .= '<div class="explan-note">';
        
        if(!empty($notess)){
            foreach ($notess as $t){
                $note1 .= $t != "" ? '<p style="text-align: justify;">'.uh($t).'</p>' : '';
            }
        }
        
        $note1 .= '</div><div class="no-print ftrm-btn-action-plan"></div>';
        
//         if(!(isset($params['dev']) && $params['dev'])){
//             $note1 .= '<div class="no-print"><hr/>
// <button type="button" class="btn btn-sm btn-default btn-success"><i class="fa fa-plus"></i> Thêm ghi chú</button>
// </div>';
//         }
        
        $ccode = $v['code'];
        
        
//         view(Yii::$app->tour->series->getOne($v['series_id']));
        
//         if($v['series_id'] > 0){
//             $ccode .= " (".Yii::$app->tour->series->getOne($v['series_id'])->name.")";
//         }
        
        $regex = [
            '{{%TOUR_KE_HOACH_HD_CHI_TIET}}'	=>	$this->Tour_KeHoachHuongDan($id,$params),
            '{{%TOUR_CODE}}'=>$ccode,
            '{{%TOUR_SO_LUONG_KHACH}}'=>$total_pax . $guest_note,
            '{{%TOUR_KE_HOACH_HD_HDV}}'=>'<p class=""><b class="underline">Hướng dẫn viên:</b><br/>'.$g.'</p>',
            '{{%TOUR_KE_HOACH_HD_DH}}'=>'<p style="" class=""><b class="">ĐIỀU HÀNH:</b><br/>'.$tour_leader .'</b></p>',
            '{{%TOUR_KE_HOACH_HD_TT}}'=>'<p style="text-align:center"><b>THỦ TRƯỞNG ĐƠN VỊ</b></p>',
            '{{%TOUR_KE_HOACH_HD_GHI_CHU}}'=> $note1,
            '{{%TOUR_KE_HOACH_HD_GHI_CHU1}}'=>$plan_note,
            '{{%TOUR_KE_HOACH_HD_GHI_CHU2}}'=>$plan_note2,
            '{{%LOGO}}'=>'<span style="margin-top: 5px;display: inline-block;">'.getImage([
                'src'=>isset(Yii::$app->config['logo']['logo']['image']) ? Yii::$app->config['logo']['logo']['image'] : 
                (isset(Yii::$app->cfg->app['logo']['image']) ? Yii::$app->cfg->app['logo']['image'] : '')
                , 
                'absolute'=>true,
                    'h'=>93,
                ] ,true
            ) .'</span>',
            '{{%TOUR_KE_HOACH_HD_LAIXE}}'=>'',
            '{{%TOUR_SO_LUONG_PHONG}}'=>''


        ];
        $html .= replace_text_form($regex, uh($text1['value'],2));

        return $html;
    }


    public function showPrice($price = 0,$currency = -1, $showSymbol = true){
        $text_translate = 2;
        if(is_array($price)){

            $text_translate = isset($price['text_contact']) ? $price['text_contact'] : $text_translate;
            $price = isset($price['price']) ? $price['price'] : 0;
        }
        $currency = $currency == -1 ? (Yii::$app->c->getDefaultCurrency()) : (Yii::$app->c->getCurrency($currency));
        if(!is_numeric($price)) $price = cprice($price);
        if(!($price != 0)){
            //f[products][prices][zero][vi-VN]
            $controller_code = is_array($price) && isset($price['controller_code']) ? $price['controller_code'] :
            (defined('CONTROLLER_CODE') ? CONTROLLER_CODE : false);
            //view(Yii::$app->config[$controller_code]);
            if(isset(Yii::$app->config[$controller_code]['prices']['zero'][__LANG__]) && Yii::$app->config[$controller_code]['prices']['zero'][__LANG__] != ""){
                return uh(Yii::$app->config[$controller_code]['prices']['zero'][__LANG__]);
            }
            return is_numeric($text_translate) ? getTextTranslate($text_translate) : Yii::$app->t->translate($text_translate);
        }
        switch ($currency['display_type']){

            case 1: $pre = ''; $after = $currency['symbol']; break;
            case 2: $pre = ''; $after = $currency['code']; break;
            case 3: $pre = $currency['symbol']; $after = ''; break;
            case 4: $pre = $currency['code']; $after = ''; break;
            case 5: $pre = ''; $after = $currency['symbol2']; break;
            case 6: $pre = $currency['symbol2']; $after = ''; break;
            case 7: $pre = ''; $after = ' ' . $currency['symbol2']; break;
            case 8: $pre = ''; $after = ' ' . $currency['code']; break;
        }
//         if(isset($currency['display']) && $currency['display'] == -1){
//             $pre = $symbol;
//             $after = '';
//         }else{
//             $pre = '';
//             $after = $symbol;
//         }
        if(!$showSymbol) {
            $pre = $after = '';
        }
        return $pre . number_format($price,$currency['decimal_number']) . $after;
    }




    public function showCurrency($id=1, $display_type = false){
        $list = Yii::$app->c->getUserCurrency();
        // view($list);
        if(isset($list['list']) && !empty($list['list'])){
            foreach ($list['list'] as $k=>$v){
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


    public function getSupports($o = []){
        $r = \app\models\SiteConfigs::getConfigs('SUPPORTS');
        return isset($r['supports']) ? $r['supports'] : [];
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

        
        $query = (new Query())->from(['a'=>'{{%form_design}}'])->where(['a.is_active'=>1]);
        
        if($lang !== false){
            $query->andWhere(['a.lang'=>$lang]);
        }
        
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

    public function renderModal($o = []){
        $modal = '';
        $ajax_action = ltrim(isset($o['ajax_action']) ? $o['ajax_action'] : 'ajax', '/');
        
        if(isset($o['base_url']) && $o['base_url'] != ""){
            $ajax_action = $o['base_url'] . '/' . $ajax_action;
            
            $o['attrs']['data-base_url'] = $o['base_url'];
        }else{
            
        }
        
        $ajax_action = ltrim($ajax_action, '/');
        
        $action = isset($o['action']) ? $o['action'] : '';
        $class = isset($o['class']) ? $o['class'] : '';
        $title = isset($o['title']) ? $o['title'] : '';
        $name = isset($o['name']) ? $o['name'] : 'mymodal';
        $body= '<div class="modal-body inline-block w100">' . (isset($o['body']) ? $o['body'] : '') .'</div>' ;
        
        $footer = isset($o['footer']) ? $o['footer'] : '';
        
        
        $unset = isset($o['unset']) ? $o['unset'] : [];
        
        if(isset($_POST) && !empty($_POST)){
            if($action == ""){
                $action = 'quick-submit-' . post('action');
            }
            $_POST['action'] = $action;
            foreach ($_POST as $key=>$value){
                if(!is_array($value) && !in_array($key, $unset)){
                    $footer .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
                }
                
            }
            if(!in_array('request_post', $unset)){
                $footer .= '<textarea name="request_post" class="hide">'.json_encode($_POST).'</textarea>';
            }
            if(!in_array('modal', $unset)){
                $footer .= '<input type="hidden" name="modal" value=".'.($r = randString(12)).'"/>';
            }else{
                $r = randString(12);
            }
            
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
        
        $formTag = '<form 
data-action="'.$ajax_action.'" 
name="ajaxForm" 
action="/'.$ajax_action.'" 
class="ajaxForm '.$form_class.' f12px" 
method="'.(isset($o['method']) ? $o['method'] : 'post').'" 
onsubmit="'.(isset($o['onsubmit']) ? $o['onsubmit'] : 'return ajaxSubmitForm(this);').'"';
        
        if(isset($o['attrs']) && !empty($o['attrs'])){
            foreach ($o['attrs'] as $k1=>$v1){
                if(!is_array($v1)){
                    $formTag .= "$k1=\"$v1\" ";
                }
            }
        }
        
        $formTag .= '>';
        
        
        $header = '<div class="modal fade '.$name . " $r " .'" id="'.$name.'" tabindex="-1" role="dialog" aria-labelledby="'.$name.'Label"> '.$formTag.'
'.(!in_array('_csrf-frontend', $unset) ? '<input type="hidden" name="_csrf-frontend" value="'.Yii::$app->request->csrfToken.'" />' : '').'
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
