<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\assets;

use yii\web\AssetBundle;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Asset bundle for the Twitter bootstrap javascript files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BootstrapAsset extends AssetBundle
{

    public $bsVersion;
    
    private $_isBs4 ;
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',                
//         'yii\bootstrap4\BootstrapPluginAsset',
    ];
    
    public function init()
    {
      
        $this->bsVersion = isset(Yii::$app->params['bsVersion']) ? Yii::$app->params['bsVersion'] : '4.x';
        if($this->isBs4()){
            $this->depends[] = 'yii\bootstrap4\BootstrapPluginAsset';
        }else{
            $this->depends[] = 'yii\bootstrap\BootstrapPluginAsset';
        } 
    }
    
    protected function configureBsVersion()
    {
        $v = empty($this->bsVersion) ? ArrayHelper::getValue(Yii::$app->params, 'bsVersion', '3') : $this->bsVersion;
        $ver = static::parseVer($v);
        $this->_isBs4 = $ver === '4';
        return $ver;
    }
    
    /**
     * Validate if Bootstrap 4.x version
     * @return bool
     *
     * @throws InvalidConfigException
     */
    public function isBs4()
    {
        if (!isset($this->_isBs4)) {
            $this->configureBsVersion();
        }
        return $this->_isBs4;
    }
    
    protected static function parseVer($ver)
    {
        $ver = (string)$ver;
        return substr(trim($ver), 0, 1);
    }
}

