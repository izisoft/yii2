<?php 
namespace izi\local;
use Yii;
class Local extends \yii\base\Component
{
    
    
    // 
    private $_model;
    
    public function getModel(){
        if($this->_model == null){
            $this->_model = Yii::createObject('izi\local\models\Local'); 
        }
        return $this->_model;
    }
    
    private $_place;
    public function getPlace(){
        if($this->_place == null){
            $this->_place = Yii::createObject('izi\web\Place');
        }
        return $this->_place;
    }
    
    
    private $_place2;
    public function getPlace2(){
        if($this->_place2 == null){
            $this->_place2 = Yii::createObject('izi\local\Place2');
        }
        return $this->_place2;
    }
    
    private $_region;
    public function getRegion(){
        if($this->_region == null){
            $this->_region = Yii::createObject([
                'class' =>   'izi\local\Region',
                'local' =>  $this,
                'place' =>  $this->getPlace(),
            ]);
        }
        return $this->_region;
    }
    
    
    private $_code;
    
    // 
    
    public function getCode($country_id = 0){
        $country_id = $country_id > 0 ? $country_id : $this->getLocation();
        if(!empty($this->_code) && $this->_code->local_id == $country_id){
            return $this->_code;
        }        
        
        if( $this->_code == null){
            $this->_code = Yii::createObject([
                'class'=>'izi\local\Code',
                'local_id'=>$country_id
            ]);
        }else{
            
            $this->_code->setLocalId($country_id);// = $country_id;
            $this->_code->clear();
            
        }
        return $this->_code;
    }
    
    
    public function getCountryCode($country_id = 0){
        $item = $this->getModel()->getItem($country_id);
        if(!empty($item)) return $item['code'];
    }
    
    //
    
    private $_location;
    
    public function getLocation(){
        if($this->_location == null){
            $this->_location = \app\modules\admin\models\Siteconfigs::getDefaultLocation();
        }
        return $this->_location;
    }
    
    
    public function parseCountryxxx($mode = 1){
        $dir = dirname(Yii::getAlias('@app')) . '/environments/local_wiki';
        $html = '';
        
        switch ($mode){
            case 2:
                $html = file_get_contents($dir.'/ds_quoc_gia_co_chu_quyen.txt');
                break;
            default:
                $html = file_get_contents($dir.'/241_country.txt');
                break;
        }
        
        require_once Yii::getAlias('@common') . "/functions/simple_html_dom.php";
        $dom = new \simple_html_dom();
        $dom->load($html);
        
        //
        switch ($mode){
            case 2:
                $json = [];
                $tables = $dom->find('table');
                foreach ($tables as $k=>$table){
                    //view($table->tag);
                    //$table = (new \simple_html_dom())->load($table->tag);
                    $tbodys =  ($table->find('tr'));
                    
                   
                    
                    foreach ($tbodys as $k2=>$tr){
                        if($k2>0){
                            
                            $item2= $item = [];
                            
                            $tt = $tr->childNodes(0);
                            
                            $country_name = trim($tt->find('b',0)->plaintext);
                            
                            $country_name = preg_replace('/&nbsp;/', ' ',  $country_name);
                            
                            $item['vi-VN'] = preg_replace('/^[ \s]+|[ \s]+$/', '',  $country_name);
                                                       
                            //
                            $full_name = $tt->find('p',0);//->innertext;
                            
                            $full_name = $full_name != null ? $full_name->innertext : $tt->innertext;
                            
                            $pattern = '/\((.+?)\)/';
                            
                            $item2['vi-VN'] = preg_replace($pattern, '',  trim(str_replace(['<br>','<br/>'], ['',''], strip_tags_content($full_name))));
                            
                                                        
                            $tt = $tr->childNodes(2);
                            
                            $country_name = trim($tt->find('a',0)->plaintext);
                            
                            $country_name = preg_replace('/&nbsp;/', ' ',  $country_name);
                            
                            $item['en-US'] = preg_replace('/^[ \s]+|[ \s]+$/', '',  $country_name);
                            
                            
                            
                            //
                            $full_name = $tt->find('p',0);//->innertext;
                            
                            $full_name = $full_name != null ? $full_name->innertext : $tt->innertext;
                            
                            $pattern = '/\((.+?)\)/';
                            
                            $item2['en-US'] = preg_replace($pattern, '',  trim(str_replace(['<br>','<br/>'], ['',''], strip_tags_content($full_name))));
                            
                            $item['lang_code'] = "country_" . unMark($item['en-US'],'_');
                            
                            $item2['lang_code'] = "full_country_" . unMark($item['en-US'],'_');
                            //
                            
                           //view($item);
                            
                            $json[] = $item;
                            $json[] = $item2;
                        }
                         
                    }
                     
                }
                writeFile($dir.'/ds_quoc_gia_co_chu_quyen.json',json_encode($json));
                //view($json);
                
                break;
            default:
                $tbody = $dom->find('tbody tr');
                $country = [];
                $e = [];
                foreach ($tbody as $k=> $tr){
                    if($k>0){
                        $name = trim( $tr->childNodes (1)->plaintext);
                        
                        //$name = ucwords(mb_strtolower($name,'UTF-8'));
                        $name = ucwords(strtolower($name));
                        
                        $short_name = trim( $tr->childNodes (2)->plaintext);
                        $country[] = ['short_name'=>$short_name, 'title'=>$name];
                        
                        
                        
                        if((new \yii\db\Query())->from('local')->where(['code'=>$short_name])->count(1) >0){
                            if(!in_array($short_name, $e)){
                                $e[] = $short_name;
                            }else{
                                //view("$short_name/$name");
                                
                                
                            }
                        }
                        
                    }
                }
                writeFile("$dir/241_country.json",json_encode($country));
                //view($country);
                
                break;
        }
        
        
        
        
        return $dom->save();
    }
    
    
    
