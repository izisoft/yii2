<?php 
function view($param, $title = 'Dump', $exit = false)
{
    echo $title . '<hr/>';
    echo '<pre>';
    var_dump($param);
    echo '</pre>';
    
    if($exit) exit;
}

function cu($param, $ab = false, $option = []){    
    
    if($param === false){
        $category_id = isset($option['category_id']) ? $option['category_id'] : 0;
        if($category_id>0){
            //$item = \app\models\Slugs::getItem('',$category_id);
            //$param = DS . $item['url'];
        }
    }else{
        //$string = is_array($param) ? $param[0] : $param;
        if(!is_array($param)){
            $string = $param;
            $param = [$string];
        }else{
            $string = $param[0];
        }
        if(defined('__DOMAIN_ADMIN__') && __DOMAIN_ADMIN__ && substr($string, 0,1) != '/'){
            $string = '/' . $string;
            $param[0] = $string;
        }
    }
    
    
    return \yii\helpers\Url::to($param,$ab) ;
}