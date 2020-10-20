<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

$path = dirname(dirname(__DIR__)) . '/yiisoft/yii2';

require $path . '/BaseYii.php';

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It extends from [[\yii\BaseYii]] which provides the actual implementation.
 * By writing your own Yii class, you can customize some functionalities of [[\yii\BaseYii]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Yii extends \yii\BaseYii
{
    /**
     * Old config 
     */
    public static $device = 'desktop', $is_mobile = false,$_category = [],$site = [],$item = [];
    public static function file(){
        $cfg = (new \yii\db\Query)->from(['{{%server_config}}'])
        ->where(['is_active'=>1,'sid'=>__SID__])
        ->andWhere(['>','state',-2])
        ->one();
        if(empty($cfg)){
            $cfg = (new \yii\db\Query)->from(['{{%server_config}}'])
            ->where(['is_active'=>1,'sid'=>0])
            ->andWhere(['>','state',-2])
            ->one();
        }
        
        return new \izi\web\Ftp($cfg);
    }
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require $path . '/classes.php';
Yii::$container = new yii\di\Container();