    public function parseCountryName(){
        
    }
    
    public function updateCountryName(){
        $dir = dirname(Yii::getAlias('@app')) . '/environments/local_wiki/ds_quoc_gia_co_chu_quyen.json';
        $local = json_decode(file_get_contents($dir,true),1);
        foreach ($local as $v){
            //dbUpdateTextTranslate
            if((new \yii\db\Query())->from('text_translate')->where(['lang_code'=>$v['lang_code']])->count(1) > 0){
                Yii::$app->t->dbUpdateTextTranslate($v['lang_code'], 'vi-VN', $v['vi-VN'],1);
                Yii::$app->t->dbUpdateTextTranslate($v['lang_code'], 'en-US', $v['en-US'],1);
            }else{
                view($v);
            }
        }
    }
    
    public function updateLangCodeOfCountry(){
        $local = $this->getModel()->getAll(['parent_id'=>0]);
        foreach ($local as $v){
            $lang_code = 'country_' . unMark( mb_strtolower($v['title'],'UTF-8'),'_');
            Yii::$app->db->createCommand()->update('local', ['lang_code'=>$lang_code],['id'=>$v['id']])->execute();
        }
    }
    
    
    
    public function getCountries($params = [], $cache = !YII_DEBUG){
        if(!isset($params['parent_id'])){
            $params['parent_id'] = 0;
        }
        $checksum = crc32( json_encode(array_merge([
            'function'  => __FUNCTION__,
            'class' =>  __CLASS__,
        ],$params)));
        $path = Yii::getAlias('@runtime/icache');
        $fp = "$path/$checksum.json";
        //        
        if($cache && file_exists($fp)){
            $l = json_decode(file_get_contents($fp,true),1);            
            if(!empty($l)){
                return array_sort($l, 'title');
            }
        }
        
        $l = $this->getModel()->getAll($params);
        if(!empty($l)){
            foreach ($l as $k=>$v){
                $l[$k]['title'] = $v['lang_code'] != "" ? Yii::$app->t->translate($v['lang_code']) : $v['title'];
            }
            
            $l = array_sort($l, 'title');
            
            $fp = "$path/$checksum.json";
            writeFile($fp,json_encode($l));
        }
        return $l;
    }
    
    public function updateCountryCode(){
        $dir = dirname(Yii::getAlias('@app')) . '/environments/local_wiki';
        $data = json_decode(file_get_contents($dir . "/unknown_code.json",true),1);
        
        $d2 = [];
        
        foreach ($data as $country => $v){
            $lang_code = $v['lang_code'];
            unset( $v['lang_code']);
            $item = (new \yii\db\Query())->from('local')->where(['lang_code'=>$lang_code])->one();
            
            if(!empty($item)){            
                foreach ($v as $code=>$val){
                    
                    if((new \yii\db\Query())->from('countries_to_code')->where([
                        'country_id'=>$item['id'],
                        'code'=>$code
                    ])->count(1) == 0 && $val != '—'){
                        Yii::$app->db->createCommand()->insert('countries_to_code', [
                            'country_id'=>$item['id'],
                            'code'=>$code,
                            'value'=>trim($val)
                        ])->execute();
                    }else{
//                         Yii::$app->db->createCommand()->update('countries_to_code',[
//                             'value'=> $val != '—' ? trim($val) : ''
                            
//                         ], [
//                             'country_id'=>$item['id'],
//                             'code'=>$code,
                            
//                         ])->execute();
                    }
                }
            }else{
                $v['lang_code'] = $lang_code;
                $d2[$country] = $v;
                $fp = "$dir/unknown_code2.json";
                writeFile($fp,json_encode($d2));
            }
        }
    }
    
