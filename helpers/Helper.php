<?php 
function view($param, $title = 'Dump', $exit = false)
{
    echo $title . '<hr/>';
    echo '<pre>';
    var_dump($param);
    echo '</pre>';
    
    if($exit) exit;
}