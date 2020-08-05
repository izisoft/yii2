<?php 
namespace izi\web;
use Yii;

class Theme extends \yii\base\Theme

{
    private $_viewPath;
    
    private $_version;
    
    public function getViewPath($path = '')
    {
        return rtrim($this->_viewPath, DIRECTORY_SEPARATOR) . ($path != "" ? DIRECTORY_SEPARATOR . trim($path , DIRECTORY_SEPARATOR) : "");
    }
    
    public function getVersion()
    {
        return $this->_version;
    }
    
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this->_version;
    }
    
    public function setViewPath($path)
    {
        $this->_viewPath = Yii::getAlias($path);
    }
    
//     public function init()
//     {
//          echo __METHOD__;
//     }
    
    
}