    public function parseContryCode(){
        $dir = dirname(Yii::getAlias('@app')) . '/environments/local_wiki';
        $data = //array_merge([
            json_decode(file_get_contents("$dir/code1.json", true),1) +
            json_decode(file_get_contents("$dir/code2.json", true),1) +
            json_decode(file_get_contents("$dir/code3.json", true),1) +
            json_decode(file_get_contents("$dir/code4.json", true),1)
            
        //])
        ;
            $fp = $dir . "/code_all.json";
            
        writeFile($fp, json_encode($data));
        return $data;
    }
    
    public function parseContryCodeFromWiki(){
        $dir = dirname(Yii::getAlias('@app')) . '/environments/local_wiki';
        require_once Yii::getAlias('@common') . "/functions/simple_html_dom.php";
        
        $fp = $dir . "/code4.json";
        $data = [];
        $a = [
            //'A',
            //'B',
            //'C',
            //'D–E',
            //'F',
            //'G',
            //'H–I',
            //'J–K',
            //'L',
            //'M',
            //'N',
            //'O–Q',
            //'R',
            //'S',
            'T',
            'U–Z'
        ];
        
        foreach ($a as $k=> $b){

            if($k>4){
                //break;
            }
            $html = file_get_contents('https://en.wikipedia.org/wiki/Country_codes:_' . $b);
        
         
        
        
        $dom = new \simple_html_dom();
        $dom->load($html);
        
        
        
        $tables = $dom->find('#mw-content-text',0);
        
        //view($tables->find('table',0)->plaintext);
        
        foreach ($tables->find('table') as $table){
            $pattern = '/\[(.+?)\]/';
            
            $country_name = trim($table->prev_sibling()->find('a',0)->plaintext);
            
            $country_name = preg_replace('/&nbsp;/', ' ',  $country_name);
            
            $country_name = trim(preg_replace($pattern,'', $country_name));
            
            foreach ($table->find('tr') as $k2 =>    $tr){
                switch ($k2){
                    case 0:
                        
                        
                        
                        $p = $tr->childNodes (0)->find('p',0);
                        
                        if(!empty($p)){
                            $data[$country_name]['ISO-3166-1'] = trim($p->plaintext);
                            
                            $p = $tr->childNodes (1)->find('p',0);
                            
                            if(!empty($p)){
                                $data[$country_name]['ISO-3166-1-A3'] = trim($p->plaintext);
                            }else{
                                //view($data,true);
                            }
                            
                            $p = $tr->childNodes (2)->find('p',0);
                            
                            if(!empty($p)){
                                $data[$country_name]['ISO-3166-1-A2'] = trim($p->plaintext);
                            }else{
                                //view($data,true);
                            }
                            
                            
                            $p = $tr->childNodes (3)->find('p',0);
                            
                            if(!empty($p)){
                                $data[$country_name]['ICAO-airport'] = trim($p->plaintext);
                            }else{
                                //view($data,true);
                            }
                            
                            
                        }else{
                            //view($data,true);
                        }
                        
                        
                        
                        
                        //$data[$country_name]['ISO-3166-1'] = !empty($p) ? trim($tr->childNodes (0)->find('p',0)->plaintext) : "?";
                        
                        //$data[$country_name]['ISO-3166-1-A3'] = trim($tr->childNodes (1)->find('p',0)->plaintext);
                        //$data[$country_name]['ISO-3166-1-A2'] = trim($tr->childNodes (2)->find('p',0)->plaintext);
                        //$data[$country_name]['ICAO-airport'] = trim($tr->childNodes (3)->find('p',0)->plaintext);
                        break;
                    case 1:
                        $data[$country_name]['E164'] = trim($tr->childNodes (0)->find('p',0)->plaintext);
                        $data[$country_name]['IOC'] = trim($tr->childNodes (1)->find('p',0)->plaintext);
                        $data[$country_name]['Domain'] = trim($tr->childNodes (2)->find('p',0)->plaintext);
                        $data[$country_name]['ICAO-aircraft'] = trim($tr->childNodes (3)->find('p',0)->plaintext);
                        break;
                    case 2:
                        $data[$country_name]['E212'] = trim($tr->childNodes (0)->find('p',0)->plaintext);
                        $data[$country_name]['NATO3'] = trim($tr->childNodes (1)->find('p',0)->plaintext);
                        $data[$country_name]['NATO2'] = trim($tr->childNodes (2)->find('p',0)->plaintext);
                        $data[$country_name]['LOC-MARC'] = trim($tr->childNodes (3)->find('p',0)->plaintext);
                        break;
                    case 3:
                        $data[$country_name]['ITU-Maritime'] = trim($tr->childNodes (0)->find('p',0)->plaintext);
                        $data[$country_name]['ITU-Letter'] = trim($tr->childNodes (1)->find('p',0)->plaintext);
                        $data[$country_name]['FIPS'] = trim($tr->childNodes (2)->find('p',0)->plaintext);
                        $data[$country_name]['License-plate'] = trim($tr->childNodes (3)->find('p',0)->plaintext);
                        break;
                    case 4:
                        $data[$country_name]['GTIN-GS1'] = trim($tr->childNodes (0)->find('p',0)->plaintext);
                        $data[$country_name]['UNDP'] = trim($tr->childNodes (1)->find('p',0)->plaintext);
                        $data[$country_name]['WMO'] = trim($tr->childNodes (2)->find('p',0)->plaintext);
                        $data[$country_name]['ITU-callsign'] = trim($tr->childNodes (3)->find('p',0)->plaintext);
                        break;
                        
                }
                
            }
             
        }
        }
        
        writeFile($fp,json_encode($data));
        return $data;
        //return $dom->save();
        
    }
    
    
    public function showLocalNameByRegex($name = '', $type_id = 0, $show_full = false){
        $r = [
            ['id'=>0,'title'=>'','short'=>''],
            ['id'=>1,'title'=>'Tỉnh ','short'=>'T'],
            ['id'=>2,'title'=>'Thành Phố ','short'=>'TP'],
            ['id'=>3,'title'=>'Huyện ','short'=>'H'],
            ['id'=>4,'title'=>'Quận ','short'=>'Q'],
            ['id'=>5,'title'=>'Thị Xã ','short'=>'TX'],
            ['id'=>6,'title'=>'Xã ','short'=>'X'],
            ['id'=>7,'title'=>'Phường ','short'=>'P'],
            ['id'=>8,'title'=>'Thị Trấn ','short'=>'TT'],
        ];
        if(!is_numeric($type_id)){
            return $name;
        }
        if(is_numeric($type_id) && $type_id > -1){
            foreach ($r as $v){
                if($v['id'] == $type_id){
                    if($show_full){
                        return $v['title'] . $name;
                    }else{
                        return $v['short'] != "" ? $v['short'] .(
                            is_numeric($name) ? $name : ($name != '-' ? '. ' . $name : '')
                            ) : ($name != '-' ? $name : '');
                            
                            //return $v['short'] . (is_numeric($name) ? $name : ($name != '-' ? '. ' . $name : ''));
                    }
                    
                    break;
                }
            }
        }
        return $r;
    }
    
//     public function showLocalName($id, $params = []){
//         $item = $this->getModel()->getItem($id);
//         if(!empty($item)){
//             //view($item);
            
            
//             if(isset($params['show_full']) && $params['show_full'] == true){
//                 $local = $this->getModel()->parseLocal2($id);
//                 $pieces = [];
//                 foreach ($local as $v){
//                     $pieces[] = $this->showLocalNameByRegex($v['lang_code'] != "" ? Yii::$app->t->translate($v['lang_code']) : $v['title'], $v['type_id']);
//                 }
//                 return implode(', ', $pieces);
//             }else{
//                 $name = $this->showLocalNameByRegex($item['lang_code'] != "" ? Yii::$app->t->translate($item['lang_code']) : $item['title'], $item['type_id']);
//             }
            
//             return $name;
//         }
//     }
    
    
    public function parseLocal($id , $default = 0){
        return $this->getModel()->parseLocal($id, $default);
    }
    
    
    
    
    public function getJsonIso2(){
        $js = '[{"code":"af", "name":"Afghanistan"},{"code":"ax", "name":"Aland Islands"},{"code":"al", "name":"Albania"},{"code":"dz", "name":"Algeria"},{"code":"as", "name":"American Samoa"},{"code":"ad", "name":"Andorra"},{"code":"ao", "name":"Angola"},{"code":"ai", "name":"Anguilla"},{"code":"ag", "name":"Antigua"},{"code":"ar", "name":"Argentina"},{"code":"am", "name":"Armenia"},{"code":"aw", "name":"Aruba"},{"code":"au", "name":"Australia"},{"code":"at", "name":"Austria"},{"code":"az", "name":"Azerbaijan"},{"code":"bs", "name":"Bahamas"},{"code":"bh", "name":"Bahrain"},{"code":"bd", "name":"Bangladesh"},{"code":"bb", "name":"Barbados"},{"code":"by", "name":"Belarus"},{"code":"be", "name":"Belgium"},{"code":"bz", "name":"Belize"},{"code":"bj", "name":"Benin"},{"code":"bm", "name":"Bermuda"},{"code":"bt", "name":"Bhutan"},{"code":"bo", "name":"Bolivia"},{"code":"ba", "name":"Bosnia"},{"code":"bw", "name":"Botswana"},{"code":"bv", "name":"Bouvet Island"},{"code":"br", "name":"Brazil"},{"code":"vg", "name":"British Virgin Islands"},{"code":"bn", "name":"Brunei"},{"code":"bg", "name":"Bulgaria"},{"code":"bf", "name":"Burkina Faso"},{"code":"mm", "name":"Burma"},{"code":"bi", "name":"Burundi"},{"code":"tc", "name":"Caicos Islands"},{"code":"kh", "name":"Cambodia"},{"code":"cm", "name":"Cameroon"},{"code":"ca", "name":"Canada"},{"code":"cv", "name":"Cape Verde"},{"code":"ky", "name":"Cayman Islands"},{"code":"cf", "name":"Central African Republic"},{"code":"td", "name":"Chad"},{"code":"cl", "name":"Chile"},{"code":"cn", "name":"China"},{"code":"cx", "name":"Christmas Island"},{"code":"cc", "name":"Cocos Islands"},{"code":"co", "name":"Colombia"},{"code":"km", "name":"Comoros"},{"code":"cg", "name":"Congo Brazzaville"},{"code":"cd", "name":"Congo"},{"code":"ck", "name":"Cook Islands"},{"code":"cr", "name":"Costa Rica"},{"code":"ci", "name":"Cote Divoire"},{"code":"hr", "name":"Croatia"},{"code":"cu", "name":"Cuba"},{"code":"cy", "name":"Cyprus"},{"code":"cz", "name":"Czech Republic"},{"code":"dk", "name":"Denmark"},{"code":"dj", "name":"Djibouti"},{"code":"dm", "name":"Dominica"},{"code":"do", "name":"Dominican Republic"},{"code":"ec", "name":"Ecuador"},{"code":"eg", "name":"Egypt"},{"code":"sv", "name":"El Salvador"},{"code":"gb", "name":"England"},{"code":"gq", "name":"Equatorial Guinea"},{"code":"er", "name":"Eritrea"},{"code":"ee", "name":"Estonia"},{"code":"et", "name":"Ethiopia"},{"code":"eu", "name":"European Union"},{"code":"fk", "name":"Falkland Islands"},{"code":"fo", "name":"Faroe Islands"},{"code":"fj", "name":"Fiji"},{"code":"fi", "name":"Finland"},{"code":"fr", "name":"France"},{"code":"gf", "name":"French Guiana"},{"code":"pf", "name":"French Polynesia"},{"code":"tf", "name":"French Territories"},{"code":"ga", "name":"Gabon"},{"code":"gm", "name":"Gambia"},{"code":"ge", "name":"Georgia"},{"code":"de", "name":"Germany"},{"code":"gh", "name":"Ghana"},{"code":"gi", "name":"Gibraltar"},{"code":"gr", "name":"Greece"},{"code":"gl", "name":"Greenland"},{"code":"gd", "name":"Grenada"},{"code":"gp", "name":"Guadeloupe"},{"code":"gu", "name":"Guam"},{"code":"gt", "name":"Guatemala"},{"code":"gw", "name":"Guinea-Bissau"},{"code":"gn", "name":"Guinea"},{"code":"gy", "name":"Guyana"},{"code":"ht", "name":"Haiti"},{"code":"hm", "name":"Heard Island"},{"code":"hn", "name":"Honduras"},{"code":"hk", "name":"Hong Kong"},{"code":"hu", "name":"Hungary"},{"code":"is", "name":"Iceland"},{"code":"in", "name":"India"},{"code":"io", "name":"Indian Ocean Territory"},{"code":"id", "name":"Indonesia"},{"code":"ir", "name":"Iran"},{"code":"iq", "name":"Iraq"},{"code":"ie", "name":"Ireland"},{"code":"il", "name":"Israel"},{"code":"it", "name":"Italy"},{"code":"jm", "name":"Jamaica"},{"code":"jp", "name":"Japan"},{"code":"jo", "name":"Jordan"},{"code":"kz", "name":"Kazakhstan"},{"code":"ke", "name":"Kenya"},{"code":"ki", "name":"Kiribati"},{"code":"kw", "name":"Kuwait"},{"code":"kg", "name":"Kyrgyzstan"},{"code":"la", "name":"Laos"},{"code":"lv", "name":"Latvia"},{"code":"lb", "name":"Lebanon"},{"code":"ls", "name":"Lesotho"},{"code":"lr", "name":"Liberia"},{"code":"ly", "name":"Libya"},{"code":"li", "name":"Liechtenstein"},{"code":"lt", "name":"Lithuania"},{"code":"lu", "name":"Luxembourg"},{"code":"mo", "name":"Macau"},{"code":"mk", "name":"Macedonia"},{"code":"mg", "name":"Madagascar"},{"code":"mw", "name":"Malawi"},{"code":"my", "name":"Malaysia"},{"code":"mv", "name":"Maldives"},{"code":"ml", "name":"Mali"},{"code":"mt", "name":"Malta"},{"code":"mh", "name":"Marshall Islands"},{"code":"mq", "name":"Martinique"},{"code":"mr", "name":"Mauritania"},{"code":"mu", "name":"Mauritius"},{"code":"yt", "name":"Mayotte"},{"code":"mx", "name":"Mexico"},{"code":"fm", "name":"Micronesia"},{"code":"md", "name":"Moldova"},{"code":"mc", "name":"Monaco"},{"code":"mn", "name":"Mongolia"},{"code":"me", "name":"Montenegro"},{"code":"ms", "name":"Montserrat"},{"code":"ma", "name":"Morocco"},{"code":"mz", "name":"Mozambique"},{"code":"na", "name":"Namibia"},{"code":"nr", "name":"Nauru"},{"code":"np", "name":"Nepal"},{"code":"an", "name":"Netherlands Antilles"},{"code":"nl", "name":"Netherlands"},{"code":"nc", "name":"New Caledonia"},{"code":"pg", "name":"New Guinea"},{"code":"nz", "name":"New Zealand"},{"code":"ni", "name":"Nicaragua"},{"code":"ne", "name":"Niger"},{"code":"ng", "name":"Nigeria"},{"code":"nu", "name":"Niue"},{"code":"nf", "name":"Norfolk Island"},{"code":"kp", "name":"North Korea"},{"code":"mp", "name":"Northern Mariana Islands"},{"code":"no", "name":"Norway"},{"code":"om", "name":"Oman"},{"code":"pk", "name":"Pakistan"},{"code":"pw", "name":"Palau"},{"code":"ps", "name":"Palestine"},{"code":"pa", "name":"Panama"},{"code":"py", "name":"Paraguay"},{"code":"pe", "name":"Peru"},{"code":"ph", "name":"Philippines"},{"code":"pn", "name":"Pitcairn Islands"},{"code":"pl", "name":"Poland"},{"code":"pt", "name":"Portugal"},{"code":"pr", "name":"Puerto Rico"},{"code":"qa", "name":"Qatar"},{"code":"re", "name":"Reunion"},{"code":"ro", "name":"Romania"},{"code":"ru", "name":"Russia"},{"code":"rw", "name":"Rwanda"},{"code":"sh", "name":"Saint Helena"},{"code":"kn", "name":"Saint Kitts and Nevis"},{"code":"lc", "name":"Saint Lucia"},{"code":"pm", "name":"Saint Pierre"},{"code":"vc", "name":"Saint Vincent"},{"code":"ws", "name":"Samoa"},{"code":"sm", "name":"San Marino"},{"code":"gs", "name":"Sandwich Islands"},{"code":"st", "name":"Sao Tome"},{"code":"sa", "name":"Saudi Arabia"},{"code":"sn", "name":"Senegal"},{"code":"cs", "name":"Serbia"},{"code":"rs", "name":"Serbia"},{"code":"sc", "name":"Seychelles"},{"code":"sl", "name":"Sierra Leone"},{"code":"sg", "name":"Singapore"},{"code":"sk", "name":"Slovakia"},{"code":"si", "name":"Slovenia"},{"code":"sb", "name":"Solomon Islands"},{"code":"so", "name":"Somalia"},{"code":"za", "name":"South Africa"},{"code":"kr", "name":"South Korea"},{"code":"es", "name":"Spain"},{"code":"lk", "name":"Sri Lanka"},{"code":"sd", "name":"Sudan"},{"code":"sr", "name":"Suriname"},{"code":"sj", "name":"Svalbard"},{"code":"sz", "name":"Swaziland"},{"code":"se", "name":"Sweden"},{"code":"ch", "name":"Switzerland"},{"code":"sy", "name":"Syria"},{"code":"tw", "name":"Taiwan"},{"code":"tj", "name":"Tajikistan"},{"code":"tz", "name":"Tanzania"},{"code":"th", "name":"Thailand"},{"code":"tl", "name":"Timorleste"},{"code":"tg", "name":"Togo"},{"code":"tk", "name":"Tokelau"},{"code":"to", "name":"Tonga"},{"code":"tt", "name":"Trinidad"},{"code":"tn", "name":"Tunisia"},{"code":"tr", "name":"Turkey"},{"code":"tm", "name":"Turkmenistan"},{"code":"tv", "name":"Tuvalu"},{"code":"ug", "name":"Uganda"},{"code":"ua", "name":"Ukraine"},{"code":"ae", "name":"United Arab Emirates"},{"code":"us", "name":"United States"},{"code":"uy", "name":"Uruguay"},{"code":"um", "name":"Us Minor Islands"},{"code":"vi", "name":"Us Virgin Islands"},{"code":"uz", "name":"Uzbekistan"},{"code":"vu", "name":"Vanuatu"},{"code":"va", "name":"Vatican City"},{"code":"ve", "name":"Venezuela"},{"code":"vn", "name":"Vietnam"},{"code":"wf", "name":"Wallis and Futuna"},{"code":"eh", "name":"Western Sahara"},{"code":"ye", "name":"Yemen"},{"code":"zm", "name":"Zambia"},{"code":"zw", "name":"Zimbabwe"}]';
        
        return json_decode(trim($js));
        
    }
    
