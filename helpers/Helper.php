<?php 

function trim_space($text)
{
    if(!is_string($text)) return $text;
    return trim(preg_replace('/\s+/', ' ', $text));
}

function view($param, $label = null, $exit = false)
{
    if(YII_DEBUG && !Yii::$app->request->isAjax){
        
        ini_set('memory_limit', -1);
        
        echo "$label:<hr><pre>";
        var_dump($param);
        echo "</pre><hr>";
        
        if($exit) exit(0);
    }
}

function dev($param, $label = null, $exit = false)
{
    if(YII_DEBUG && !Yii::$app->request->isAjax){
        echo "<pre>";
        echo($param);
        echo "</pre>";
    }
}
function cprice($val = 0){
    
    if(!(strlen($val) > 0)){
        return 0;
    }
    
    if(is_string($val)){
        if(strlen($val) == 0) return $val;
        $n = str_replace(',', '', $val);
        return is_numeric($n) ? $n : 0;
    }elseif (is_numeric($val)){
        return $val;
    }
    return 0;
}
function uh($text,$i = 1){
    if(!is_string($text)) return $text;
    $h = htmlspecialchars_decode(stripslashes($text),ENT_QUOTES );
    switch ($i){
        case 'quot': $h = str_replace('"', '&quot;', $h);break;
        case 'nobr': $h = str_replace(array('<br/>','<br>','</br>'), array(' ',' ',' '), $h);break;
        
    }
    if(is_numeric($i) && $i > 1){    while ($i > 1){	$i--;   	return uh($h);    }    }
    return $h;
}

function uhs($text, $step = 1)
{
    return htmlspecialchars($text, $step);
}

function trim_all($string,$pattern  = '/(\s\s+)|(\\t|\\r|\\n|\\0\\x0B)/'){
    return preg_replace($pattern, ' ', trim($string));
}



function getParam($name , $defaultValue = null) {
    return \Yii::$app->request->get($name, $defaultValue);
}


function post($element, $defaultValue = null){
    $post = Yii::$app->request->post($element,$defaultValue);
    
    return replaceDateMask($post);
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