    public function updateCountryIso2(){
        foreach ($this->getCountries() as $v){
            $iso2 = $this->getCode($v['id'])->iso2;            
            $local  = (new \yii\db\Query())->from('local')->where(['id'=>$v['id'], 'parent_id'=>0])->one();
            
            if(strtolower($iso2) != $local['code']){
                view($iso2);
                view($local);
            }
        }
//         $e = [];
//         foreach ($this->getJsonIso2() as $v){
            
//             $local  = (new \yii\db\Query())->from('local')->where(['code'=>$v->code, 'parent_id'=>0])->one();
        
//             if(!empty($local)){
//                 view( $v->code . ': ' . $v->name .' | ' . $local['title']);
//             }else{
//                 //view($v->name . ': ' . $v->code);
                
//             }
            
//         }
        //view((new \yii\db\Query())->from('local')->where([ 'parent_id'=>0])->andWhere(['not in', 'code', $e])->one());
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
            $address .= (trim($address) != '' ? ', ' : "") . $this->showLocalName(uh($local['ward']['title']),$local['ward']['type_id']);
            
        }
        //////////
        if(isset($local['district']) && !empty($local['district'])  && trim($local['district']['title']) != "-"){
            $address .= (trim($address) != '' ? ', ' : "") .$this->showLocalName(uh($local['district']['title']),$local['district']['type_id']);
        }
        //////////
        if(isset($local['province']) && !empty($local['province'])  && trim($local['province']['title']) != "-"){
            $address .= (trim($address) != '' ? ', ' : "") .$this->showLocalName(uh($local['province']['title']),$local['province']['type_id']);
        }
        //////////-
        // view($showCountry);
        if($showCountry && isset($local['country']) && !empty($local['country']) && trim($local['country']['title']) != "-" && !in_array($local['country']['id'], $igrones)){
            $address .= (trim($address) != '' ? ', ' : "") .$this->showLocalName(uh($local['country']['title']),$local['country']['type_id']);
        }
        //////////
        return $address;
    }
    
    
    public function parseCountry($id = 0, $default = 0){
        
        if($id < 1) $id = $default;
        
        $query= (new \yii\db\Query())->select(['id','lft','rgt','title','lang_code','international_title','level','type_id'])
        ->from($this->getModel()->tableName());
        if($id>0){
            $query->where(['id'=>$id]);
        }else {
            return false;
            $query->where(['is_default'=>1,'parent_id'=>0]);
        }
        $item = $query->one();
        
        if(!empty($item)){
            $r = (new \yii\db\Query())->select(['id','lft','rgt','title','lang_code','international_title','level','type_id'])
            ->from($this->getModel()->tableName())->where([
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
    
    
    
    
    public function getLocalType($type_id = -1, $language = ROOT_LANG){
        
        switch ($language){
            case ROOT_LANG:
                $r = [
                ['id'=>0,'title'=>'Quốc gia ','short'=>''],
                ['id'=>1,'title'=>'Tỉnh ','short'=>'T'],
                ['id'=>2,'title'=>'Thành Phố ','short'=>'TP'],
                ['id'=>9,'title'=>'Thủ đô ','short'=>'Tđ'],
                ['id'=>10,'title'=>'Đặc khu ','short'=>'Đk'],
                ['id'=>11,'title'=>'Quần đảo ','short'=>'Qđ'],
                ['id'=>16,'title'=>'Đảo ','short'=>'Đ'],
                ['id'=>12,'title'=>'Bang ','short'=>'B'],
                ['id'=>13,'title'=>'Liên bang ','short'=>'Lb'],
                ['id'=>14,'title'=>'Vùng ','short'=>'V'],
                ['id'=>15,'title'=>'Khu ','short'=>'K'],
                ['id'=>3,'title'=>'Huyện ','short'=>'H'],
                ['id'=>4,'title'=>'Quận ','short'=>'Q'],
                ['id'=>5,'title'=>'Thị Xã ','short'=>'TX'],
                ['id'=>6,'title'=>'Xã ','short'=>'X'],
                ['id'=>7,'title'=>'Phường ','short'=>'P'],
                ['id'=>8,'title'=>'Thị Trấn ','short'=>'TT'],
                
                ];
                break;
            default:
                $r = [
                ['id'=>0,'title'=>'Country','short'=>''],
                ['id'=>1,'title'=>'Province','short'=>'Pro.'],
                ['id'=>2,'title'=>'City','short'=>'City'],
                ['id'=>9,'title'=>'Capital','short'=>'Capital'],
                ['id'=>10,'title'=>'Đặc khu ','short'=>'Đk'],
                ['id'=>11,'title'=>'Quần đảo ','short'=>'Qđ'],
                ['id'=>16,'title'=>'Đảo ','short'=>'Đ'],
                ['id'=>12,'title'=>'State','short'=>'State'],
                ['id'=>13,'title'=>'Liên bang ','short'=>'Lb'],
                ['id'=>14,'title'=>'Vùng ','short'=>'V'],
                ['id'=>15,'title'=>'Khu ','short'=>'K'],
                ['id'=>3,'title'=>'District','short'=>'Dist.'],
                ['id'=>4,'title'=>'District','short'=>'Dist.'],
                ['id'=>5,'title'=>'Sub-district','short'=>'Sub-district'],
                ['id'=>6,'title'=>'Ward','short'=>'Ward'],
                ['id'=>7,'title'=>'Ward','short'=>'Ward'],
                ['id'=>8,'title'=>'Sub-district','short'=>'Sub-district'],
                
                ];
                break;
        }
        
        
        
        if(is_numeric($type_id) && $type_id > -1){
            foreach ($r as $v){
                if($v['id'] == $type_id){
                    return $v['title'] ; break;
                }
            }
        }
        return $r;
    }
    
    
    public function showLocalType($type_id = -1,$language = ROOT_LANG ){
        $r = getLocalType($language);
        if(is_numeric($type_id) && $type_id > -1){
            foreach ($r as $v){
                if($v['id'] == $type_id){
                    
                    return $v['id']>0 ? $v['title'] : '' ; break;
                }
            }
        }
        return $r;
    }
    
    public function showLocalName($name = '', $type_id = 0, $language = ROOT_LANG, $show_full = false){
        $r = $this->getLocalType($language);
        if(!is_numeric($type_id)){
            return $name;
        }
        if(is_numeric($type_id) && $type_id > -1){
            foreach ($r as $v){
                if($v['id'] == $type_id){
                    if($show_full){
                        switch ($language){
                            case ROOT_LANG:
                                return ($v['id']>0 ? $v['title'] : '') . $name;
                                break;
                            default:
                                return is_numeric($name) ? ($v['id']>0 ? $v['title'] : '') . $name : ($name . ($v['id']>0 ? $v['title'] : ''));
                                break;
                        }
                    }else{
                        switch ($language){
                            case ROOT_LANG:
                                
                                return $v['short'] != "" ? $v['short'] .(
                                is_numeric($name) ? $name : ($name != '-' ? '. ' . $name : '')
                                ) : ($name != '-' ? $name : '');
                                break;
                            default:
                                
                                if(is_numeric($name)){
                                    return $v['short'] != "" ? $v['short'] .(
                                        is_numeric($name) ? $name : ($name != '-' ? '. ' . $name : '')
                                        ) : ($name != '-' ? $name : '');
                                }else{
                                    return $v['short'] != "" ? $name . ' ' . $v['short'] : ($name != '-' ? $name : '');
                                }
                                
                                break;
                        }
                        
                    }
                    
                    break;
                }
            }
        }
        return $r;
    }
    
    
}